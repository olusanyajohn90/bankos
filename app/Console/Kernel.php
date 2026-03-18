<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check workflow SLAs every hour — escalate overdue tasks, warn at-risk
        $schedule->command('workflow:check-sla')->hourly();

        // KPI computation — daily for current month, monthly for quarterly/yearly
        $schedule->command('bankos:compute-kpis --period-type=monthly')
                 ->dailyAt('01:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        $schedule->command('bankos:compute-kpis --period-type=quarterly')
                 ->monthlyOn(1, '02:00')
                 ->withoutOverlapping();

        $schedule->command('bankos:compute-kpis --period-type=yearly')
                 ->yearlyOn(1, 1, '03:00')
                 ->withoutOverlapping();

        // Leave: accrue balances for new year on Jan 1, carry over from prior year on Jan 2
        $schedule->command('hr:accrue-leave --year=' . now()->year)
                 ->yearlyOn(1, 1, '04:00')
                 ->withoutOverlapping();

        $schedule->command('hr:accrue-leave --carry-over --from-year=' . (now()->year - 1) . ' --to-year=' . now()->year)
                 ->yearlyOn(1, 2, '04:00')
                 ->withoutOverlapping();

        // Fixed deposits: accrue daily interest and process maturities at 00:30
        $schedule->command('bankos:process-fixed-deposits')
                 ->dailyAt('00:30')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Standing orders: process due orders daily at 00:45
        $schedule->command('bankos:process-standing-orders')
                 ->dailyAt('00:45')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Documents: alert expiring documents daily at 07:00
        $schedule->command('bankos:docs:alert-expiring --days=30')
                 ->dailyAt('07:00')
                 ->withoutOverlapping();

        // Account dormancy: check and flag dormant accounts on 1st of each month at 06:00
        $schedule->command('bankos:check-dormancy')
                 ->monthlyOn(1, '06:00')
                 ->withoutOverlapping();

        // Fixed Assets: run monthly depreciation on 1st of each month at 06:30
        $schedule->command('bankos:depreciate-assets')
                 ->monthlyOn(1, '06:30')
                 ->withoutOverlapping();

        // SaaS billing: record tenant usage metrics on 1st of each month at 00:30
        $schedule->command('billing:record-usage')
                 ->monthlyOn(1, '00:30')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Savings interest: accrue daily interest on all active savings accounts at midnight
        $schedule->command('banking:accrue-interest')
                 ->dailyAt('00:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Loan overdue detection: mark active loans past their maturity date as overdue at 07:00
        $schedule->command('banking:detect-overdue-loans')
                 ->dailyAt('07:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Loan repayment reminders: queue reminder emails for loans due within 3 days at 08:00
        $schedule->command('banking:send-loan-reminders')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Weekly account statements — every Monday at 07:00
        $schedule->command('banking:send-weekly-statements')
                 ->weeklyOn(1, '07:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Custom scheduled reports — run daily at 06:00, command decides which are due
        $schedule->command('reports:send-scheduled')
                 ->dailyAt('06:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
