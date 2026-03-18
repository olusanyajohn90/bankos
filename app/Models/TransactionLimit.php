<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionLimit extends Model
{
    use HasUuids;

    protected $table = 'transaction_limits';

    protected $fillable = [
        'id', 'tenant_id', 'kyc_tier', 'channel', 'transaction_type',
        'single_limit', 'daily_limit', 'monthly_limit',
    ];

    protected $casts = [
        'single_limit'   => 'float',
        'daily_limit'    => 'float',
        'monthly_limit'  => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
