<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BeneficialOwner extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'owner_name', 'nationality',
        'id_type', 'id_number', 'ownership_percentage',
        'is_pep', 'is_sanctioned', 'verification_status',
        'date_of_birth', 'address',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'is_pep'               => 'boolean',
        'is_sanctioned'        => 'boolean',
        'date_of_birth'        => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
