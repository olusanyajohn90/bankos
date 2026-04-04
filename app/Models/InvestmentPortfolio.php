<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPortfolio extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'portfolio_name', 'risk_profile',
        'total_value', 'total_cost', 'unrealized_pnl', 'status', 'advisor_id',
    ];

    protected $casts = [
        'total_value'    => 'decimal:2',
        'total_cost'     => 'decimal:2',
        'unrealized_pnl' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function holdings()
    {
        return $this->hasMany(InvestmentHolding::class, 'portfolio_id');
    }
}
