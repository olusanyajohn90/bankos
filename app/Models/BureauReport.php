<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BureauReport extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'loan_id', 'bureau', 'reference',
        'file_path', 'original_filename',
        'credit_score', 'active_loans_count', 'total_outstanding',
        'delinquency_count', 'status', 'raw_response',
        'raw_text', 'parsed_data',
        'retrieved_at', 'uploaded_at',
    ];

    protected $casts = [
        'total_outstanding' => 'decimal:2',
        'raw_response'      => 'array',
        'parsed_data'       => 'array',
        'retrieved_at'      => 'datetime',
        'uploaded_at'       => 'datetime',
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
