<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'pair', 'buy_rate', 'sell_rate', 'mid_rate', 'effective_date',
    ];

    protected $casts = [
        'buy_rate' => 'decimal:4',
        'sell_rate' => 'decimal:4',
        'mid_rate' => 'decimal:4',
        'effective_date' => 'date',
    ];
}
