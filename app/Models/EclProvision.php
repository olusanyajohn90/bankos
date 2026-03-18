<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclProvision extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'loan_id', 'customer_id', 'days_past_due', 'stage',
        'outstanding_balance', 'probability_of_default', 'loss_given_default',
        'exposure_at_default', 'ecl_amount', 'reporting_date',
    ];

    protected $casts = [
        'outstanding_balance'     => 'decimal:2',
        'probability_of_default'  => 'decimal:6',
        'loss_given_default'      => 'decimal:6',
        'exposure_at_default'     => 'decimal:2',
        'ecl_amount'              => 'decimal:2',
        'reporting_date'          => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getStageLabelAttribute(): string
    {
        return match($this->stage) {
            1 => 'Stage 1 — Performing',
            2 => 'Stage 2 — Underperforming',
            3 => 'Stage 3 — Non-Performing',
            default => 'Unknown',
        };
    }
}
