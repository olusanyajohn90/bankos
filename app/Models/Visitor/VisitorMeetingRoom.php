<?php

namespace App\Models\Visitor;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class VisitorMeetingRoom extends Model
{
    use HasUuids;

    protected $table = 'visitor_meeting_rooms';

    protected $fillable = ['tenant_id', 'name', 'location', 'capacity', 'is_available'];

    protected $casts = ['is_available' => 'boolean'];

    public function meetings(): HasMany
    {
        return $this->hasMany(VisitorMeeting::class, 'room_id');
    }

    public function isBookedAt(\DateTime $start, \DateTime $end, ?string $excludeMeetingId = null): bool
    {
        $q = $this->meetings()
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_at', '<', $end)
            ->whereRaw(
                DB::getDriverName() === 'pgsql'
                    ? "(scheduled_at + (duration_minutes || ' minutes')::interval) > ?"
                    : 'DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?',
                [$start]
            );

        if ($excludeMeetingId) $q->where('id', '!=', $excludeMeetingId);

        return $q->exists();
    }
}
