<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiAlert extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'kpi_target_id', 'kpi_actual_id', 'recipient_id',
        'severity', 'achievement_pct', 'target_value', 'actual_value',
        'period_value', 'status', 'read_at', 'dismissed_at', 'note_id',
    ];

    protected $casts = [
        'achievement_pct' => 'float',
        'target_value'    => 'float',
        'actual_value'    => 'float',
        'read_at'         => 'datetime',
        'dismissed_at'    => 'datetime',
    ];

    public function kpiTarget(): BelongsTo  { return $this->belongsTo(KpiTarget::class); }
    public function kpiActual(): BelongsTo  { return $this->belongsTo(KpiActual::class); }
    public function recipient(): BelongsTo  { return $this->belongsTo(User::class, 'recipient_id'); }
    public function note(): BelongsTo       { return $this->belongsTo(KpiNote::class); }

    public function scopeUnread($q)               { return $q->where('status', 'unread'); }
    public function scopeRed($q)                  { return $q->where('severity', 'red'); }
    public function scopeForUser($q, string $uid)  { return $q->where('recipient_id', $uid); }

    public function markRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }

    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed', 'dismissed_at' => now()]);
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'green'  => 'emerald',
            'yellow' => 'yellow',
            'red'    => 'red',
            default  => 'gray',
        };
    }
}
