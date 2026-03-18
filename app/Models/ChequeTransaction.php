<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ChequeTransaction extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cheque_book_id', 'account_id', 'cheque_number',
        'payee_name', 'amount', 'issue_date', 'presented_date',
        'cleared_date', 'bounced_date', 'status',
        'drawer_reference', 'bank_reference', 'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'issue_date'     => 'date',
        'presented_date' => 'date',
        'cleared_date'   => 'date',
        'bounced_date'   => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function chequeBook()
    {
        return $this->belongsTo(ChequeBook::class, 'cheque_book_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'cleared'    => 'badge-active',
            'presented'  => 'badge-pending',
            'bounced'    => 'badge-danger',
            'cancelled'  => 'badge-dormant',
            default      => 'badge-dormant',
        };
    }
}
