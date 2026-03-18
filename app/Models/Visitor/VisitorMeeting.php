<?php

namespace App\Models\Visitor;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorMeeting extends Model
{
    use HasUuids;

    protected $table = 'visitor_meetings';

    protected $fillable = [
        'tenant_id', 'visit_id', 'room_id', 'organiser_id', 'title', 'agenda',
        'minutes', 'status', 'scheduled_at', 'duration_minutes', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
    ];

    public function organiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organiser_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(VisitorMeetingRoom::class, 'room_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(VisitorVisit::class, 'visit_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(VisitorMeetingAttendee::class, 'meeting_id');
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'scheduled'   => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-green-100 text-green-700',
            'completed'   => 'bg-gray-100 text-gray-600',
            'cancelled'   => 'bg-red-100 text-red-700',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
