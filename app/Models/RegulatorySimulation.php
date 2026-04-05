<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RegulatorySimulation extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'scenario_params',
        'baseline_metrics', 'simulated_metrics', 'impact_analysis',
        'ai_recommendation', 'status', 'created_by',
    ];

    protected $casts = [
        'scenario_params'   => 'array',
        'baseline_metrics'  => 'array',
        'simulated_metrics' => 'array',
        'impact_analysis'   => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
