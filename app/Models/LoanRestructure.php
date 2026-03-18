<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRestructure extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'loan_id', 'status',
        'previous_outstanding', 'previous_tenure', 'previous_rate',
        'new_tenure', 'new_rate', 'reason', 'officer_notes',
        'requested_by', 'reviewed_by', 'reviewed_at',
        'new_loan_id',
    ];

    protected $casts = [
        'previous_outstanding' => 'decimal:2',
        'previous_rate'        => 'decimal:4',
        'new_rate'             => 'decimal:4',
        'reviewed_at'          => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    public function newLoan()
    {
        return $this->belongsTo(\App\Models\Loan::class, 'new_loan_id');
    }
}
