<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentHolding extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'portfolio_id', 'asset_type', 'asset_name', 'asset_code',
        'quantity', 'cost_price', 'current_price', 'market_value',
        'purchase_date', 'maturity_date', 'yield_rate', 'status',
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'cost_price'    => 'decimal:4',
        'current_price' => 'decimal:4',
        'market_value'  => 'decimal:2',
        'purchase_date' => 'date',
        'maturity_date' => 'date',
        'yield_rate'    => 'decimal:4',
    ];

    public function portfolio()
    {
        return $this->belongsTo(InvestmentPortfolio::class, 'portfolio_id');
    }
}
