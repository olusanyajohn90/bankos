<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvance extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id','user_id','staff_profile_id','amount_requested','amount_approved',
        'reason','repayment_months','monthly_deduction','status','approval_request_id',
        'approved_by','approved_at','rejection_reason','disbursed_at','balance_remaining',
    ];

    protected $casts = [
        'amount_requested'  => 'decimal:2',
        'amount_approved'   => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'approved_at'       => 'datetime',
        'disbursed_at'      => 'datetime',
        'repayment_months'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }
}
