<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiDefinition extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'code', 'name', 'description', 'category',
        'unit', 'direction', 'weight', 'department_applicable',
        'computation_type', 'auto_compute_method', 'is_active', 'is_system',
    ];

    protected $casts = [
        'department_applicable' => 'array',
        'is_active'             => 'boolean',
        'is_system'             => 'boolean',
        'weight'                => 'float',
    ];

    public function targets(): HasMany { return $this->hasMany(KpiTarget::class, 'kpi_id'); }
    public function actuals(): HasMany { return $this->hasMany(KpiActual::class, 'kpi_id'); }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'business_development' => 'Business Development',
            'credit_lending'       => 'Credit & Lending',
            'operations'           => 'Operations',
            'customer_service'     => 'Customer Service',
            'branch'               => 'Branch Performance',
            default                => ucfirst($this->category),
        };
    }

    public function scopeActive($q)  { return $q->where('is_active', true); }
    public function scopeSystem($q)  { return $q->where('is_system', true); }
    public function scopeAuto($q)    { return $q->where('computation_type', 'auto'); }
}
