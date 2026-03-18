<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_id', 'step_number', 'step_name', 'assigned_to', 'assigned_role',
        'status', 'actioned_by', 'actioned_at', 'notes', 'due_at',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'actioned_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    public function request(): BelongsTo    { return $this->belongsTo(ApprovalRequest::class, 'request_id'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function actionedBy(): BelongsTo { return $this->belongsTo(User::class, 'actioned_by'); }
}
