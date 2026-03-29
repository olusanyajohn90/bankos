<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatReaction extends Model
{
    public $timestamps = false;

    protected $table = 'chat_reactions';

    protected $fillable = [
        'message_id',
        'user_id',
        'emoji',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
