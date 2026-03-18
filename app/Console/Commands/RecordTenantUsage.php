<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\TenantUsage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordTenantUsage extends Command
{
    protected $signature   = 'billing:record-usage';
    protected $description = 'Record monthly usage metrics for all tenants and check plan limits';

    public function handle(): int
    {
        $period = now()->format('Y-m');
        $this->info("Recording tenant usage for period: {$period}");

        $tenants = Tenant::where('status', 'active')->get();
        $updated = 0;
        $limited = 0;

        foreach ($tenants as $tenant) {
            try {
                $counts = $this->countTenantUsage($tenant, $period);

                TenantUsage::updateOrInsert(
                    ['tenant_id' => $tenant->id, 'period' => $period],
                    array_merge($counts, ['recorded_at' => now()])
                );

                // Check limits and mark past_due if over limit
                $overLimit = $this->checkLimits($tenant, $counts);
                if ($overLimit) {
                    TenantSubscription::where('tenant_id', $tenant->id)
                        ->where('status', 'active')
                        ->update(['status' => 'past_due']);

                    $this->warn("  [OVER LIMIT] {$tenant->name}");
                    $limited++;
                }

                $updated++;
                $this->line("  [OK] {$tenant->name} — customers: {$counts['customer_count']}, txns: {$counts['transaction_count']}");
            } catch (\Throwable $e) {
                $this->error("  [ERROR] {$tenant->name}: {$e->getMessage()}");
                Log::error("RecordTenantUsage failed for tenant {$tenant->id}", ['error' => $e->getMessage()]);
            }
        }

        $this->info("Done. Updated: {$updated} tenants. Over-limit (past_due): {$limited}.");
        Log::info("billing:record-usage completed", [
            'period'  => $period,
            'updated' => $updated,
            'limited' => $limited,
        ]);

        return self::SUCCESS;
    }

    private function countTenantUsage(Tenant $tenant, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $customerCount = DB::table('customers')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->count();

        $staffCount = DB::table('users')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        $branchCount = DB::table('branches')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        $transactionCount = DB::table('transactions')
            ->where('tenant_id', $tenant->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return [
            'customer_count'    => $customerCount,
            'staff_count'       => $staffCount,
            'branch_count'      => $branchCount,
            'transaction_count' => $transactionCount,
            'api_call_count'    => 0, // to be populated via API gateway logging
        ];
    }

    private function checkLimits(Tenant $tenant, array $counts): bool
    {
        $subscription = TenantSubscription::with('plan')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription || !$subscription->plan) {
            return false;
        }

        $plan = $subscription->plan;

        if ($plan->max_customers && $counts['customer_count'] > $plan->max_customers) {
            return true;
        }

        if ($plan->max_staff_users && $counts['staff_count'] > $plan->max_staff_users) {
            return true;
        }

        if ($plan->max_branches && $counts['branch_count'] > $plan->max_branches) {
            return true;
        }

        if ($plan->max_transactions_monthly && $counts['transaction_count'] > $plan->max_transactions_monthly) {
            return true;
        }

        return false;
    }
}
