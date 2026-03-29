<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class ChatWorkflow extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'chat_workflows';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'created_by',
        'is_active',
        'trigger',
        'steps',
        'conversation_id',
        'run_count',
        'last_run_at',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'trigger'     => 'json',
        'steps'       => 'json',
        'run_count'   => 'integer',
        'last_run_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ChatWorkflowRun::class, 'workflow_id');
    }
}
