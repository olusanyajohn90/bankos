<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'url',
        'secret',
        'events',
        'is_active',
        'last_triggered_at',
        'failure_count',
    ];

    protected $casts = [
        'events'            => 'array',
        'is_active'         => 'boolean',
        'last_triggered_at' => 'datetime',
        'failure_count'     => 'integer',
    ];

    protected $hidden = [
        'secret', // Never expose the secret in API responses or JSON serialisation
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(WebhookDeliveryLog::class, 'endpoint_id');
    }
}
