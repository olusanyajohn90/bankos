<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AccrueInterest extends Command
{
    protected $signature = 'banking:accrue-interest {--tenant= : Limit to a specific tenant ID}';
    protected $description = 'Daily savings interest accrual for all active savings accounts';

    public function handle(): int
    {
        $today = Carbon::today();

        $tenantsQuery = DB::table('tenants')->where('status', 'active');
        if ($this->option('tenant')) {
            $tenantsQuery->where('id', $this->option('tenant'));
        }
        $tenants = $tenantsQuery->get();

        foreach ($tenants as $tenant) {
            try {
                $this->accrueForTenant($tenant, $today);
            } catch (\Throwable $e) {
                Log::error("AccrueInterest: failed for tenant {$tenant->id} — {$e->getMessage()}");
                $this->error("Tenant {$tenant->name}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    private function accrueForTenant(object $tenant, Carbon $today): void
    {
        // Fetch all active savings accounts for this tenant, joining savings_products for the rate
        $accounts = DB::table('accounts as a')
            ->leftJoin('savings_products as sp', 'sp.id', '=', 'a.savings_product_id')
            ->where('a.tenant_id', $tenant->id)
            ->where('a.type', 'savings')
            ->where('a.status', 'active')
            ->select(
                'a.id',
                'a.available_balance',
                'a.ledger_balance',
                'a.currency',
                DB::raw('COALESCE(sp.interest_rate, 4.00) as rate')
            )
            ->get();

        $totalAccrued = 0;
        $accountCount = 0;
        $dateStr      = $today->format('Ymd');

        foreach ($accounts as $account) {
            $rate          = (float) $account->rate;
            $balance       = (float) $account->available_balance;
            $dailyInterest = round($balance * ($rate / 100 / 365), 2);

            if ($dailyInterest < 0.01) {
                continue; // Skip micro-penny noise
            }

            $shortId   = strtoupper(substr(str_replace('-', '', $account->id), 0, 8));
            $reference = "INT-{$dateStr}-{$shortId}";

            DB::transaction(function () use ($account, $dailyInterest, $reference, $tenant, $today) {
                // Credit the account
                DB::table('accounts')
                    ->where('id', $account->id)
                    ->increment('available_balance', $dailyInterest);

                DB::table('accounts')
                    ->where('id', $account->id)
                    ->increment('ledger_balance', $dailyInterest);

                // Insert transaction record
                DB::table('transactions')->insert([
                    'id'          => (string) \Illuminate\Support\Str::uuid(),
                    'tenant_id'   => $tenant->id,
                    'account_id'  => $account->id,
                    'reference'   => $reference,
                    'type'        => 'interest',
                    'amount'      => $dailyInterest,
                    'currency'    => $account->currency ?? 'NGN',
                    'description' => 'Daily interest accrual',
                    'status'      => 'success',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            $totalAccrued += $dailyInterest;
            $accountCount++;
        }

        $summary = sprintf(
            'Accrued NGN %s across %d account(s) for tenant %s',
            number_format($totalAccrued, 2),
            $accountCount,
            $tenant->name
        );

        Log::info("AccrueInterest: {$summary}");
        $this->info($summary);
    }
}
