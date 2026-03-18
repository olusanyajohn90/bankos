<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'staff_profile_id', 'date', 'clock_in', 'clock_out',
        'expected_in', 'expected_out', 'status', 'minutes_late',
        'hours_worked', 'overtime_hours', 'is_manually_adjusted', 'notes', 'marked_by',
    ];

    protected $casts = [
        'date'                 => 'date',
        'hours_worked'         => 'float',
        'overtime_hours'       => 'float',
        'is_manually_adjusted' => 'boolean',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function markedBy(): BelongsTo     { return $this->belongsTo(User::class, 'marked_by'); }

    public function isLate(): bool   { return $this->status === 'late' || $this->minutes_late > 0; }
    public function isPresent(): bool { return in_array($this->status, ['present', 'late', 'half_day']); }
}
