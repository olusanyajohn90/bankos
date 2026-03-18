<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlPosting extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'gl_account_id', 'transaction_id', 'reference',
        'debit_amount', 'credit_amount', 'balance_after', 'description',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function glAccount()
    {
        return $this->belongsTo(GlAccount::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
