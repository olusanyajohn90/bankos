<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatCallParticipant extends Model
{
    public $timestamps = false;

    protected $table = 'chat_call_participants';

    protected $fillable = [
        'call_id',
        'user_id',
        'joined_at',
        'left_at',
        'is_muted',
        'is_video_on',
        'is_screen_sharing',
    ];

    protected $casts = [
        'joined_at'         => 'datetime',
        'left_at'           => 'datetime',
        'is_muted'          => 'boolean',
        'is_video_on'       => 'boolean',
        'is_screen_sharing' => 'boolean',
    ];

    public function call(): BelongsTo
    {
        return $this->belongsTo(ChatCall::class, 'call_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
