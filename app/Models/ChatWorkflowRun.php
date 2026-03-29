<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatWorkflowRun extends Model
{
    use HasUuids;

    protected $table = 'chat_workflow_runs';

    protected $fillable = [
        'workflow_id',
        'triggered_by_message_id',
        'triggered_by_user_id',
        'status',
        'step_results',
        'error',
    ];

    protected $casts = [
        'step_results' => 'json',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ChatWorkflow::class, 'workflow_id');
    }

    public function triggeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function triggeredByMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'triggered_by_message_id');
    }
}
