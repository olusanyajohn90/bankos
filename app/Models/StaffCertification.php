<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCertification extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'name', 'issuing_body', 'cert_number', 'issue_date', 'expiry_date', 'is_verified', 'verified_by', 'verified_at'];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function verifiedByUser(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function isExpired(): bool { return $this->expiry_date && $this->expiry_date->isPast(); }
}
