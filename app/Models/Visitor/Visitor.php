<?php

namespace App\Models\Visitor;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasUuids;

    protected $table = 'visitors';

    protected $fillable = [
        'tenant_id', 'full_name', 'id_type', 'id_number', 'phone', 'email',
        'company', 'photo_path', 'notes', 'is_blacklisted', 'blacklist_reason',
    ];

    protected $casts = ['is_blacklisted' => 'boolean'];

    public function visits(): HasMany
    {
        return $this->hasMany(VisitorVisit::class, 'visitor_id');
    }

    public function watchlist(): HasMany
    {
        return $this->hasMany(VisitorWatchlist::class, 'visitor_id');
    }

    public function lastVisit(): ?VisitorVisit
    {
        return $this->visits()->latest('checked_in_at')->first();
    }

    public function isVip(): bool
    {
        return $this->watchlist()->where('status', 'vip')->exists();
    }
}
