<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingOfferRedemption extends Model
{
    use HasUuids;

    protected $fillable = [
        'offer_id', 'customer_id', 'discount_amount', 'applied_to',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(MarketingOffer::class, 'offer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
