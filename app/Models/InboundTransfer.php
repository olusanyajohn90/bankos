<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundTransfer extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'account_id', 'session_id',
        'sender_name', 'sender_account', 'sender_bank',
        'destination_account', 'amount', 'currency',
        'channel', 'narration', 'source', 'posting_type',
        'status', 'transaction_id', 'raw_payload',
        'received_at', 'posted_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'received_at' => 'datetime',
        'posted_at'   => 'datetime',
        'raw_payload' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
