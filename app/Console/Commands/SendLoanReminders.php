<?php

namespace App\Console\Commands;

use App\Notifications\LoanRepaymentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class SendLoanReminders extends Command
{
    protected $signature = 'banking:send-loan-reminders {--tenant= : Limit to a specific tenant ID}';
    protected $description = 'Queue repayment reminder notifications for loans due within 3 days';

    public function handle(): int
    {
        $today     = Carbon::today();
        $threeDays = $today->copy()->addDays(3)->toDateString();
        $todayStr  = $today->toDateString();

        $tenantsQuery = DB::table('tenants')->where('status', 'active');
        if ($this->option('tenant')) {
            $tenantsQuery->where('id', $this->option('tenant'));
        }
        $tenants = $tenantsQuery->get();

        $totalSent = 0;

        foreach ($tenants as $tenant) {
            try {
                $sent = $this->sendForTenant($tenant, $todayStr, $threeDays);
                $totalSent += $sent;
                Log::info("SendLoanReminders: Sent {$sent} reminder(s) for tenant {$tenant->name}");
                $this->info("Tenant {$tenant->name}: {$sent} reminder(s) queued");
            } catch (\Throwable $e) {
                Log::error("SendLoanReminders: failed for tenant {$tenant->id} — {$e->getMessage()}");
                $this->error("Tenant {$tenant->name}: {$e->getMessage()}");
            }
        }

        $this->info("Total: Sent {$totalSent} reminders.");

        return self::SUCCESS;
    }

    private function sendForTenant(object $tenant, string $todayStr, string $threeDays): int
    {
        // Find loans due within today and the next 3 days
        $loans = DB::table('loans as l')
            ->join('customers as c', 'c.id', '=', 'l.customer_id')
            ->where('l.tenant_id', $tenant->id)
            ->whereIn('l.status', ['active', 'overdue'])
            ->whereNotNull('l.expected_maturity_date')
            ->whereBetween('l.expected_maturity_date', [$todayStr, $threeDays])
            ->select(
                'l.id',
                'l.loan_number',
                'l.outstanding_balance',
                'l.expected_maturity_date',
                'l.tenant_id',
                'c.first_name',
                'c.last_name',
                'c.email'
            )
            ->get();

        $sent = 0;

        foreach ($loans as $loan) {
            if (empty($loan->email)) {
                continue; // Skip customers without an email address
            }

            try {
                Notification::route('mail', $loan->email)
                    ->notify(new LoanRepaymentReminder($loan, $tenant));

                $sent++;
            } catch (\Throwable $e) {
                Log::warning("SendLoanReminders: Could not queue reminder for loan {$loan->loan_number} — {$e->getMessage()}");
            }
        }

        return $sent;
    }
}
