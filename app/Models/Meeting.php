<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'group_id', 'conducted_by',
        'meeting_date', 'meeting_time', 'location',
        'notes', 'total_collected', 'status',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'total_collected' => 'decimal:2',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function getPresentCountAttribute(): int
    {
        return $this->attendances()->where('present', true)->count();
    }
}
