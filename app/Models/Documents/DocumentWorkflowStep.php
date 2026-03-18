<?php

namespace App\Models\Documents;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentWorkflowStep extends Model
{
    use HasUuids;

    protected $table = 'document_workflow_steps';

    protected $fillable = [
        'workflow_id', 'step_order', 'name', 'action_type',
        'assignee_type', 'assignee_user_id', 'assignee_role',
        'deadline_hours', 'is_optional',
    ];

    protected $casts = ['is_optional' => 'boolean'];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflow::class, 'workflow_id');
    }

    public function assigneeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(DocumentWorkflowAction::class, 'step_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action_type) {
            'approve'     => 'Approval',
            'sign'        => 'Signature',
            'review'      => 'Review',
            'acknowledge' => 'Acknowledgement',
            default       => ucfirst($this->action_type),
        };
    }
}
