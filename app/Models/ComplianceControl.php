<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComplianceControl extends Model
{
    use HasUuids;

    protected $table = 'compliance_controls';

    protected $fillable = [
        'id', 'framework_id', 'tenant_id', 'control_ref', 'title',
        'description', 'category', 'status', 'evidence_notes',
        'evidence_files', 'auto_check_config', 'last_checked_at',
        'remediation_plan', 'remediation_due', 'assigned_to', 'priority',
    ];

    protected $casts = [
        'evidence_files'   => 'array',
        'auto_check_config' => 'array',
        'last_checked_at'  => 'datetime',
        'remediation_due'  => 'date',
    ];

    public function framework()
    {
        return $this->belongsTo(ComplianceFramework::class, 'framework_id');
    }

    public function evidence()
    {
        return $this->hasMany(ComplianceEvidence::class, 'control_id');
    }

    public function monitors()
    {
        return $this->hasMany(ComplianceMonitor::class, 'control_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'compliant'     => 'green',
            'partial'       => 'amber',
            'non_compliant' => 'red',
            'not_assessed'  => 'gray',
            default         => 'gray',
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            1 => 'Critical', 2 => 'High', 3 => 'Medium', 4 => 'Low', default => 'Unknown',
        };
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            1 => 'red', 2 => 'orange', 3 => 'amber', 4 => 'blue', default => 'gray',
        };
    }
}
