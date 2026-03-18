<?php

namespace App\Console\Commands;

use App\Models\WorkflowInstance;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WorkflowCheckSla extends Command
{
    protected $signature   = 'workflow:check-sla {--dry-run : Show what would happen without making changes}';
    protected $description = 'Check workflow SLA deadlines, escalate overdue tasks, and notify approvers';

    public function handle(WorkflowService $workflowService, NotificationService $notificationService): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '[DRY RUN] Checking workflow SLAs...' : 'Checking workflow SLAs...');

        // ── 1. Find overdue workflows ──────────────────────────────────────
        $overdue = WorkflowInstance::where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->with(['subject', 'tenant.users'])
            ->get();

        $this->info("Found {$overdue->count()} overdue workflow(s).");

        foreach ($overdue as $instance) {
            $hoursOverdue = abs($instance->slaHoursRemaining());
            $this->line("  → [{$instance->process_name}] {$instance->subjectDescription()} — {$hoursOverdue}h overdue");

            if (!$dryRun) {
                // Auto-escalate if there are more steps available
                $config         = config("workflows.processes.{$instance->process_name}.steps", []);
                $hasNextStep    = $instance->step < count($config);

                if ($hasNextStep && $instance->step < $instance->total_steps) {
                    $workflowService->escalate($instance, "SLA breached by {$hoursOverdue}h — auto-escalated to next level.");
                    $this->warn("    Escalated to step " . ($instance->step + 1));
                } else {
                    // No next step — just log and notify
                    Log::warning("Workflow SLA breached (no escalation path): [{$instance->id}] {$instance->process_name}");
                }

                // Notify approvers in the tenant
                $this->notifyApprovers($instance, $notificationService, $hoursOverdue);
            }
        }

        // ── 2. Find at-risk workflows (due within 4 hours) — warn only ────
        $atRisk = WorkflowInstance::where('status', 'pending')
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [now(), now()->addHours(4)])
            ->get();

        if ($atRisk->count() > 0) {
            $this->warn("Found {$atRisk->count()} at-risk workflow(s) due within 4 hours.");

            foreach ($atRisk as $instance) {
                $hoursLeft = $instance->slaHoursRemaining();
                $this->line("  → [{$instance->process_name}] {$instance->subjectDescription()} — {$hoursLeft}h remaining");

                if (!$dryRun) {
                    $this->notifyApprovers($instance, $notificationService, null, $hoursLeft);
                }
            }
        }

        // ── 3. Summary ────────────────────────────────────────────────────
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Overdue (actioned)',   $overdue->count()],
                ['At Risk (warned)',     $atRisk->count()],
                ['Dry run',              $dryRun ? 'YES' : 'no'],
            ]
        );

        Log::info("workflow:check-sla completed — overdue: {$overdue->count()}, at_risk: {$atRisk->count()}");

        return self::SUCCESS;
    }

    /**
     * Send SLA breach/warning notification to users with the assigned role in the tenant.
     */
    private function notifyApprovers(
        WorkflowInstance   $instance,
        NotificationService $notificationService,
        ?int $hoursOverdue = null,
        ?int $hoursLeft    = null
    ): void {
        // Find users in the tenant with the assigned role
        $approvers = \App\Models\User::where('tenant_id', $instance->tenant_id)
            ->role($instance->assigned_role)
            ->whereNotNull('email')
            ->get();

        if ($approvers->isEmpty()) {
            // Fallback: notify all tenant admins
            $approvers = \App\Models\User::where('tenant_id', $instance->tenant_id)
                ->role('tenant_admin')
                ->whereNotNull('email')
                ->get();
        }

        foreach ($approvers as $user) {
            $subject = $hoursOverdue !== null
                ? "⚠️ SLA Breach: {$instance->process_name} is {$hoursOverdue}h overdue"
                : "⏰ Action Required: {$instance->process_name} due in {$hoursLeft}h";

            $body = $hoursOverdue !== null
                ? "Dear {$user->name},\n\nA workflow requires your immediate attention.\n\nProcess: {$instance->process_name}\nSubject: {$instance->subjectDescription()}\nSLA: {$hoursOverdue} hours overdue\nStep: {$instance->currentStepLabel()}\n\nPlease log in to bankOS and take action as soon as possible to avoid further escalation.\n\nThis is an automated SLA alert from bankOS."
                : "Dear {$user->name},\n\nA workflow in your queue is due soon.\n\nProcess: {$instance->process_name}\nSubject: {$instance->subjectDescription()}\nSLA: {$hoursLeft} hours remaining\nStep: {$instance->currentStepLabel()}\n\nPlease review and action this task before the deadline.\n\nThis is an automated SLA reminder from bankOS.";

            try {
                \Illuminate\Support\Facades\Mail::html(
                    view('emails.notification', compact('subject', 'body'))->render(),
                    fn ($msg) => $msg->to($user->email)->subject($subject)
                );
            } catch (\Throwable $e) {
                Log::warning("SLA notification failed for {$user->email}: " . $e->getMessage());
            }
        }
    }
}
