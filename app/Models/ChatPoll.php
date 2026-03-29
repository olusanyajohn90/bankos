<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatPoll extends Model
{
    use HasUuids;

    protected $table = 'chat_polls';

    protected $fillable = [
        'message_id',
        'conversation_id',
        'question',
        'allow_multiple',
        'is_anonymous',
        'is_closed',
        'closes_at',
    ];

    protected $casts = [
        'allow_multiple' => 'boolean',
        'is_anonymous'   => 'boolean',
        'is_closed'      => 'boolean',
        'closes_at'      => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ChatPollOption::class, 'poll_id')->orderBy('sort_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChatPollVote::class, 'poll_id');
    }
}
