<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CalendarEvent extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'calendar_id',
        'title',
        'description',
        'type',
        'source',
        'source_id',
        'color',
        'all_day',
        'start_at',
        'end_at',
        'location',
        'is_recurring',
        'recurrence_rule',
        'recurrence_end',
        'created_by',
        'visibility',
        'status',
    ];

    protected $casts = [
        'all_day'        => 'boolean',
        'is_recurring'   => 'boolean',
        'start_at'       => 'datetime',
        'end_at'         => 'datetime',
        'recurrence_end' => 'date',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(CalendarEventAttendee::class, 'event_id');
    }

    public function attendeeUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            CalendarEventAttendee::class,
            'event_id',   // FK on attendees table
            'id',         // FK on users table
            'id',         // local key on calendar_events
            'user_id'     // local key on attendees
        );
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(CalendarEventReminder::class, 'event_id');
    }
}
