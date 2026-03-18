<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DetectOverdueLoans extends Command
{
    protected $signature = 'banking:detect-overdue-loans {--tenant= : Limit to a specific tenant ID}';
    protected $description = 'Mark active loans with a passed maturity date as overdue';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $tenantsQuery = DB::table('tenants')->where('status', 'active');
        if ($this->option('tenant')) {
            $tenantsQuery->where('id', $this->option('tenant'));
        }
        $tenants = $tenantsQuery->get();

        $totalMarked = 0;

        foreach ($tenants as $tenant) {
            try {
                $marked = $this->detectForTenant($tenant, $today);
                $totalMarked += $marked;
                Log::info("DetectOverdueLoans: Marked {$marked} loan(s) overdue for tenant {$tenant->name}");
                $this->info("Tenant {$tenant->name}: {$marked} loan(s) marked overdue");
            } catch (\Throwable $e) {
                Log::error("DetectOverdueLoans: failed for tenant {$tenant->id} — {$e->getMessage()}");
                $this->error("Tenant {$tenant->name}: {$e->getMessage()}");
            }
        }

        $this->info("Total: {$totalMarked} loan(s) marked overdue across all tenants.");

        return self::SUCCESS;
    }

    private function detectForTenant(object $tenant, string $today): int
    {
        // Find active loans whose expected maturity date has passed
        $overdueLoans = DB::table('loans')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereNotNull('expected_maturity_date')
            ->where('expected_maturity_date', '<', $today)
            ->select('id', 'loan_number', 'account_id', 'tenant_id')
            ->get();

        if ($overdueLoans->isEmpty()) {
            return 0;
        }

        foreach ($overdueLoans as $loan) {
            DB::transaction(function () use ($loan) {
                // Update loan status to overdue
                DB::table('loans')
                    ->where('id', $loan->id)
                    ->update(['status' => 'overdue', 'updated_at' => now()]);

                // Insert a system transaction record if loan has an account
                if ($loan->account_id) {
                    DB::table('transactions')->insert([
                        'id'          => (string) Str::uuid(),
                        'tenant_id'   => $loan->tenant_id,
                        'account_id'  => $loan->account_id,
                        'reference'   => 'OVD-' . now()->format('Ymd') . '-' . strtoupper(substr(str_replace('-', '', $loan->id), 0, 8)),
                        'type'        => 'fee',
                        'amount'      => 0.00,
                        'currency'    => 'NGN',
                        'description' => 'Loan marked overdue — missed repayment date',
                        'status'      => 'success',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            });
        }

        return $overdueLoans->count();
    }
}
