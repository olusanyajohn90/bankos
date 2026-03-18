<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Region extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'manager_id', 'status'];

    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function branches(): HasMany { return $this->hasMany(Branch::class); }
    public function staffProfiles(): HasMany { return $this->hasMany(StaffProfile::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
