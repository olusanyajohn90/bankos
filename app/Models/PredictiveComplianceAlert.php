<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PredictiveComplianceAlert extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'alert_type', 'title', 'description',
        'prediction_data', 'severity', 'status',
        'recommended_action', 'ai_analysis',
    ];

    protected $casts = [
        'prediction_data' => 'array',
    ];
}
