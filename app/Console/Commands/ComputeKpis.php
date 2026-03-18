<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\KpiAlertService;
use App\Services\KpiComputeService;
use Illuminate\Console\Command;

class ComputeKpis extends Command
{
    protected $signature = 'bankos:compute-kpis
        {--period= : Period value e.g. 2025-03 or 2025-Q1 or 2025}
        {--period-type=monthly : monthly / quarterly / yearly}
        {--tenant= : Specific tenant UUID, or all tenants if omitted}';

    protected $description = 'Auto-compute KPI actuals from live data and fire performance alerts';

    public function handle(KpiComputeService $kpi, KpiAlertService $alerts): int
    {
        $periodType  = $this->option('period-type');
        $periodValue = $this->option('period') ?? $kpi->currentPeriodValue($periodType);

        $this->info("BankOS KPI Computation — {$periodType} / {$periodValue}");

        $tenantId = $this->option('tenant');
        $tenants  = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return Command::SUCCESS;
        }

        $totalComputed = 0;
        $totalAlerts   = 0;
        $totalErrors   = 0;

        foreach ($tenants as $tenant) {
            $this->components->task("Tenant: {$tenant->name}", function () use (
                $kpi, $alerts, $tenant, $periodType, $periodValue,
                &$totalComputed, &$totalAlerts, &$totalErrors
            ) {
                $result = $kpi->computeAll($tenant->id, $periodType, $periodValue);
                $totalComputed += $result['computed'];
                $totalErrors   += $result['errors'];

                $fired = $alerts->checkAndFire($tenant->id, $periodType, $periodValue);
                $totalAlerts += $fired;

                return $result['errors'] === 0;
            });
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Tenants processed', $tenants->count()],
                ['KPI actuals computed', $totalComputed],
                ['Alerts fired', $totalAlerts],
                ['Errors', $totalErrors],
            ]
        );

        if ($totalErrors > 0) {
            $this->warn("Completed with {$totalErrors} error(s). Check logs for details.");
            return Command::FAILURE;
        }

        $this->info('KPI computation completed successfully.');
        return Command::SUCCESS;
    }
}
