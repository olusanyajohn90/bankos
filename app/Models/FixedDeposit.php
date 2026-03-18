<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class FixedDeposit extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_id', 'customer_id', 'source_account_id',
        'fd_number', 'principal_amount', 'interest_rate', 'tenure_days',
        'start_date', 'maturity_date', 'expected_interest', 'accrued_interest',
        'paid_interest', 'status', 'auto_rollover', 'liquidated_at',
        'liquidation_amount', 'penalty_amount', 'liquidation_reason',
        'created_by', 'branch_id',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'maturity_date'      => 'date',
        'liquidated_at'      => 'datetime',
        'principal_amount'   => 'decimal:2',
        'interest_rate'      => 'decimal:3',
        'expected_interest'  => 'decimal:2',
        'accrued_interest'   => 'decimal:2',
        'paid_interest'      => 'decimal:2',
        'liquidation_amount' => 'decimal:2',
        'penalty_amount'     => 'decimal:2',
        'auto_rollover'      => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(FixedDepositProduct::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sourceAccount()
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeMaturingBefore($q, $date)
    {
        return $q->where('maturity_date', '<=', $date)->where('status', 'active');
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->startOfDay()->diffInDays($this->maturity_date, false));
    }

    public function getIsMaturedAttribute(): bool
    {
        return $this->maturity_date->isPast() && $this->status === 'active';
    }
}
