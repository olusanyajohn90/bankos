<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiTarget extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'kpi_id', 'target_type', 'target_ref_id', 'target_ref_type',
        'department', 'period_type', 'period_value', 'target_value',
        'alert_threshold_pct', 'set_by', 'notes',
    ];

    protected $casts = [
        'target_value'        => 'float',
        'alert_threshold_pct' => 'integer',
    ];

    public function kpiDefinition(): BelongsTo { return $this->belongsTo(KpiDefinition::class, 'kpi_id'); }
    public function setBy(): BelongsTo         { return $this->belongsTo(User::class, 'set_by'); }

    public function matchingActual(): ?KpiActual
    {
        return KpiActual::where('tenant_id', $this->tenant_id)
            ->where('kpi_id', $this->kpi_id)
            ->where('subject_type', $this->target_type)
            ->where('subject_ref_id', $this->target_ref_id)
            ->where('period_type', $this->period_type)
            ->where('period_value', $this->period_value)
            ->first();
    }

    public function getAchievementPctAttribute(): ?float
    {
        $actual = $this->matchingActual();
        if (!$actual || $this->target_value == 0) return null;

        $kpi = $this->kpiDefinition;
        if ($kpi && $kpi->direction === 'lower_better') {
            // Lower is better: PAR30 target 5%, actual 3% → 167% (exceeds target)
            return round(($this->target_value / $actual->value) * 100, 2);
        }

        return round(($actual->value / $this->target_value) * 100, 2);
    }

    public function getSeverityAttribute(): string
    {
        $pct = $this->achievement_pct;
        if ($pct === null) return 'gray';
        if ($pct >= 90) return 'green';
        if ($pct >= $this->alert_threshold_pct) return 'yellow';
        return 'red';
    }

    public function scopeForPeriod($q, string $periodType, string $periodValue)
    {
        return $q->where('period_type', $periodType)->where('period_value', $periodValue);
    }
}
