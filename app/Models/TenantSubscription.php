<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'paystack_subscription_code',
        'paystack_customer_code',
        'amount_paid',
        'billing_cycle',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'date',
        'current_period_end'   => 'date',
        'cancelled_at'         => 'datetime',
        'amount_paid'          => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(TenantInvoice::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active'    => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            'trial'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'past_due'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
            'cancelled' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
