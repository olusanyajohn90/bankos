<?php

namespace App\Services\FixedAsset;

use App\Models\FixedAsset;
use App\Models\Tenant;
use Carbon\Carbon;

class FixedAssetService
{
    /**
     * Accrue straight-line depreciation for N months on a single asset.
     */
    public function accrueStraightLine(FixedAsset $asset, int $months = 1): void
    {
        if ($asset->status !== 'active') {
            return;
        }

        $depreciableBase = (float) $asset->purchase_cost - (float) $asset->residual_value;
        $annualDepr      = $depreciableBase / max(1, $asset->useful_life_years);
        $monthlyDepr     = $annualDepr / 12;
        $charge          = $monthlyDepr * $months;

        // Cap at remaining depreciable amount
        $totalAllowed  = $depreciableBase;
        $alreadyTaken  = (float) $asset->accumulated_depreciation;
        $remaining     = max(0, $totalAllowed - $alreadyTaken);
        $charge        = min($charge, $remaining);

        if ($charge <= 0) {
            return;
        }

        $newAccumulated  = round($alreadyTaken + $charge, 2);
        $newBookValue    = round(max((float) $asset->residual_value, (float) $asset->current_book_value - $charge), 2);

        $updates = [
            'accumulated_depreciation' => $newAccumulated,
            'current_book_value'       => $newBookValue,
            'last_depreciation_date'   => Carbon::today()->toDateString(),
        ];

        // If fully depreciated
        if ($newAccumulated >= $totalAllowed) {
            $updates['status'] = 'fully_depreciated';
        }

        $asset->update($updates);
    }

    /**
     * Accrue double-declining-balance depreciation for N months on a single asset.
     */
    public function accrueDeclineBalance(FixedAsset $asset, int $months = 1): void
    {
        if ($asset->status !== 'active') {
            return;
        }

        $rate        = (2 / max(1, $asset->useful_life_years)); // annual double-declining rate
        $monthlyDep  = (float) $asset->current_book_value * ($rate / 12) * $months;

        // Floor at residual value
        $floor  = (float) $asset->residual_value;
        $newNbv = max($floor, (float) $asset->current_book_value - $monthlyDep);
        $charge = (float) $asset->current_book_value - $newNbv;

        if ($charge <= 0) {
            return;
        }

        $newAccumulated = round((float) $asset->accumulated_depreciation + $charge, 2);

        $updates = [
            'accumulated_depreciation' => $newAccumulated,
            'current_book_value'       => round($newNbv, 2),
            'last_depreciation_date'   => Carbon::today()->toDateString(),
        ];

        if ($newNbv <= $floor) {
            $updates['status'] = 'fully_depreciated';
        }

        $asset->update($updates);
    }

    /**
     * Run monthly depreciation for all active assets of a tenant.
     * Returns array with processed count and total depreciation charged.
     */
    public function processMonthly(string $tenantId): array
    {
        $assets           = FixedAsset::where('tenant_id', $tenantId)->active()->get();
        $count            = 0;
        $totalDepreciation = 0.0;

        foreach ($assets as $asset) {
            $before = (float) $asset->current_book_value;

            if ($asset->depreciation_method === 'declining_balance') {
                $this->accrueDeclineBalance($asset, 1);
            } else {
                $this->accrueStraightLine($asset, 1);
            }

            $asset->refresh();
            $charged           = $before - (float) $asset->current_book_value;
            $totalDepreciation += max(0, $charged);
            $count++;
        }

        return [
            'processed'         => $count,
            'total_depreciation' => round($totalDepreciation, 2),
        ];
    }

    /**
     * Dispose of an asset.
     */
    public function dispose(FixedAsset $asset, float $disposalValue, string $notes, Carbon $date): void
    {
        $asset->update([
            'status'          => 'disposed',
            'disposed_at'     => $date->toDateString(),
            'disposal_value'  => $disposalValue,
            'disposal_notes'  => $notes,
            'current_book_value' => 0,
        ]);
    }
}
