<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPresence extends Model
{
    public $timestamps = false;

    protected $table = 'chat_presence';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'last_seen_at',
        'typing_in',
        'typing_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'typing_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typingInConversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'typing_in');
    }
}
