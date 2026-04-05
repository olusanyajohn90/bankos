<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SuspiciousActivityReport extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'report_type', 'reference', 'customer_id',
        'narrative', 'transactions_involved', 'total_amount',
        'suspicion_category', 'status', 'filing_reference',
        'prepared_by', 'approved_by', 'filed_at',
    ];

    protected $casts = [
        'transactions_involved' => 'array',
        'total_amount'          => 'decimal:2',
        'filed_at'              => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
