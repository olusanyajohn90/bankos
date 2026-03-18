<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class PayrollRun extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'period_month', 'period_year', 'status', 'total_gross', 'total_deductions', 'total_net', 'total_paye', 'total_pension_employee', 'total_pension_employer', 'total_nhf', 'total_nsitf', 'staff_count', 'run_by', 'approved_by', 'approved_at', 'paid_at', 'notes'];

    protected $casts = [
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'total_gross' => 'float',
        'total_deductions' => 'float',
        'total_net' => 'float',
        'total_paye' => 'float',
        'total_pension_employee' => 'float',
        'total_pension_employer' => 'float',
        'total_nhf' => 'float',
        'total_nsitf' => 'float',
    ];

    public function runBy(): BelongsTo { return $this->belongsTo(User::class, 'run_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(PayrollItem::class); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePaid($q) { return $q->where('status', 'paid'); }

    public function getPeriodLabelAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->period_month, 1)) . ' ' . $this->period_year;
    }
}
