<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AmlAlert extends Model
{
    use HasUuids;

    protected $table = 'aml_alerts';

    protected $fillable = [
        'id', 'tenant_id', 'alert_type', 'severity', 'status',
        'entity_type', 'entity_id', 'customer_id', 'transaction_id', 'account_id',
        'score', 'details', 'notes', 'assigned_to', 'reviewed_at', 'reviewed_by',
    ];

    protected $casts = [
        'details'     => 'array',
        'reviewed_at' => 'datetime',
        'score'       => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function severityColor(): string
    {
        return match ($this->severity) {
            'critical' => 'red',
            'high'     => 'orange',
            'medium'   => 'amber',
            'low'      => 'blue',
            default    => 'gray',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'open'         => 'red',
            'under_review' => 'amber',
            'escalated'    => 'orange',
            'dismissed'    => 'gray',
            'reported'     => 'green',
            default        => 'gray',
        };
    }
}
