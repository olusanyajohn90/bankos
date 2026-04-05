<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrossBorderRule extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'country_code', 'country_name',
        'requirements', 'restrictions', 'risk_category', 'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'restrictions' => 'array',
        'is_active'    => 'boolean',
    ];
}
