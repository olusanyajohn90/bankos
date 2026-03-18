<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'branch_id', 'team_lead_id',
        'name', 'department', 'description', 'status', 'is_cross_branch',
    ];

    protected $casts = [
        'is_cross_branch' => 'boolean',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function branch(): BelongsTo  { return $this->belongsTo(Branch::class); }
    public function teamLead(): BelongsTo { return $this->belongsTo(User::class, 'team_lead_id'); }
    public function members(): HasMany   { return $this->hasMany(StaffProfile::class); }
}
