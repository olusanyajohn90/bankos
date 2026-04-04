<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceAuditTrail extends Model
{
    protected $table = 'compliance_audit_trail';

    protected $fillable = [
        'tenant_id', 'event_type', 'entity_type', 'entity_id',
        'description', 'metadata', 'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function eventColor(): string
    {
        return match ($this->event_type) {
            'breach'           => 'red',
            'warning'          => 'amber',
            'check_passed'     => 'green',
            'evidence_added'   => 'blue',
            'status_changed'   => 'purple',
            'framework_scored' => 'indigo',
            default            => 'gray',
        };
    }
}
