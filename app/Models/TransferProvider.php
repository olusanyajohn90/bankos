<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferProvider extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'transfer_providers';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'provider_class',
        'config',
        'is_active',
        'is_default',
        'max_amount',
        'min_amount',
        'flat_fee',
        'percentage_fee',
        'fee_cap',
        'priority',
    ];

    protected $casts = [
        'config'         => 'array',
        'is_active'      => 'boolean',
        'is_default'     => 'boolean',
        'max_amount'     => 'decimal:2',
        'min_amount'     => 'decimal:2',
        'flat_fee'       => 'decimal:2',
        'percentage_fee' => 'decimal:4',
        'fee_cap'        => 'decimal:2',
        'priority'       => 'integer',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    // ── Fee Calculation ───────────────────────────────────────────────────────

    /**
     * Calculate the transfer fee for a given amount.
     *
     * Fee = flat_fee + (amount * percentage_fee), capped at fee_cap if set.
     */
    public function calculateFee(float $amount): float
    {
        $fee = (float) $this->flat_fee + ($amount * (float) $this->percentage_fee);

        if ($this->fee_cap !== null && $fee > (float) $this->fee_cap) {
            $fee = (float) $this->fee_cap;
        }

        return round($fee, 2);
    }
}
