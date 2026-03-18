<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledJobRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'job_name', 'status', 'started_at', 'ended_at',
        'duration_ms', 'records', 'errors', 'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];
}
