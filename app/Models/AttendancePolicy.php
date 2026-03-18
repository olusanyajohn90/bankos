<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendancePolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'name', 'work_start_time', 'work_end_time',
        'grace_minutes', 'daily_work_hours', 'half_day_hours',
        'allow_overtime', 'is_default', 'working_days',
    ];

    protected $casts = [
        'allow_overtime' => 'boolean',
        'is_default'     => 'boolean',
        'working_days'   => 'array',
        'daily_work_hours' => 'float',
    ];

    public function records(): HasMany { return $this->hasMany(AttendanceRecord::class, 'policy_id'); }
}
