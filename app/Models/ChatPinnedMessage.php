<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPinnedMessage extends Model
{
    public $timestamps = false;

    protected $table = 'chat_pinned_messages';

    protected $fillable = [
        'conversation_id',
        'message_id',
        'pinned_by',
        'pinned_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function pinnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }
}
