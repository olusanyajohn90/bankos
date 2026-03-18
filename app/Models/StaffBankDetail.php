<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffBankDetail extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'bank_name', 'bank_code', 'account_number', 'account_name', 'is_primary', 'is_verified'];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
}
