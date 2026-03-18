<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class CommsMessage extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'comms_messages';

    protected $fillable = [
        'tenant_id',
        'type',
        'subject',
        'body',
        'priority',
        'requires_ack',
        'ack_deadline',
        'sender_id',
        'scope_type',
        'scope_id',
        'status',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'requires_ack' => 'boolean',
        'published_at' => 'datetime',
        'archived_at'  => 'datetime',
        'ack_deadline' => 'date',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommsAttachment::class, 'message_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CommsRecipient::class, 'message_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }

    public function readRate(): string
    {
        $total = $this->recipients()->count();
        if ($total === 0) return '0%';
        $read = $this->recipients()->whereNotNull('read_at')->count();
        return round($read / $total * 100) . '%';
    }

    public function ackRate(): string
    {
        if (!$this->requires_ack) return 'N/A';
        $total = $this->recipients()->count();
        if ($total === 0) return '0%';
        $acked = $this->recipients()->whereNotNull('ack_at')->count();
        return round($acked / $total * 100) . '%';
    }
}
