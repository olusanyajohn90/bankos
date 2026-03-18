<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Department extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'division_id', 'name', 'code', 'head_id', 'cost_centre_code', 'status'];

    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function head(): BelongsTo { return $this->belongsTo(User::class, 'head_id'); }
    public function staffProfiles(): HasMany { return $this->hasMany(StaffProfile::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
