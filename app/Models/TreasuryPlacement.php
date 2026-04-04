<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryPlacement extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'reference', 'type', 'counterparty', 'principal',
        'interest_rate', 'start_date', 'maturity_date', 'tenor_days',
        'expected_interest', 'accrued_interest', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'principal'         => 'decimal:2',
        'interest_rate'     => 'decimal:4',
        'expected_interest' => 'decimal:2',
        'accrued_interest'  => 'decimal:2',
        'start_date'        => 'date',
        'maturity_date'     => 'date',
        'tenor_days'        => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
