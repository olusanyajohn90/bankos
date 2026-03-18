<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentVisit extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'agent_id', 'customer_id', 'latitude', 'longitude',
        'address_resolved', 'purpose', 'notes', 'amount_collected', 'visited_at',
    ];

    protected $casts = [
        'latitude'         => 'decimal:7',
        'longitude'        => 'decimal:7',
        'amount_collected' => 'decimal:2',
        'visited_at'       => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
