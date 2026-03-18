<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Builder;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id',
    ];

    /**
     * The "booted" method of the model.
     * Apply global scope to only show standard roles (tenant_id = null)
     * OR roles belonging to the current tenant.
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $tenantId = auth()->user()->tenant_id;
                // If we're operating within a tenant context, show global roles + this tenant's roles
                if ($tenantId) {
                    $builder->where(function($q) use ($tenantId) {
                        $q->whereNull('roles.tenant_id')
                          ->orWhere('roles.tenant_id', $tenantId);
                    });
                }
            }
        });
    }

    /**
     * Scope a query to only include custom roles for a specific tenant.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustom($query)
    {
        return $query->whereNotNull('tenant_id');
    }

    /**
     * Relationship to the Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
