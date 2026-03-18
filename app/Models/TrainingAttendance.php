<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAttendance extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'program_id', 'staff_profile_id', 'enrolled_at', 'status', 'score', 'certificate_issued', 'completed_at'];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'float',
        'certificate_issued' => 'boolean',
    ];

    public function program(): BelongsTo { return $this->belongsTo(TrainingProgram::class, 'program_id'); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
}
