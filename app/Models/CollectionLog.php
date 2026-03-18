<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionLog extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'loan_id', 'customer_id', 'officer_id', 'days_past_due',
        'overdue_score', 'action', 'outcome', 'promise_amount', 'promise_date',
        'notes', 'actioned_at',
    ];

    protected $casts = [
        'promise_amount' => 'decimal:2',
        'promise_date'   => 'date',
        'actioned_at'    => 'datetime',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'officer_id');
    }
}
