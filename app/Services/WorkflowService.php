<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanRestructure;
use App\Models\LoanTopup;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\WorkflowComment;
use App\Models\WorkflowInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    /**
     * Create a workflow instance for a subject model.
     *
     * Uses config/workflows.php to derive: role, SLA deadline, and total_steps
     * based on the optional 'amount' option (delegated authority routing).
     *
     * @param string $processName  e.g. 'Loan Approval'
     * @param Model  $subject      The model being reviewed (Loan, Customer, etc.)
     * @param array  $options      amount, metadata, assigned_role (fallback)
     */
    public function create(string $processName, Model $subject, array $options = []): WorkflowInstance
    {
        $config     = config("workflows.processes.{$processName}", []);
        $steps      = $config['steps'] ?? [];
        $amount     = $options['amount'] ?? null;
        $totalSteps = count($steps);

        // ── Delegated Authority: determine how many steps are needed ──────
        if ($amount !== null && $totalSteps > 1) {
            $totalSteps = $totalSteps; // Default: full chain
            foreach ($steps as $i => $stepConfig) {
                if (isset($stepConfig['max_amount']) && (float) $amount <= (float) $stepConfig['max_amount']) {
                    $totalSteps = $i + 1; // This step is sufficient
                    break;
                }
            }
        }

        $totalSteps   = max(1, $totalSteps);
        $firstStep    = $steps[0] ?? [];
        $slaHours     = $firstStep['sla_hours'] ?? 48;
        $assignedRole = $firstStep['role'] ?? ($options['assigned_role'] ?? 'tenant_admin');

        return WorkflowInstance::create([
            'tenant_id'     => $subject->tenant_id,
            'process_name'  => $processName,
            'subject_type'  => get_class($subject),
            'subject_id'    => $subject->id,
            'status'        => 'pending',
            'assigned_role' => $assignedRole,
            'step'          => 1,
            'total_steps'   => $totalSteps,
            'due_at'        => now()->addHours($slaHours),
            'started_at'    => now(),
            'metadata'      => $options['metadata'] ?? null,
        ]);
    }

    /**
     * Advance a workflow: approve (advance step or complete) or reject.
     *
     * Records a comment, updates workflow state.
     * Returns true when the workflow is fully resolved (approved or rejected).
     * Returns false when it advanced to the next step (still pending).
     */
    public function advance(WorkflowInstance $instance, string $action, ?string $notes, User $actor): bool
    {
        if ($instance->status !== 'pending') {
            return false;
        }

        // Always record the action as a comment
        $this->recordComment($instance, $notes ?? '', $actor, $action);

        if ($action === 'reject') {
            $instance->update([
                'status'      => 'rejected',
                'actioned_by' => $actor->id,
                'notes'       => $notes,
                'ended_at'    => now(),
            ]);
            return true;
        }

        // Approve path ─────────────────────────────────────────────────────
        if ($instance->step < $instance->total_steps) {
            // Not the final step — advance to next
            $nextStep       = $instance->step + 1;
            $config         = config("workflows.processes.{$instance->process_name}.steps", []);
            $nextStepConfig = $config[$nextStep - 1] ?? [];
            $slaHours       = $nextStepConfig['sla_hours'] ?? 48;
            $nextRole       = $nextStepConfig['role'] ?? $instance->assigned_role;

            $instance->update([
                'step'          => $nextStep,
                'assigned_role' => $nextRole,
                'due_at'        => now()->addHours($slaHours),
                'started_at'    => now(),
            ]);

            return false; // Still pending at next step
        }

        // Final step — fully approved
        $instance->update([
            'status'      => 'approved',
            'actioned_by' => $actor->id,
            'notes'       => $notes,
            'ended_at'    => now(),
        ]);

        return true;
    }

    /**
     * Add a comment without changing the workflow state.
     */
    public function comment(WorkflowInstance $instance, string $comment, User $actor): WorkflowComment
    {
        return $this->recordComment($instance, $comment, $actor, 'comment');
    }

    /**
     * Cancel a workflow (e.g. when the underlying subject is deleted).
     */
    public function cancel(WorkflowInstance $instance, ?string $reason = null, ?User $actor = null): void
    {
        if ($actor) {
            $this->recordComment($instance, $reason ?? 'Workflow cancelled.', $actor, 'comment');
        }

        $instance->update([
            'status'   => 'cancelled',
            'ended_at' => now(),
        ]);
    }

    /**
     * Escalate an overdue workflow to the next role/step.
     * Called by the WorkflowCheckSla artisan command.
     */
    public function escalate(WorkflowInstance $instance, string $reason = 'SLA breached — auto-escalated'): void
    {
        if ($instance->status !== 'pending') return;

        $config = config("workflows.processes.{$instance->process_name}.steps", []);

        // Try to escalate to the next step if one exists
        $nextStepIndex = $instance->step; // 0-indexed = current step
        $nextConfig    = $config[$nextStepIndex] ?? $config[count($config) - 1] ?? [];
        $nextRole      = $nextConfig['role'] ?? $instance->assigned_role;
        $slaHours      = $nextConfig['sla_hours'] ?? 48;
        $newStep       = min($instance->step + 1, $instance->total_steps);

        // Record system comment
        $systemUser = User::where('tenant_id', $instance->tenant_id)->first();
        if ($systemUser) {
            WorkflowComment::create([
                'workflow_instance_id' => $instance->id,
                'user_id'              => $systemUser->id,
                'comment'              => $reason,
                'action'               => 'escalated',
            ]);
        }

        $instance->update([
            'step'          => $newStep,
            'assigned_role' => $nextRole,
            'due_at'        => now()->addHours($slaHours),
            'started_at'    => now(),
        ]);

        Log::info("Workflow escalated: [{$instance->id}] {$instance->process_name} → step {$newStep}, role {$nextRole}");
    }

    /**
     * Resolve the pending workflow for a given subject (used by existing controllers
     * when approval/rejection happens outside WorkflowController).
     */
    public function resolveForSubject(Model $subject, string $action, ?string $notes = null, ?User $actor = null): void
    {
        $instance = WorkflowInstance::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$instance) return;

        $actor ??= auth()->user();

        if ($actor) {
            $this->advance($instance, $action, $notes, $actor);
        } else {
            $instance->update([
                'status'   => $action === 'reject' ? 'rejected' : 'approved',
                'ended_at' => now(),
            ]);
        }
    }

    // ── Internal ───────────────────────────────────────────────────────────

    private function recordComment(WorkflowInstance $instance, string $comment, User $actor, string $action): WorkflowComment
    {
        return WorkflowComment::create([
            'workflow_instance_id' => $instance->id,
            'user_id'              => $actor->id,
            'comment'              => $comment ?: '(no notes provided)',
            'action'               => $action,
        ]);
    }
}
