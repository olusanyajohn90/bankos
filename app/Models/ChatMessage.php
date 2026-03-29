<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatMessage extends Model
{
    use HasUuids;

    protected $table = 'chat_messages';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'sender_id',
        'reply_to_id',
        'body',
        'type',
        'is_edited',
        'edited_at',
        'is_deleted',
        'deleted_at',
        'delivery_status',
        'is_disappearing',
        'disappear_at',
    ];

    protected $casts = [
        'is_edited'       => 'boolean',
        'edited_at'       => 'datetime',
        'is_deleted'      => 'boolean',
        'deleted_at'      => 'datetime',
        'is_disappearing' => 'boolean',
        'disappear_at'    => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ChatAttachment::class, 'message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatReaction::class, 'message_id');
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(ChatReadReceipt::class, 'message_id');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(ChatPoll::class, 'message_id');
    }

    public function task(): HasOne
    {
        return $this->hasOne(ChatTask::class, 'message_id');
    }

    public function pinnedIn(): HasMany
    {
        return $this->hasMany(ChatPinnedMessage::class, 'message_id');
    }

    public function starredBy(): HasMany
    {
        return $this->hasMany(ChatStarredMessage::class, 'message_id');
    }

    public function getBodyAttribute($value): string
    {
        if ($this->is_deleted) return '[Message deleted]';
        return $value ?? '';
    }
}
