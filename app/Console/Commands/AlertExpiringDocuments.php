<?php

namespace App\Console\Commands;

use App\Services\Documents\DocumentExpiryService;
use Illuminate\Console\Command;

class AlertExpiringDocuments extends Command
{
    protected $signature = 'bankos:docs:alert-expiring {--days=30 : Days ahead to check}';
    protected $description = 'Send alerts for documents expiring within N days';

    public function handle(DocumentExpiryService $service): int
    {
        $days = (int) $this->option('days');
        $this->components->info("Checking for documents expiring within {$days} days...");
        $count = $service->alertExpiring($days);
        $this->components->info("Sent {$count} expiry alert notifications.");
        return self::SUCCESS;
    }
}
