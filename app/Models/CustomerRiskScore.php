<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerRiskScore extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'overall_score', 'risk_level',
        'score_breakdown', 'risk_factors', 'last_assessed_at',
        'assessed_by', 'ai_narrative',
    ];

    protected $casts = [
        'overall_score'    => 'decimal:2',
        'score_breakdown'  => 'array',
        'risk_factors'     => 'array',
        'last_assessed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
