<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeeRule extends Model
{
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'transaction_type',
        'account_type',
        'fee_type',
        'amount',
        'min_fee',
        'max_fee',
        'min_transaction_amount',
        'max_transaction_amount',
        'is_active',
        'waivable',
    ];

    protected $casts = [
        'amount'                  => 'float',
        'min_fee'                 => 'float',
        'max_fee'                 => 'float',
        'min_transaction_amount'  => 'float',
        'max_transaction_amount'  => 'float',
        'is_active'               => 'boolean',
        'waivable'                => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (FeeRule $rule) {
            if (empty($rule->id)) {
                $rule->id = (string) Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
