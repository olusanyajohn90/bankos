<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeFinanceInstrument extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'reference', 'type', 'beneficiary_name',
        'beneficiary_bank', 'amount', 'currency', 'issue_date', 'expiry_date',
        'purpose', 'terms', 'commission_rate', 'commission_amount', 'status',
        'documents', 'created_by',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'commission_rate'   => 'decimal:4',
        'commission_amount' => 'decimal:2',
        'issue_date'        => 'date',
        'expiry_date'       => 'date',
        'documents'         => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
