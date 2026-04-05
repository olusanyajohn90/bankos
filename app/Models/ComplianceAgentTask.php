<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ComplianceAgentTask extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'agent_type', 'description', 'config',
        'status', 'result', 'items_processed', 'issues_found',
        'started_at', 'completed_at', 'error',
    ];

    protected $casts = [
        'config'       => 'array',
        'result'       => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
}
