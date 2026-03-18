<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    use HasUuids;

    protected $fillable = ['payroll_run_id', 'staff_profile_id', 'gross_salary', 'taxable_income', 'total_deductions', 'paye', 'employee_pension', 'employer_pension', 'nhf', 'nsitf', 'net_salary', 'bank_detail_id', 'payment_status', 'payment_date'];

    protected $casts = [
        'gross_salary' => 'float',
        'taxable_income' => 'float',
        'total_deductions' => 'float',
        'paye' => 'float',
        'employee_pension' => 'float',
        'employer_pension' => 'float',
        'nhf' => 'float',
        'nsitf' => 'float',
        'net_salary' => 'float',
        'payment_date' => 'datetime',
    ];

    public function payrollRun(): BelongsTo { return $this->belongsTo(PayrollRun::class); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function bankDetail(): BelongsTo { return $this->belongsTo(StaffBankDetail::class); }
    public function lines(): HasMany { return $this->hasMany(PayrollItemLine::class); }
}
