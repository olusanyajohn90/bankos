<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class TellerSession extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'branch_id', 'teller_id', 'session_date',
        'opening_cash', 'cash_in', 'cash_out', 'closing_cash',
        'expected_closing', 'variance', 'status', 'notes', 'supervised_by',
    ];

    protected $casts = [
        'session_date'     => 'date',
        'opening_cash'     => 'decimal:2',
        'cash_in'          => 'decimal:2',
        'cash_out'         => 'decimal:2',
        'closing_cash'     => 'decimal:2',
        'expected_closing' => 'decimal:2',
        'variance'         => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervised_by');
    }

    public function vaultEntries()
    {
        return $this->hasMany(VaultEntry::class, 'teller_session_id');
    }

    public function cashCounts()
    {
        return $this->hasMany(CashCount::class, 'teller_session_id');
    }

    public function getExpectedClosingAttribute(): float
    {
        return (float) $this->opening_cash
            + (float) ($this->attributes['cash_in'] ?? 0)
            - (float) ($this->attributes['cash_out'] ?? 0);
    }

    public function scopeOpen($q)
    {
        return $q->where('status', 'open');
    }
}
