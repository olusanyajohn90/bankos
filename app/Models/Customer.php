<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, BelongsToTenant, Notifiable;

    /**
     * The column used as the password for Sanctum token auth.
     */
    protected $hidden = [
        'portal_password',
        'portal_pin',
    ];

    protected $fillable = [
        'tenant_id', 'branch_id', 'customer_number', 'type', 'first_name', 'middle_name',
        'last_name', 'date_of_birth', 'gender', 'email', 'phone', 'occupation',
        'marital_status', 'address', 'bvn', 'nin', 'bvn_verified', 'nin_verified',
        'kyc_tier', 'kyc_status', 'status',
        'portal_password', 'portal_pin', 'portal_active', 'last_login_at',
    ];

    protected $casts = [
        'address'        => 'array',
        'bvn_verified'   => 'boolean',
        'nin_verified'   => 'boolean',
        'date_of_birth'  => 'date',
        'portal_active'  => 'boolean',
        'last_login_at'  => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationships
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function bureauReports()
    {
        return $this->hasMany(BureauReport::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }

    public function insurancePolicies()
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function crossSells()
    {
        return $this->hasMany(MarketingCrossSell::class);
    }
}
