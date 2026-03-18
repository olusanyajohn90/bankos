<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmLead extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'stage_id', 'title', 'contact_name', 'contact_phone',
        'contact_email', 'company', 'source', 'product_interest',
        'estimated_value', 'probability_pct', 'status', 'assigned_to',
        'converted_account_id', 'expected_close_date', 'closed_date',
        'lost_reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'estimated_value'     => 'decimal:2',
        'expected_close_date' => 'date',
        'closed_date'         => 'date',
    ];

    public function stage(): BelongsTo     { return $this->belongsTo(CrmPipelineStage::class, 'stage_id'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function interactions(): HasMany { return $this->hasMany(CrmInteraction::class, 'lead_id'); }
    public function followUps(): HasMany    { return $this->hasMany(CrmFollowUp::class, 'subject_id')->where('subject_type', 'lead'); }
}
