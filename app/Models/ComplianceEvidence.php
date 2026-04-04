<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComplianceEvidence extends Model
{
    use HasUuids;

    protected $table = 'compliance_evidence';

    protected $fillable = [
        'id', 'control_id', 'tenant_id', 'type', 'title',
        'description', 'file_path', 'data', 'is_auto_collected',
        'collected_by', 'collected_at', 'expires_at',
    ];

    protected $casts = [
        'data'              => 'array',
        'is_auto_collected' => 'boolean',
        'collected_at'      => 'datetime',
        'expires_at'        => 'datetime',
    ];

    public function control()
    {
        return $this->belongsTo(ComplianceControl::class, 'control_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
