<?php

namespace App\Services;

use App\Models\FixedAsset;
use Carbon\Carbon;

class FixedAssetService
{
    /**
     * Accrue one month of depreciation for a single asset.
     * Idempotent: will not double-charge in the same calendar month.
     */
    public function accrue(FixedAsset $asset): bool
    {
        if ($asset->status !== 'active') {
            return false;
        }

        $today = Carbon::today();

        // Already depreciated this month?
        if ($asset->last_depreciation_date
            && Carbon::parse($asset->last_depreciation_date)->isSameMonth($today)) {
            return false;
        }

        $charge = $asset->monthly_depreciation;

        if ($charge <= 0) {
            return false;
        }

        // Do not exceed depreciable base
        $maxCharge = max(0, (float) $asset->current_book_value - (float) $asset->residual_value);
        $charge    = min($charge, $maxCharge);

        if ($charge <= 0) {
            return false;
        }

        $newAccumulated = (float) $asset->accumulated_depreciation + $charge;
        $newNbv         = max((float) $asset->residual_value, (float) $asset->current_book_value - $charge);

        $asset->update([
            'accumulated_depreciation' => round($newAccumulated, 2),
            'current_book_value'       => round($newNbv, 2),
            'last_depreciation_date'   => $today->toDateString(),
        ]);

        return true;
    }

    /**
     * Dispose / write off an asset.
     */
    public function dispose(FixedAsset $asset, string $disposalDate, float $proceeds = 0): void
    {
        $asset->update([
            'status'        => 'disposed',
            'disposed_at'   => $disposalDate,
            'disposal_value'=> $proceeds,
        ]);
    }

    /**
     * Write off an asset (zero proceeds).
     */
    public function writeOff(FixedAsset $asset, string $date): void
    {
        $asset->update([
            'status'      => 'written_off',
            'disposed_at' => $date,
        ]);
    }

    /**
     * Run monthly depreciation for all active assets of a tenant.
     * Returns array with count processed and total depreciation amount.
     */
    public function processMonthly(string $tenantId): array
    {
        $assets  = FixedAsset::where('tenant_id', $tenantId)->active()->get();
        $count   = 0;
        $total   = 0.0;

        foreach ($assets as $asset) {
            $charge = $asset->monthly_depreciation;
            if ($this->accrue($asset)) {
                $count++;
                $total += $charge;
            }
        }

        return ['processed' => $count, 'total_depreciation' => $total];
    }
}
