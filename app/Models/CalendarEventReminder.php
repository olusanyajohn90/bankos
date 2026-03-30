<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEventReminder extends Model
{
    protected $fillable = [
        'event_id',
        'minutes_before',
        'is_sent',
    ];

    protected $casts = [
        'is_sent'        => 'boolean',
        'minutes_before' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }
}
