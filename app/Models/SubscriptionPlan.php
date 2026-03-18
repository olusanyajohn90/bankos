<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'max_customers',
        'max_staff_users',
        'max_branches',
        'max_transactions_monthly',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price_monthly'            => 'decimal:2',
        'price_yearly'             => 'decimal:2',
        'max_customers'            => 'integer',
        'max_staff_users'          => 'integer',
        'max_branches'             => 'integer',
        'max_transactions_monthly' => 'integer',
        'features'                 => 'array',
        'is_active'                => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(TenantSubscription::class, 'plan_id');
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    public function isUnlimited(): bool
    {
        return is_null($this->max_customers);
    }

    public function formattedMonthlyPrice(): string
    {
        if ($this->price_monthly == 0) {
            return 'Custom';
        }
        return '₦' . number_format($this->price_monthly, 0);
    }

    public function formattedYearlyPrice(): string
    {
        if ($this->price_yearly == 0) {
            return 'Custom';
        }
        return '₦' . number_format($this->price_yearly, 0);
    }
}
