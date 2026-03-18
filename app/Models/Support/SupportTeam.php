<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class SupportTeam extends Model
{
    use HasUuids;

    protected $table = 'support_teams';

    protected $fillable = [
        'tenant_id','name','code','division','description','email',
        'team_lead_id','is_active','working_hours',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'working_hours' => 'array',
    ];

    public function teamLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'support_team_members', 'team_id', 'user_id')
                    ->withPivot('role','is_active')->withTimestamps();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'team_id');
    }

    public function openTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'team_id')->whereNotIn('status', ['resolved','closed','cancelled']);
    }
}
