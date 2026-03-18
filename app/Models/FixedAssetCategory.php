<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class FixedAssetCategory extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'useful_life_years',
        'depreciation_method', 'residual_rate',
        'gl_asset_code', 'gl_depreciation_code',
    ];

    protected $casts = [
        'useful_life_years' => 'integer',
        'residual_rate'     => 'decimal:2',
    ];

    public function assets()
    {
        return $this->hasMany(FixedAsset::class, 'category_id');
    }
}
