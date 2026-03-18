<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'leave_type_id', 'year', 'entitled_days', 'used_days', 'pending_days'];

    protected $casts = [
        'entitled_days' => 'float',
        'used_days' => 'float',
        'pending_days' => 'float',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function availableDays(): float { return max(0, $this->entitled_days - $this->used_days - $this->pending_days); }
}
