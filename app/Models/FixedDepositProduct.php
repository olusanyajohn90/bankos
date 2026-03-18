<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class FixedDepositProduct extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'interest_rate', 'interest_payment',
        'min_tenure_days', 'max_tenure_days', 'min_amount', 'max_amount',
        'early_liquidation_penalty', 'allow_top_up', 'allow_early_liquidation',
        'auto_rollover', 'status',
    ];

    protected $casts = [
        'interest_rate'             => 'decimal:3',
        'min_amount'                => 'decimal:2',
        'max_amount'                => 'decimal:2',
        'early_liquidation_penalty' => 'decimal:2',
        'allow_top_up'              => 'boolean',
        'allow_early_liquidation'   => 'boolean',
        'auto_rollover'             => 'boolean',
    ];

    public function fixedDeposits()
    {
        return $this->hasMany(FixedDeposit::class, 'product_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
