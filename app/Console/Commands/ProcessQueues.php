<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:work-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start queue workers for all bankOS named queues (development use). In production, use Supervisor with supervisor.conf.';

    /**
     * Execute the console command.
     *
     * Starts a single queue:work process listening on all named queues in
     * priority order: financial transfers first, then notifications, PDF,
     * AML, webhooks, and finally the default queue.
     *
     * NOTE: This is for development convenience only.
     * In production, run one Supervisor worker per queue for isolation
     * and independent retry/concurrency control. See supervisor.conf.
     */
    public function handle(): int
    {
        $queues = 'transfers,pdf,notifications,aml,webhooks,default';

        $this->info('Starting bankOS queue worker for all queues...');
        $this->info("Listening on: {$queues}");
        $this->newLine();
        $this->warn('For production, use Supervisor with supervisor.conf in the project root.');
        $this->newLine();

        $command = implode(' ', [
            PHP_BINARY,
            base_path('artisan'),
            'queue:work',
            "--queue={$queues}",
            '--sleep=3',
            '--tries=3',
            '--max-time=3600',
            '-v',
        ]);

        $this->line("Executing: {$command}");
        $this->newLine();

        passthru($command, $exitCode);

        return $exitCode;
    }
}
