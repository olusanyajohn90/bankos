<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryFxDeal extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'reference', 'deal_type', 'direction', 'currency_pair',
        'amount', 'rate', 'counter_amount', 'trade_date', 'settlement_date',
        'status', 'counterparty', 'dealer_id',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'rate'           => 'decimal:6',
        'counter_amount' => 'decimal:2',
        'trade_date'     => 'date',
        'settlement_date'=> 'date',
    ];

    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }
}
