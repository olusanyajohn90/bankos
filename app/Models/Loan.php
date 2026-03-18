<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'customer_id', 'account_id', 'product_id', 'group_id',
        'parent_loan_id', 'officer_id', 'referral_code',
        'loan_number', 'principal_amount', 'outstanding_balance', 'interest_rate',
        'interest_method', 'amortization', 'tenure_days', 'repayment_frequency',
        'purpose', 'source_channel', 'collateral_desc', 'collateral_value',
        'ai_credit_score', 'bureau_report_id', 'ifrs9_stage', 'ecl_provision',
        'status', 'disbursed_at', 'expected_maturity_date',
        'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'collateral_value' => 'decimal:2',
        'ecl_provision' => 'decimal:2',
        'disbursed_at' => 'datetime',
        'expected_maturity_date' => 'date',
    ];

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class, 'product_id');
    }

    public function product()
    {
        return $this->loanProduct();
    }

    /** The loan this one was restructured from. */
    public function parentLoan()
    {
        return $this->belongsTo(Loan::class, 'parent_loan_id');
    }

    /** The new loan created when this loan was restructured. */
    public function restructuredTo()
    {
        return $this->hasOne(Loan::class, 'parent_loan_id');
    }

    public function restructures()
    {
        return $this->hasMany(LoanRestructure::class);
    }

    public function liquidations()
    {
        return $this->hasMany(LoanLiquidation::class);
    }

    public function topups()
    {
        return $this->hasMany(LoanTopup::class);
    }

    // --- Accessors for UI Compatibility ---
    
    public function getLoanReferenceAttribute() {
        return $this->loan_number;
    }

    public function getTotalInterestAttribute() {
        // Correct banking formula: principal × (annual_rate / 12) × tenure_months
        return round((float)$this->principal_amount * ($this->interest_rate / 100 / 12) * $this->tenure_days, 2);
    }

    public function getTotalPayableAttribute() {
        return round((float)$this->principal_amount + $this->getTotalInterestAttribute(), 2);
    }

    public function getAmountPaidAttribute() {
        return $this->getTotalPayableAttribute() - $this->outstanding_balance;
    }

    public function getDurationAttribute() {
        return $this->tenure_days;
    }

    public function getDurationTypeAttribute() {
        return 'months';
    }

    public function getExpectedMaturityDateAttribute() {
        if (!$this->disbursed_at) return null;
        return \Carbon\Carbon::parse($this->disbursed_at)->addMonths($this->tenure_days);
    }

    public function getDisbursementAccountIdAttribute() {
        return $this->account_id;
    }

    /**
     * The remaining principal component of the outstanding balance.
     * Uses a proportional method: strips the unearned future interest so the
     * customer is not charged for time they won't use the money.
     *
     * outstanding_principal = principal_amount × (outstanding_balance / total_payable)
     */
    public function getOutstandingPrincipalAttribute(): float
    {
        $totalPayable = $this->total_payable;
        if ($totalPayable <= 0) return (float) $this->principal_amount;
        $ratio = (float) $this->outstanding_balance / $totalPayable;
        return round((float) $this->principal_amount * $ratio, 2);
    }

    /**
     * Months remaining = original tenure minus the number of installments
     * already paid. Derived from actual DB figures (amount_paid ÷ installment),
     * so it reflects real payments rather than calendar time.
     */
    public function getRemainingMonthsAttribute(): int
    {
        if ($this->tenure_days <= 0) return 0;
        $installment   = $this->total_payable / $this->tenure_days;
        $paidMonths    = $installment > 0 ? (int) floor($this->amount_paid / $installment) : 0;
        return max(0, $this->tenure_days - $paidMonths);
    }


    /**
     * Generate the full monthly amortization schedule.
     * Uses a cumulative-paid approach to mark each installment as Paid / Overdue / Upcoming.
     */
    public function getAmortizationScheduleAttribute(): array
    {
        if (!$this->disbursed_at || $this->tenure_days <= 0) return [];

        $disbursedAt  = \Carbon\Carbon::parse($this->disbursed_at);
        $totalPayable = $this->total_payable;
        $installment  = round($totalPayable / $this->tenure_days, 2);

        // Per-installment breakdown (flat-rate: constant split across all periods)
        $principalPerInstalment = round((float)$this->principal_amount / $this->tenure_days, 2);
        $interestPerInstalment  = round($installment - $principalPerInstalment, 2);

        $cumulativePaid = $this->amount_paid;
        $schedule = [];

        for ($i = 1; $i <= $this->tenure_days; $i++) {
            $dueDate        = $disbursedAt->copy()->addMonths($i);
            $expectedPaidBy = round($installment * $i, 2);
            $isPastDue      = $dueDate->isPast();

            if ($cumulativePaid >= $expectedPaidBy) {
                $status = 'paid';
            } elseif ($isPastDue) {
                $status = 'overdue';
            } else {
                $status = 'upcoming';
            }

            $schedule[] = [
                'number'    => $i,
                'due_date'  => $dueDate,
                'amount'    => $installment,
                'principal' => $principalPerInstalment,
                'interest'  => $interestPerInstalment,
                'status'    => $status,
            ];
        }

        return $schedule;
    }

    /**
     * IFRS 9 / CBN Loan Performance Classification based on Days Past Due (DPD).
     * Bands: Performing (0), Watch (1-29), Substandard (30-89), Doubtful (90-179), Loss (180+).
     */
    public function getPerformanceClassAttribute(): array
    {
        if (in_array($this->status, ['pending', 'approved'])) {
            return ['label' => 'Pre-Disbursement', 'stage' => 0, 'color' => 'blue', 'badge' => 'bg-blue-100 text-blue-700 border-blue-200', 'dpd' => 0];
        }
        if ($this->status === 'closed') {
            return ['label' => 'Closed / Settled', 'stage' => 0, 'color' => 'gray', 'badge' => 'bg-gray-100 text-gray-600 border-gray-200', 'dpd' => 0];
        }
        if (!$this->disbursed_at) {
            return ['label' => 'Unclassified', 'stage' => 0, 'color' => 'gray', 'badge' => 'bg-gray-100 text-gray-500 border-gray-200', 'dpd' => 0];
        }

        $disbursedAt    = \Carbon\Carbon::parse($this->disbursed_at);
        $monthsElapsed  = max(0, (int) $disbursedAt->diffInMonths(now()));
        $totalPayable   = $this->total_payable;
        $installment    = $this->tenure_days > 0 ? ($totalPayable / $this->tenure_days) : 0;
        $expectedByNow  = min($installment * $monthsElapsed, $totalPayable);
        $actualPaid     = $this->amount_paid;
        $deficiency     = max(0, $expectedByNow - $actualPaid);

        // Approximate DPD: how many days of missed payment
        $dpd = $installment > 0 ? (int) round(($deficiency / $installment) * 30) : 0;

        if ($dpd === 0)   return ['label' => 'Performing',    'stage' => 1, 'color' => 'green',  'badge' => 'bg-green-100 text-green-700 border-green-200',   'dpd' => $dpd];
        if ($dpd < 30)    return ['label' => 'Watch List',    'stage' => 2, 'color' => 'yellow', 'badge' => 'bg-yellow-100 text-yellow-700 border-yellow-200', 'dpd' => $dpd];
        if ($dpd < 90)    return ['label' => 'Substandard',   'stage' => 3, 'color' => 'orange', 'badge' => 'bg-orange-100 text-orange-700 border-orange-200', 'dpd' => $dpd];
        if ($dpd < 180)   return ['label' => 'Doubtful',      'stage' => 4, 'color' => 'red',    'badge' => 'bg-red-100 text-red-700 border-red-200',          'dpd' => $dpd];
        return                   ['label' => 'Loss',          'stage' => 5, 'color' => 'darkred','badge' => 'bg-red-200 text-red-900 border-red-400',           'dpd' => $dpd];
    }

    public function creditDecision()
    {
        return $this->hasOne(CreditDecision::class, 'loan_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }
}
