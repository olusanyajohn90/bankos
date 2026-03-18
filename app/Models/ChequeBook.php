<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ChequeBook extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'account_id', 'series_start', 'series_end',
        'leaves', 'leaves_used', 'issued_date', 'status', 'issued_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'leaves'      => 'integer',
        'leaves_used' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function cheques()
    {
        return $this->hasMany(ChequeTransaction::class, 'cheque_book_id');
    }

    public function getLeavesRemainingAttribute(): int
    {
        return max(0, $this->leaves - $this->leaves_used);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
