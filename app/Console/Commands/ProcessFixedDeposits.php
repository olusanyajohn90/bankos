<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\FixedDeposit;
use App\Services\FixedDeposit\FixedDepositService;
use Illuminate\Console\Command;

class ProcessFixedDeposits extends Command
{
    protected $signature = 'bankos:process-fixed-deposits {--tenant=}';
    protected $description = 'Accrue interest and process maturities for fixed deposits';

    public function handle(FixedDepositService $service): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->where('status', 'active')->get()
            : Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $this->components->task($tenant->name, function () use ($service, $tenant) {
                $active = FixedDeposit::where('tenant_id', $tenant->id)->where('status', 'active')->get();
                foreach ($active as $fd) {
                    $service->accrueInterest($fd);
                }
                $matured = $service->processMaturities($tenant->id);
                $this->line("  → {$active->count()} FDs accrued, {$matured} matured/rolled");
            });
        }

        return self::SUCCESS;
    }
}
