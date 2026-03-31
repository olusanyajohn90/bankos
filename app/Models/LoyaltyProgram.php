<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'is_active',
        'tiers', 'earning_rules', 'redemption_options', 'points_expiry_months',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'tiers'              => 'array',
        'earning_rules'      => 'array',
        'redemption_options' => 'array',
    ];

    public function points(): HasMany
    {
        return $this->hasMany(LoyaltyPoints::class, 'program_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'program_id');
    }
}
