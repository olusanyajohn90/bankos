<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Notifications\WeeklyStatementNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendWeeklyStatements extends Command
{
    protected $signature   = 'banking:send-weekly-statements {--tenant= : Limit to a single tenant ID}';
    protected $description = 'Send weekly account statement emails to active portal customers';

    public function handle(): int
    {
        $periodEnd   = now()->subDay()->format('d M Y');          // yesterday (Sunday)
        $periodStart = now()->subDays(7)->format('d M Y');        // 7 days ago (last Monday)
        $since       = now()->subDays(7)->startOfDay();

        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->where('status', 'active')->get()
            : Tenant::where('status', 'active')->get();

        $this->info("Sending weekly statements for period {$periodStart} – {$periodEnd}");

        $totalSent = 0;

        foreach ($tenants as $tenant) {
            $this->components->task($tenant->name, function () use ($tenant, $since, $periodStart, $periodEnd, &$totalSent) {

                // Get all portal-active customers with an email for this tenant
                $customers = DB::table('customers')
                    ->where('tenant_id', $tenant->id)
                    ->where('portal_active', 1)
                    ->whereNotNull('email')
                    ->whereRaw("TRIM(email) != ''")
                    ->get();

                foreach ($customers as $customer) {
                    // Get this customer's active accounts
                    $accounts = DB::table('accounts')
                        ->where('tenant_id', $tenant->id)
                        ->where('customer_id', $customer->id)
                        ->where('status', 'active')
                        ->get();

                    foreach ($accounts as $account) {
                        // Get last 7 days of transactions for this account
                        $transactions = DB::table('transactions')
                            ->where('tenant_id', $tenant->id)
                            ->where('account_id', $account->id)
                            ->where('status', 'success')
                            ->where('created_at', '>=', $since)
                            ->orderBy('created_at', 'asc')
                            ->get();

                        // Skip accounts with no activity
                        if ($transactions->isEmpty()) {
                            continue;
                        }

                        try {
                            Notification::route('mail', $customer->email)
                                ->notify(new WeeklyStatementNotification(
                                    $customer,
                                    $account,
                                    $tenant,
                                    $transactions,
                                    $periodStart,
                                    $periodEnd
                                ));

                            $totalSent++;
                        } catch (\Throwable $e) {
                            $this->warn(
                                "  Failed to send statement to {$customer->email} "
                                . "(account {$account->account_number}): {$e->getMessage()}"
                            );
                        }
                    }
                }
            });
        }

        $this->info("Done. {$totalSent} statement(s) sent.");

        return self::SUCCESS;
    }
}
