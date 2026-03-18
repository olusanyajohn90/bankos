<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'meeting_id', 'customer_id', 'present', 'amount_paid', 'notes',
    ];

    protected $casts = [
        'present' => 'boolean',
        'amount_paid' => 'decimal:2',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
