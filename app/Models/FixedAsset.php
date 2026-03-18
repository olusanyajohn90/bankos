<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class FixedAsset extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'asset_tag', 'description',
        'purchase_date', 'purchase_cost', 'current_book_value',
        'accumulated_depreciation', 'depreciation_method', 'useful_life_years',
        'residual_value', 'last_depreciation_date', 'status',
        'disposed_at', 'disposal_value', 'disposal_notes',
        'branch_id', 'purchased_by',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'last_depreciation_date'   => 'date',
        'disposed_at'              => 'date',
        'purchase_cost'            => 'decimal:2',
        'current_book_value'       => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'residual_value'           => 'decimal:2',
        'disposal_value'           => 'decimal:2',
        'useful_life_years'        => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchasedBy()
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    /**
     * Monthly straight-line depreciation charge.
     */
    public function getMonthlyDepreciationAttribute(): float
    {
        $depreciableBase = (float) $this->purchase_cost - (float) $this->residual_value;

        if ($this->depreciation_method === 'declining_balance') {
            $annualRate = ($this->useful_life_years > 0)
                ? (2 / $this->useful_life_years)
                : 0;
            return round(((float) $this->current_book_value * $annualRate) / 12, 2);
        }

        $usefulLifeMonths = max(1, $this->useful_life_years * 12);
        return round($depreciableBase / $usefulLifeMonths, 2);
    }

    public function getAnnualDepreciationAttribute(): float
    {
        return $this->monthly_depreciation * 12;
    }

    /**
     * Generate a simple depreciation schedule for display.
     */
    public function getDepreciationScheduleAttribute(): array
    {
        $rows         = [];
        $nbv          = (float) $this->purchase_cost;
        $residual     = (float) $this->residual_value;
        $annual       = $this->annual_depreciation;
        $purchaseYear = $this->purchase_date->year;

        for ($year = 1; $year <= $this->useful_life_years; $year++) {
            $charge = min($annual, max(0, $nbv - $residual));
            $nbvEnd = max($residual, $nbv - $charge);
            $rows[] = [
                'year'        => $purchaseYear + $year - 1,
                'opening_nbv' => round($nbv, 2),
                'charge'      => round($charge, 2),
                'accumulated' => round((float) $this->purchase_cost - $nbvEnd, 2),
                'closing_nbv' => round($nbvEnd, 2),
            ];
            $nbv = $nbvEnd;
            if ($nbv <= $residual) break;
        }

        return $rows;
    }
}
