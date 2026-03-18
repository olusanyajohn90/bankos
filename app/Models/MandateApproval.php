<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MandateApproval extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'account_id',
        'mandate_id',
        'description',
        'amount',
        'reference',
        'required_approvals',
        'approvals_received',
        'status',
        'requested_by',
        'expires_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'metadata'          => 'array',
        'expires_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'amount'            => 'decimal:2',
    ];

    // Relationships

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function mandate()
    {
        return $this->belongsTo(AccountMandate::class, 'mandate_id');
    }

    public function actions()
    {
        return $this->hasMany(MandateApprovalAction::class, 'approval_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Scopes

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
