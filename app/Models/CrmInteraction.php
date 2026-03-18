<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmInteraction extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'subject_type', 'subject_id', 'lead_id', 'account_id',
        'interaction_type', 'direction', 'subject', 'summary', 'outcome',
        'next_action', 'next_action_date', 'duration_mins',
        'interacted_at', 'created_by',
    ];

    protected $casts = [
        'interacted_at'    => 'datetime',
        'next_action_date' => 'date',
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lead(): BelongsTo      { return $this->belongsTo(CrmLead::class, 'lead_id'); }
}
