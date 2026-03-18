<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StaffProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'user_id', 'branch_id', 'manager_id', 'team_id',
        'department', 'job_title', 'staff_code', 'referral_code',
        'joined_date', 'employment_type', 'status',
        'region_id', 'department_id', 'grade_level', 'cost_centre_code',
        'employee_number', 'confirmation_date', 'exit_date', 'exit_reason',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'confirmation_date' => 'date',
        'exit_date' => 'date',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function branch(): BelongsTo  { return $this->belongsTo(Branch::class); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function team(): BelongsTo    { return $this->belongsTo(Team::class); }

    public function region(): BelongsTo { return $this->belongsTo(Region::class); }
    public function orgDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }

    public function leaveBalances(): HasMany { return $this->hasMany(LeaveBalance::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
    public function disciplinaryCases(): HasMany { return $this->hasMany(DisciplinaryCase::class); }
    public function performanceReviews(): HasMany { return $this->hasMany(PerformanceReview::class); }
    public function trainingAttendances(): HasMany { return $this->hasMany(TrainingAttendance::class); }
    public function certifications(): HasMany { return $this->hasMany(StaffCertification::class); }
    public function documents(): HasMany { return $this->hasMany(StaffDocument::class); }
    public function payConfig(): HasOne { return $this->hasOne(StaffPayConfig::class); }
    public function bankDetails(): HasMany { return $this->hasMany(StaffBankDetail::class); }
    public function payrollItems(): HasMany { return $this->hasMany(PayrollItem::class); }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'officer_id', 'user_id');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'opened_by', 'user_id');
    }

    public function polyDocuments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }
}
