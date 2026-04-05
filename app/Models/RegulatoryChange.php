<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RegulatoryChange extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'regulator', 'title', 'summary', 'full_text',
        'reference_number', 'effective_date', 'published_date',
        'impact_level', 'affected_areas', 'status',
        'implementation_plan', 'affected_controls', 'assigned_to',
    ];

    protected $casts = [
        'effective_date'    => 'date',
        'published_date'    => 'date',
        'affected_areas'    => 'array',
        'affected_controls' => 'array',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
