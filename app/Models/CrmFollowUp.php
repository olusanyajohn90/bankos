<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmFollowUp extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'subject_type', 'subject_id', 'title',
        'notes', 'due_at', 'status', 'assigned_to', 'created_by',
    ];

    protected $casts = ['due_at' => 'datetime'];

    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
}
