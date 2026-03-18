<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class LeaveType extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'days_entitled', 'carry_over_days', 'gender_restriction', 'requires_approval', 'is_paid', 'is_active'];

    protected $casts = [
        'days_entitled' => 'float',
        'carry_over_days' => 'float',
        'requires_approval' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaveBalances(): HasMany { return $this->hasMany(LeaveBalance::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
