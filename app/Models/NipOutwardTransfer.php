<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NipOutwardTransfer extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'nip_outward_transfers';

    protected $fillable = [
        'tenant_id',
        'initiated_by',
        'source_account_id',
        'session_id',
        'name_enquiry_ref',
        'sender_account_number',
        'sender_account_name',
        'sender_bank_code',
        'beneficiary_account_number',
        'beneficiary_account_name',
        'beneficiary_bank_code',
        'beneficiary_bank_name',
        'amount',
        'narration',
        'status',
        'nibss_response_code',
        'nibss_session_id',
        'failure_reason',
        'initiated_at',
        'completed_at',
        'reversed_at',
    ];

    protected $casts = [
        'status'       => 'string',
        'amount'       => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'reversed_at'  => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────────────────────

    public function sourceAccount()
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Retrieve the BankList entry for the beneficiary bank.
     * Not a standard Eloquent relation — resolved by CBN code lookup.
     */
    public function getBank(): ?BankList
    {
        return BankList::findByCode($this->beneficiary_bank_code);
    }
}
