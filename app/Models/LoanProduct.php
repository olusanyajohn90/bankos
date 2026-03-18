<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'currency',
        'interest_rate', 'interest_method', 'amortization',
        'min_amount', 'max_amount', 'min_tenure', 'max_tenure', 'duration_type',
        'max_dti', 'processing_fee', 'insurance_fee', 'grace_period',
        'group_lending', 'ai_assessment', 'early_repayment',
        'early_repayment_penalty', 'collateral_types', 'status',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'collateral_types' => 'array',
        'group_lending' => 'boolean',
        'ai_assessment' => 'boolean',
        'early_repayment' => 'boolean',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class, 'product_id');
    }
}
