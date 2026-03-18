<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'short_name',
        'type',
        'account_prefix',
        'primary_currency',
        'supported_currencies',
        'domain',
        'cbn_license_number',
        'nibss_institution_code',
        'routing_number',
        'logo_url',
        'contact_email',
        'contact_phone',
        'address',
        'status',
        // Branding & SaaS fields
        'logo_path',
        'primary_color',
        'secondary_color',
        'portal_domain',
        'subscription_plan',
        'onboarding_completed_at',
        'onboarding_step',
        'suspended_at',
        'suspension_reason',
    ];

    protected $casts = [
        'supported_currencies'    => 'array',
        'address'                 => 'array',
        'onboarding_completed_at' => 'datetime',
        'suspended_at'            => 'datetime',
        'onboarding_step'         => 'integer',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function glAccounts()
    {
        return $this->hasMany(GlAccount::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(TenantSubscription::class)->whereIn('status', ['active', 'trial'])->latest();
    }

    public function invoices()
    {
        return $this->hasMany(TenantInvoice::class);
    }

    public function usage()
    {
        return $this->hasMany(TenantUsage::class);
    }

    public function isSuspended(): bool
    {
        return !is_null($this->suspended_at);
    }

    public function logoUrl(): string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('images/bankos-logo.png');
    }
}
