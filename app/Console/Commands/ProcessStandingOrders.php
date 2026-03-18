<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\StandingOrder\StandingOrderService;
use Illuminate\Console\Command;

class ProcessStandingOrders extends Command
{
    protected $signature = 'bankos:process-standing-orders {--tenant=}';
    protected $description = 'Execute due standing orders for all active tenants';

    public function handle(StandingOrderService $service): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->where('status', 'active')->get()
            : Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $this->components->task($tenant->name, function () use ($service, $tenant) {
                $results = $service->processDue($tenant->id);
                $this->line("  → {$results['processed']} processed, {$results['failed']} failed");
            });
        }

        return self::SUCCESS;
    }
}
