<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CreditDecision extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'loan_id',
        'policy_id',
        'bureau_score',
        'internal_score',
        'final_score',
        'recommendation',
        'auto_decided',
        'rules_passed',
        'rules_failed',
        'conditions',
        'notes',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'auto_decided' => 'boolean',
        'rules_passed' => 'array',
        'rules_failed' => 'array',
        'conditions'   => 'array',
        'decided_at'   => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function policy()
    {
        return $this->belongsTo(CreditPolicy::class, 'policy_id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
