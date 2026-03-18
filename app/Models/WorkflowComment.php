<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowComment extends Model
{
    use HasUuids;

    protected $fillable = [
        'workflow_instance_id',
        'user_id',
        'comment',
        'action',
    ];

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'escalated'  => 'Escalated',
            'reassigned' => 'Reassigned',
            default      => 'Comment',
        };
    }

    public function actionColor(): string
    {
        return match ($this->action) {
            'approved'   => 'green',
            'rejected'   => 'red',
            'escalated'  => 'orange',
            'reassigned' => 'blue',
            default      => 'gray',
        };
    }
}
