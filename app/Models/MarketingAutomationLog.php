<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomationLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'automation_id', 'customer_id', 'step_index',
        'action_type', 'status', 'scheduled_at', 'executed_at', 'result',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'executed_at'  => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(MarketingAutomation::class, 'automation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
