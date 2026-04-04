<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskLimit extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'limit_type', 'name', 'limit_value',
        'current_value', 'utilization_pct', 'status', 'warning_threshold',
    ];

    protected $casts = [
        'limit_value'       => 'decimal:2',
        'current_value'     => 'decimal:2',
        'utilization_pct'   => 'decimal:2',
        'warning_threshold' => 'decimal:2',
    ];
}
