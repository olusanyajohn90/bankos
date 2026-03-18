<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiRecommendation extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'kpi_definition_id', 'subject_type', 'subject_ref_id',
        'period_value', 'period_type', 'achievement_pct', 'severity',
        'recommendation_type', 'title', 'body', 'action_steps',
        'is_system_generated', 'generated_by', 'is_acknowledged', 'acknowledged_at',
    ];

    protected $casts = [
        'action_steps' => 'array',
        'is_system_generated' => 'boolean',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'achievement_pct' => 'float',
    ];

    public function kpiDefinition(): BelongsTo { return $this->belongsTo(KpiDefinition::class, 'kpi_definition_id'); }
    public function generatedBy(): BelongsTo   { return $this->belongsTo(User::class, 'generated_by'); }
}
