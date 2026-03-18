<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class TrainingProgram extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'title', 'category', 'provider', 'duration_hours', 'is_mandatory', 'description', 'status'];

    protected $casts = [
        'duration_hours' => 'float',
        'is_mandatory' => 'boolean',
    ];

    public function attendances(): HasMany { return $this->hasMany(TrainingAttendance::class, 'program_id'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}
