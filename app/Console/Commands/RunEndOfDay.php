<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunEndOfDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bankos:eod {--date= : The date to run EOD for (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Automated End-of-Day (EOD) processing, including Savings Interest Accrual';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\EndOfDayService $eodService)
    {
        $this->info('Starting BankOS End-of-Day Processing...');
        
        $date = $this->option('date');
        if ($date) {
            $this->info("Running for specific date: {$date}");
        }

        try {
            // 1. Process Savings Interest
            $this->components->task('Processing Savings Account Interest Accrual', function () use ($eodService, $date) {
                $summary = $eodService->processSavingsInterest($date);
                
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Accounts Processed', $summary['accounts_processed']],
                        ['Total Interest Accrued', number_format($summary['total_interest_accrued'], 2)],
                        ['Total Interest Posted', number_format($summary['total_interest_posted'], 2)],
                        ['Errors', $summary['errors']]
                    ]
                );

                if ($summary['errors'] > 0) {
                    $this->error("Completed with {$summary['errors']} errors. Check Laravel logs for details.");
                }

                return $summary['errors'] === 0;
            });

            $this->info('BankOS EOD Processing Completed Successfully.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('EOD Processing Failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('EOD Processing Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
