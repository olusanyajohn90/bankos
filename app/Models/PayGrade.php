<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class PayGrade extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'code', 'name', 'level', 'grade',
        'basic_min', 'basic_max',
        'annual_increment_pct', 'leave_allowance_pct',
        'typical_title', 'is_active',
    ];

    protected $casts = [
        'basic_min'            => 'float',
        'basic_max'            => 'float',
        'annual_increment_pct' => 'float',
        'leave_allowance_pct'  => 'float',
        'is_active'            => 'boolean',
        'level'                => 'integer',
        'grade'                => 'integer',
    ];

    /** Human-readable label, e.g. "Level 7 Grade 2" */
    public function getLabelAttribute(): string
    {
        return "Level {$this->level} Grade {$this->grade}";
    }

    /** Annual leave allowance amount based on a given basic salary */
    public function leaveAllowanceAmount(float $basicSalary): float
    {
        return round($basicSalary * ($this->leave_allowance_pct / 100), 2);
    }

    public function staffPayConfigs(): HasMany
    {
        return $this->hasMany(StaffPayConfig::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /** Order by level then grade */
    public function scopeOrdered($q)
    {
        return $q->orderBy('level')->orderBy('grade');
    }
}
