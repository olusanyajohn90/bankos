<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id','created_by','title','body','priority','audience',
        'audience_ref_id','publish_at','expires_at','is_pinned','is_published',
    ];

    protected $casts = [
        'publish_at'   => 'datetime',
        'expires_at'   => 'datetime',
        'is_pinned'    => 'boolean',
        'is_published' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_reads', 'announcement_id', 'user_id')
                    ->withPivot('read_at');
    }

    public function markReadBy(int $userId): void
    {
        $this->readers()->syncWithoutDetaching([$userId => ['read_at' => now()]]);
    }

    public function isReadBy(int $userId): bool
    {
        return $this->readers()->where('user_id', $userId)->exists();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(fn($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
                     ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }
}
