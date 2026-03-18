<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AccountMandate extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'account_id',
        'mandate_class',
        'signing_rule',
        'min_signatories',
        'max_amount_sole',
        'description',
        'is_active',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'max_amount_sole'  => 'decimal:2',
        'effective_from'   => 'date',
        'effective_to'     => 'date',
    ];

    // Relationships

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function signatories()
    {
        return $this->hasMany(MandateSignatory::class, 'mandate_id');
    }

    public function approvals()
    {
        return $this->hasMany(MandateApproval::class, 'mandate_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
