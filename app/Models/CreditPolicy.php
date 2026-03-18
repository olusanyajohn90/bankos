<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CreditPolicy extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'loan_product_id',
        'is_active',
        'auto_approve_above',
        'auto_decline_below',
        'require_bureau_report',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'require_bureau_report' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function rules()
    {
        return $this->hasMany(CreditPolicyRule::class, 'policy_id');
    }

    public function decisions()
    {
        return $this->hasMany(CreditDecision::class, 'policy_id');
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
