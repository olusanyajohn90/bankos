<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayConfig extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'pay_grade_id', 'basic_salary', 'housing_allowance', 'transport_allowance', 'meal_allowance', 'other_allowances', 'pension_fund_administrator', 'pension_account_number', 'tax_id', 'nhf_number', 'effective_date'];

    protected $casts = [
        'other_allowances' => 'array',
        'basic_salary' => 'float',
        'housing_allowance' => 'float',
        'transport_allowance' => 'float',
        'meal_allowance' => 'float',
        'effective_date' => 'date',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function payGrade(): BelongsTo { return $this->belongsTo(PayGrade::class); }

    public function grossSalary(): float
    {
        $base = $this->basic_salary + $this->housing_allowance + $this->transport_allowance + $this->meal_allowance;
        $other = is_array($this->other_allowances) ? array_sum(array_column($this->other_allowances, 'amount')) : 0;
        return $base + $other;
    }

    public function pensionableBase(): float
    {
        return $this->basic_salary + $this->housing_allowance + $this->transport_allowance;
    }
}
