<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id', 'device_id', 'device_name', 'platform',
        'push_token', 'app_version', 'is_active', 'last_active_at',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
