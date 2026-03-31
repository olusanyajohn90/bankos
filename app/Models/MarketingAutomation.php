<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingAutomation extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'is_active',
        'trigger', 'conditions', 'actions',
        'enrolled_count', 'completed_count', 'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'trigger'    => 'array',
        'conditions' => 'array',
        'actions'    => 'array',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MarketingAutomationLog::class, 'automation_id');
    }
}
