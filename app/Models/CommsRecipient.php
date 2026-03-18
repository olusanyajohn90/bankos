<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommsRecipient extends Model
{
    // bigIncrements PK — no HasUuids

    protected $table = 'comms_recipients';

    protected $fillable = [
        'tenant_id',
        'message_id',
        'user_id',
        'read_at',
        'ack_at',
        'ack_note',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'ack_at'  => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CommsMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
