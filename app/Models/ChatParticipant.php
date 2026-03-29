<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatParticipant extends Model
{
    protected $table = 'chat_participants';

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
        'left_at',
        'is_muted',
        'muted_until',
    ];

    protected $casts = [
        'joined_at'    => 'datetime',
        'last_read_at' => 'datetime',
        'left_at'      => 'datetime',
        'is_muted'     => 'boolean',
        'muted_until'  => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
