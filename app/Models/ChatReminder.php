<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatReminder extends Model
{
    use HasUuids;

    protected $table = 'chat_reminders';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'conversation_id',
        'message_id',
        'note',
        'remind_at',
        'is_fired',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'is_fired'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }
}
