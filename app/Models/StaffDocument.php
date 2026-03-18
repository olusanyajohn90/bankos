<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDocument extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'staff_profile_id', 'document_type', 'document_number', 'file_url', 'is_verified', 'verified_by', 'verified_at'];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function verifiedByUser(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
}
