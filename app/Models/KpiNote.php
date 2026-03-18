<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'author_id', 'subject_type', 'subject_id',
        'kpi_id', 'period_value', 'body', 'is_alert', 'is_private',
    ];

    protected $casts = [
        'is_alert'   => 'boolean',
        'is_private' => 'boolean',
    ];

    public function author(): BelongsTo        { return $this->belongsTo(User::class, 'author_id'); }
    public function kpiDefinition(): BelongsTo { return $this->belongsTo(KpiDefinition::class, 'kpi_id'); }

    public function scopePublic($q)  { return $q->where('is_private', false); }
    public function scopeAlerts($q)  { return $q->where('is_alert', true); }
}
