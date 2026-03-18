<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDeliveryLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'endpoint_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'attempt_count',
        'delivered_at',
        'failed_at',
        'created_at',
    ];

    protected $casts = [
        'payload'       => 'array',
        'response_code' => 'integer',
        'attempt_count' => 'integer',
        'delivered_at'  => 'datetime',
        'failed_at'     => 'datetime',
        'created_at'    => 'datetime',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'endpoint_id');
    }
}
