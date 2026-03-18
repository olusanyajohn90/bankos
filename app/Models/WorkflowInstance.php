<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'process_name',
        'subject_type',
        'subject_id',
        'status',
        'assigned_role',
        'step',
        'total_steps',
        'actioned_by',
        'notes',
        'due_at',
        'started_at',
        'ended_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'due_at'     => 'datetime',
        'metadata'   => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkflowComment::class)->orderBy('created_at');
    }

    // ── SLA Helpers ────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_at && $this->due_at->isPast();
    }

    /**
     * Returns: 'overdue' | 'critical' (<= 4h) | 'at_risk' (<= 12h) | 'on_track' | 'n/a'
     */
    public function slaStatus(): string
    {
        if ($this->status !== 'pending' || !$this->due_at) {
            return 'n/a';
        }

        $hoursLeft = now()->diffInHours($this->due_at, false);

        if ($hoursLeft < 0)   return 'overdue';
        if ($hoursLeft <= 4)  return 'critical';
        if ($hoursLeft <= 12) return 'at_risk';

        return 'on_track';
    }

    public function slaHoursRemaining(): ?int
    {
        if (!$this->due_at) return null;
        return (int) now()->diffInHours($this->due_at, false);
    }

    public function slaLabel(): string
    {
        $hours = $this->slaHoursRemaining();

        if ($hours === null) return '—';
        if ($hours < 0)      return abs($hours) . 'h overdue';
        if ($hours < 1)      return '< 1h left';
        if ($hours < 24)     return $hours . 'h left';

        return ceil($hours / 24) . 'd left';
    }

    public function currentStepLabel(): string
    {
        $steps = config("workflows.processes.{$this->process_name}.steps", []);
        return $steps[$this->step - 1]['label'] ?? "Step {$this->step} of {$this->total_steps}";
    }

    public function slaBadgeClasses(): string
    {
        return match ($this->slaStatus()) {
            'overdue'  => 'bg-red-100 text-red-700 border-red-200',
            'critical' => 'bg-orange-100 text-orange-700 border-orange-200',
            'at_risk'  => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'on_track' => 'bg-green-100 text-green-700 border-green-200',
            default    => 'bg-gray-100 text-gray-500 border-gray-200',
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-yellow-100 text-yellow-800',
            'approved'  => 'bg-green-100 text-green-800',
            'rejected'  => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-600',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    public function subjectDescription(): string
    {
        $subject = $this->subject;
        if (!$subject) return 'N/A';

        return match ($this->subject_type) {
            Loan::class            => ($subject->loan_number ?? '—') . ' — ₦' . number_format((float) $subject->principal_amount, 0),
            Customer::class        => ($subject->first_name ?? '') . ' ' . ($subject->last_name ?? '') . ' (' . ($subject->customer_number ?? '') . ')',
            LoanTopup::class       => 'Top-up ₦' . number_format((float) $subject->topup_amount, 0) . ' on ' . ($subject->loan?->loan_number ?? '—'),
            LoanRestructure::class => 'Restructure: ' . ($subject->loan?->loan_number ?? '—'),
            default                => class_basename($this->subject_type) . ' #' . $this->subject_id,
        };
    }
}
