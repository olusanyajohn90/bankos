<?php

namespace App\Models\Documents;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWorkflowAction extends Model
{
    use HasUuids;

    protected $table = 'document_workflow_actions';

    protected $fillable = [
        'instance_id', 'step_id', 'assignee_id', 'actor_id',
        'status', 'notes', 'deadline_at', 'acted_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'acted_at'    => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflowInstance::class, 'instance_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflowStep::class, 'step_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->deadline_at && $this->deadline_at->isPast();
    }
}
