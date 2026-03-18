<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePolicy extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'loan_id', 'policy_number', 'provider',
        'product', 'sum_assured', 'premium', 'premium_frequency',
        'start_date', 'end_date', 'status', 'notes',
    ];

    protected $casts = [
        'sum_assured' => 'decimal:2',
        'premium'     => 'decimal:2',
        'start_date'  => 'date',
        'end_date'    => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
