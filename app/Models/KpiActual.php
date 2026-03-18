<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiActual extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'kpi_id', 'subject_type', 'subject_ref_id', 'subject_ref_type',
        'department', 'period_type', 'period_value', 'value',
        'source', 'entered_by', 'computed_at', 'computation_notes',
    ];

    protected $casts = [
        'value'       => 'float',
        'computed_at' => 'datetime',
    ];

    public function kpiDefinition(): BelongsTo { return $this->belongsTo(KpiDefinition::class, 'kpi_id'); }
    public function enteredBy(): BelongsTo     { return $this->belongsTo(User::class, 'entered_by'); }

    public function scopeForPeriod($q, string $periodType, string $periodValue)
    {
        return $q->where('period_type', $periodType)->where('period_value', $periodValue);
    }

    public function scopeForSubject($q, string $type, ?string $refId)
    {
        return $q->where('subject_type', $type)->where('subject_ref_id', $refId);
    }
}
