<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Hr\LeaveService;
use Illuminate\Console\Command;

class AccrueLeave extends Command
{
    protected $signature = 'hr:accrue-leave
                            {--year= : Year to initialise balances for (defaults to current year)}
                            {--carry-over : Run carry-over instead of initialisation}
                            {--from-year= : Source year for carry-over}
                            {--to-year= : Target year for carry-over}
                            {--tenant= : Specific tenant ID (defaults to all active tenants)}';

    protected $description = 'Initialise leave balances for a new year, or carry over unused leave from prior year';

    public function handle(LeaveService $service): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->where('status', 'active')->get()
            : Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->components->warn('No active tenants found.');
            return self::SUCCESS;
        }

        if ($this->option('carry-over')) {
            $fromYear = (int)($this->option('from-year') ?? now()->year - 1);
            $toYear   = (int)($this->option('to-year')   ?? now()->year);
            $this->components->info("Carrying over leave balances from {$fromYear} → {$toYear}");

            foreach ($tenants as $tenant) {
                $this->components->task($tenant->name, function () use ($service, $tenant, $fromYear, $toYear) {
                    $count = $service->carryOverBalances($tenant->id, $fromYear, $toYear);
                    $this->line("  → {$count} balances carried over");
                });
            }
        } else {
            $year = (int)($this->option('year') ?? now()->year);
            $this->components->info("Initialising leave balances for {$year}");

            foreach ($tenants as $tenant) {
                $this->components->task($tenant->name, function () use ($service, $tenant, $year) {
                    $count = $service->initBalances($tenant->id, $year);
                    $this->line("  → {$count} balances created");
                });
            }
        }

        return self::SUCCESS;
    }
}
