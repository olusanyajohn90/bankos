<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantUsage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tenant_usage';

    protected $fillable = [
        'tenant_id',
        'period',
        'customer_count',
        'staff_count',
        'branch_count',
        'transaction_count',
        'api_call_count',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
