<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class FixedAssetRevaluation extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'fixed_asset_id',
        'previous_book_value',
        'new_book_value',
        'revaluation_amount',
        'reason',
        'revalued_by',
        'revalued_at',
    ];

    protected $casts = [
        'previous_book_value' => 'decimal:2',
        'new_book_value'      => 'decimal:2',
        'revaluation_amount'  => 'decimal:2',
        'revalued_at'         => 'datetime',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function revaluedBy()
    {
        return $this->belongsTo(User::class, 'revalued_by');
    }
}
