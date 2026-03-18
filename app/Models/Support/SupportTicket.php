<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class SupportTicket extends Model
{
    use HasUuids;

    protected $table = 'support_tickets';

    protected $fillable = [
        'tenant_id','ticket_number','subject','description','channel','priority','status',
        'category_id','team_id','assigned_to','created_by',
        'requester_type','requester_name','requester_email','requester_phone',
        'customer_id','account_number',
        'sla_policy_id','sla_response_due_at','sla_resolution_due_at',
        'first_responded_at','resolved_at','closed_at','sla_breached',
        'escalation_level','escalated_at','escalated_to',
        'satisfaction_rating','satisfaction_comment',
    ];

    protected $casts = [
        'sla_response_due_at'  => 'datetime',
        'sla_resolution_due_at'=> 'datetime',
        'first_responded_at'   => 'datetime',
        'resolved_at'          => 'datetime',
        'closed_at'            => 'datetime',
        'escalated_at'         => 'datetime',
        'sla_breached'         => 'boolean',
    ];

    public function assignedTo(): BelongsTo  { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function escalatedTo(): BelongsTo { return $this->belongsTo(User::class, 'escalated_to'); }
    public function team(): BelongsTo        { return $this->belongsTo(SupportTeam::class, 'team_id'); }
    public function category(): BelongsTo    { return $this->belongsTo(SupportCategory::class, 'category_id'); }
    public function slaPolicy(): BelongsTo   { return $this->belongsTo(SupportSlaPolicy::class, 'sla_policy_id'); }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function isOpen(): bool     { return ! in_array($this->status, ['resolved','closed','cancelled']); }
    public function isOverdue(): bool  { return $this->sla_resolution_due_at && $this->sla_resolution_due_at->isPast() && $this->isOpen(); }
    public function isResponseOverdue(): bool { return $this->sla_response_due_at && $this->sla_response_due_at->isPast() && ! $this->first_responded_at; }

    public function priorityColor(): string
    {
        return match($this->priority) {
            'critical' => 'bg-red-100 text-red-700',
            'high'     => 'bg-orange-100 text-orange-700',
            'medium'   => 'bg-amber-100 text-amber-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'open'        => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-amber-100 text-amber-700',
            'pending'     => 'bg-purple-100 text-purple-700',
            'resolved'    => 'bg-green-100 text-green-700',
            'closed'      => 'bg-gray-100 text-gray-500',
            'cancelled'   => 'bg-gray-100 text-gray-400',
            default       => 'bg-gray-100 text-gray-400',
        };
    }
}
