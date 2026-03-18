<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\FixedAsset\FixedAssetService;
use Illuminate\Console\Command;

class DepreciateAssets extends Command
{
    protected $signature = 'bankos:depreciate-assets {--tenant-id= : Run for a specific tenant ID}';
    protected $description = 'Run monthly depreciation for fixed assets';

    public function handle(FixedAssetService $service): int
    {
        $tenantId = $this->option('tenant-id');

        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->where('status', 'active')->get()
            : Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return self::SUCCESS;
        }

        $grandTotal = 0;
        $grandCount = 0;

        foreach ($tenants as $tenant) {
            $result = $service->processMonthly($tenant->id);
            $grandCount += $result['processed'];
            $grandTotal += $result['total_depreciation'];

            $this->line(sprintf(
                '  [%s] %d asset(s) depreciated — ₦%s total',
                $tenant->name,
                $result['processed'],
                number_format($result['total_depreciation'], 2)
            ));
        }

        $this->info(sprintf(
            'Monthly depreciation complete. %d asset(s) processed, ₦%s total.',
            $grandCount,
            number_format($grandTotal, 2)
        ));

        return self::SUCCESS;
    }
}
