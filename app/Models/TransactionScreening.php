<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TransactionScreening extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'transaction_id', 'customer_id', 'screening_type',
        'result', 'confidence', 'match_details', 'reason_codes',
        'disposition', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'confidence'    => 'decimal:2',
        'match_details' => 'array',
        'reason_codes'  => 'array',
        'reviewed_at'   => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
