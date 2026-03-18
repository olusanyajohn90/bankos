<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsProduct extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'currency',
        'interest_rate', 'interest_frequency', 'min_balance', 'min_opening',
        'max_withdrawal_daily', 'max_withdrawals_monthly', 'lock_in_period',
        'early_withdrawal_penalty', 'monthly_fee', 'min_balance_penalty',
        'product_type', 'goal_target', 'maturity_date', 'status',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'min_balance' => 'decimal:2',
        'min_opening' => 'decimal:2',
        'maturity_date' => 'date',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
