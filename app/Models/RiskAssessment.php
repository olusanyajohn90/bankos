<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'risk_type', 'title', 'description', 'severity',
        'exposure_amount', 'metrics', 'status', 'mitigation_plan',
        'assigned_to', 'created_by',
    ];

    protected $casts = [
        'exposure_amount' => 'decimal:2',
        'metrics'         => 'array',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
