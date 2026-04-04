<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashPosition extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'position_date', 'currency', 'opening_balance',
        'total_inflows', 'total_outflows', 'closing_balance',
        'vault_cash', 'nostro_balance', 'breakdown', 'prepared_by',
    ];

    protected $casts = [
        'position_date'   => 'date',
        'opening_balance' => 'decimal:2',
        'total_inflows'   => 'decimal:2',
        'total_outflows'  => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'vault_cash'      => 'decimal:2',
        'nostro_balance'  => 'decimal:2',
        'breakdown'       => 'array',
    ];

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}
