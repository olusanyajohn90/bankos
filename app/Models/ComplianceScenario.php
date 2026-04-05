<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ComplianceScenario extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'category',
        'test_config', 'expected_outcome', 'actual_outcome',
        'result', 'last_run_at', 'created_by',
    ];

    protected $casts = [
        'test_config'      => 'array',
        'expected_outcome' => 'array',
        'actual_outcome'   => 'array',
        'last_run_at'      => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
