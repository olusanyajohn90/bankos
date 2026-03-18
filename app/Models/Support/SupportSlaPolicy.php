<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupportSlaPolicy extends Model
{
    use HasUuids;

    protected $table = 'support_sla_policies';

    protected $fillable = [
        'tenant_id','name','priority','response_minutes','resolution_minutes',
        'business_hours_only','is_default',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
        'is_default'          => 'boolean',
    ];

    public function getResponseLabelAttribute(): string
    {
        $h = intdiv($this->response_minutes, 60);
        $m = $this->response_minutes % 60;
        return $h > 0 ? "{$h}h" . ($m > 0 ? " {$m}m" : '') : "{$m}m";
    }

    public function getResolutionLabelAttribute(): string
    {
        $h = intdiv($this->resolution_minutes, 60);
        $m = $this->resolution_minutes % 60;
        return $h > 0 ? "{$h}h" . ($m > 0 ? " {$m}m" : '') : "{$m}m";
    }
}
