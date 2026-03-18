<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centre extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'name', 'code',
        'meeting_location', 'meeting_day', 'meeting_time', 'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function getActiveMembersCountAttribute(): int
    {
        return $this->groups->sum(fn($g) => $g->activeMembers()->count());
    }
}
