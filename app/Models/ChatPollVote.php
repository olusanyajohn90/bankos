<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPollVote extends Model
{
    public $timestamps = false;

    protected $table = 'chat_poll_votes';

    protected $fillable = [
        'poll_id',
        'option_id',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(ChatPoll::class, 'poll_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ChatPollOption::class, 'option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
