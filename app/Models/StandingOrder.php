<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

class StandingOrder extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'source_account_id', 'beneficiary_account_number',
        'beneficiary_bank_code', 'beneficiary_name', 'internal_dest_account_id',
        'transfer_type', 'amount', 'narration', 'frequency',
        'start_date', 'end_date', 'next_run_date', 'max_runs',
        'runs_completed', 'last_run_at', 'status', 'last_failure_reason', 'created_by',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'next_run_date'  => 'date',
        'last_run_at'    => 'datetime',
        'amount'         => 'decimal:2',
    ];

    public function sourceAccount()
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function internalDestAccount()
    {
        return $this->belongsTo(Account::class, 'internal_dest_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDue($q)
    {
        return $q->where('status', 'active')->where('next_run_date', '<=', now()->toDateString());
    }

    public function computeNextRunDate(): Carbon
    {
        $base = $this->next_run_date ?? now();
        return match ($this->frequency) {
            'daily'     => $base->copy()->addDay(),
            'weekly'    => $base->copy()->addWeek(),
            'monthly'   => $base->copy()->addMonth(),
            'quarterly' => $base->copy()->addMonths(3),
            'yearly'    => $base->copy()->addYear(),
        };
    }
}
