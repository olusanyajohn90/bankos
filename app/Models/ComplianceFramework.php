<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComplianceFramework extends Model
{
    use HasUuids;

    protected $table = 'compliance_frameworks';

    protected $fillable = [
        'id', 'tenant_id', 'name', 'code', 'description',
        'total_controls', 'compliant_controls', 'non_compliant_controls',
        'not_assessed_controls', 'compliance_score',
        'last_assessed_at', 'is_active',
    ];

    protected $casts = [
        'total_controls'         => 'integer',
        'compliant_controls'     => 'integer',
        'non_compliant_controls' => 'integer',
        'not_assessed_controls'  => 'integer',
        'compliance_score'       => 'float',
        'last_assessed_at'       => 'datetime',
        'is_active'              => 'boolean',
    ];

    public function controls()
    {
        return $this->hasMany(ComplianceControl::class, 'framework_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scoreColor(): string
    {
        if ($this->compliance_score >= 80) return 'green';
        if ($this->compliance_score >= 60) return 'amber';
        return 'red';
    }
}
