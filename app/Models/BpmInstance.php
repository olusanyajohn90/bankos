<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpmInstance extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'process_id', 'tenant_id', 'subject_type', 'subject_id',
        'current_step', 'status', 'step_history', 'initiated_by', 'completed_at',
    ];

    protected $casts = [
        'step_history' => 'array',
        'completed_at' => 'datetime',
    ];

    public function process()
    {
        return $this->belongsTo(BpmProcess::class, 'process_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
