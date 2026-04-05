<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PerpetualKycEvent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'event_type', 'description',
        'old_data', 'new_data', 'action_required', 'status',
        'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'old_data'    => 'array',
        'new_data'    => 'array',
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
