<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'user_id', 'first_name', 'last_name', 'phone',
        'email', 'bvn', 'nin', 'address', 'float_balance', 'daily_cash_in_limit',
        'daily_cash_out_limit', 'daily_transfer_limit', 'commission_rate',
        'home_latitude', 'home_longitude', 'total_commission_earned', 'status',
    ];

    protected $casts = [
        'float_balance'           => 'decimal:2',
        'daily_cash_in_limit'     => 'decimal:2',
        'daily_cash_out_limit'    => 'decimal:2',
        'daily_transfer_limit'    => 'decimal:2',
        'commission_rate'         => 'decimal:4',
        'home_latitude'           => 'decimal:7',
        'home_longitude'          => 'decimal:7',
        'total_commission_earned' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function floatTransactions(): HasMany
    {
        return $this->hasMany(AgentFloatTransaction::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(AgentVisit::class);
    }
}
