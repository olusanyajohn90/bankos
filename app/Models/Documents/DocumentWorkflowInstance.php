<?php

namespace App\Models\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentWorkflowInstance extends Model
{
    use HasUuids;

    protected $table = 'document_workflow_instances';

    protected $fillable = [
        'document_id', 'workflow_id', 'initiated_by', 'status',
        'current_step_order', 'notes', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflow::class, 'workflow_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(DocumentWorkflowAction::class, 'instance_id');
    }

    public function currentAction()
    {
        return $this->actions()->where('status', 'pending')->orderBy('created_at')->first();
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'in_progress' => 'bg-blue-100 text-blue-700',
            'completed'   => 'bg-green-100 text-green-700',
            'rejected'    => 'bg-red-100 text-red-700',
            'cancelled'   => 'bg-gray-100 text-gray-500',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
