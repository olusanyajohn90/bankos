<?php

namespace App\Models\Visitor;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorMeetingAttendee extends Model
{
    protected $table = 'visitor_meeting_attendees';

    protected $fillable = ['meeting_id', 'visitor_id', 'user_id', 'type', 'attendance_status'];

    public function meeting(): BelongsTo { return $this->belongsTo(VisitorMeeting::class, 'meeting_id'); }
    public function visitor(): BelongsTo { return $this->belongsTo(Visitor::class, 'visitor_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
