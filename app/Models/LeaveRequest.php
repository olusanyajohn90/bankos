<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'leave_type_id', 'start_date', 'end_date', 'days_requested', 'reason', 'status', 'approver_id', 'approved_at', 'rejection_reason', 'relief_officer_id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'days_requested' => 'float',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approver_id'); }
    public function reliefOfficer(): BelongsTo { return $this->belongsTo(User::class, 'relief_officer_id'); }
    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
}
