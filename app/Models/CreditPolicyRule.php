<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CreditPolicyRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'policy_id',
        'rule_type',
        'operator',
        'threshold_value',
        'action_on_fail',
        'action_param',
        'severity',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'threshold_value' => 'float',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function policy()
    {
        return $this->belongsTo(CreditPolicy::class, 'policy_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
