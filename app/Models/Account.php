<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'customer_id', 'account_number', 'account_name',
        'type', 'currency', 'available_balance', 'ledger_balance',
        'savings_product_id', 'status', 'opened_by', 'referral_code',
        'pnd_active', 'pnd_reason', 'pnd_placed_by', 'pnd_placed_at',
        'dormant_since', 'closed_at', 'closure_reason', 'closed_by',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'ledger_balance' => 'decimal:2',
        'pnd_active' => 'boolean',
        'pnd_placed_at' => 'datetime',
        'dormant_since' => 'date',
        'closed_at' => 'date',
    ];

    public function openedByUser()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function savingsProduct()
    {
        return $this->belongsTo(SavingsProduct::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }

    public function liens() { return $this->hasMany(\App\Models\AccountLien::class); }
    public function activeLiens() { return $this->hasMany(\App\Models\AccountLien::class)->where('is_active', true); }
    public function getTotalLienAmountAttribute(): float {
        return (float) $this->activeLiens()->sum('amount');
    }
    public function getEffectiveAvailableBalanceAttribute(): float {
        return max(0, (float)$this->available_balance - $this->total_lien_amount);
    }
    public function overdraftFacility() { return $this->hasOne(\App\Models\OverdraftFacility::class); }

    public function mandate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\AccountMandate::class)->where('is_active', true);
    }
}
