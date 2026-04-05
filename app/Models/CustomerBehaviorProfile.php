<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerBehaviorProfile extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'transaction_patterns',
        'baseline_metrics', 'anomaly_thresholds', 'anomaly_count_30d',
        'behavior_risk_score', 'profile_computed_at',
    ];

    protected $casts = [
        'transaction_patterns' => 'array',
        'baseline_metrics'     => 'array',
        'anomaly_thresholds'   => 'array',
        'behavior_risk_score'  => 'decimal:2',
        'profile_computed_at'  => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
