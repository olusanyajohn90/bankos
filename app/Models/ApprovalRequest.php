<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'matrix_id', 'action_type', 'subject_type', 'subject_id',
        'reference', 'summary', 'amount', 'status', 'current_step', 'total_steps',
        'initiated_by', 'final_actioned_by', 'final_actioned_at', 'final_notes',
        'metadata', 'due_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'metadata' => 'array',
        'final_actioned_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function matrix(): BelongsTo        { return $this->belongsTo(ApprovalMatrix::class, 'matrix_id'); }
    public function initiatedBy(): BelongsTo   { return $this->belongsTo(User::class, 'initiated_by'); }
    public function finalActionedBy(): BelongsTo { return $this->belongsTo(User::class, 'final_actioned_by'); }
    public function steps(): HasMany           { return $this->hasMany(ApprovalRequestStep::class, 'request_id')->orderBy('step_number'); }
    public function currentStepRecord()        { return $this->steps()->where('step_number', $this->current_step)->first(); }

    public function isPending(): bool  { return in_array($this->status, ['pending', 'in_review']); }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
