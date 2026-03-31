<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingUnsubscribe extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'customer_id', 'channel', 'unsubscribed_at', 'reason',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
