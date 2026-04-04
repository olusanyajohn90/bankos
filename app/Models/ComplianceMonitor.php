<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComplianceMonitor extends Model
{
    use HasUuids;

    protected $table = 'compliance_monitors';

    protected $fillable = [
        'id', 'tenant_id', 'name', 'description', 'check_type',
        'config', 'frequency', 'current_value', 'threshold_value',
        'status', 'last_checked_at', 'is_active', 'control_id',
    ];

    protected $casts = [
        'config'          => 'array',
        'current_value'   => 'float',
        'threshold_value' => 'float',
        'last_checked_at' => 'datetime',
        'is_active'       => 'boolean',
    ];

    public function control()
    {
        return $this->belongsTo(ComplianceControl::class, 'control_id');
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
            'passing' => 'green',
            'warning' => 'amber',
            'failing' => 'red',
            default   => 'gray',
        };
    }
}
