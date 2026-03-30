<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventAttendee extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'notified_at',
        'responded_at',
    ];

    protected $casts = [
        'notified_at'  => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
