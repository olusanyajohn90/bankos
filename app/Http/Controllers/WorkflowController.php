<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanRestructure;
use App\Models\LoanTopup;
use App\Models\WorkflowInstance;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflowService) {}

    // ── Index: My Tasks + stats + All Instances ────────────────────────────

    public function index()
    {
        $user      = auth()->user();
        $userRoles = $user->getRoleNames()->toArray();

        // My Tasks — pending tasks for user's roles, overdue first
        $myTasks = WorkflowInstance::where('status', 'pending')
            ->whereIn('assigned_role', $userRoles)
            ->with(['subject'])
            ->orderByRaw("CASE WHEN due_at IS NOT NULL AND due_at < NOW() THEN 0 ELSE 1 END")
            ->orderBy('due_at')
            ->get();

        // Stats cards
        $pendingCount   = WorkflowInstance::where('status', 'pending')->count();
        $overdueCount   = WorkflowInstance::where('status', 'pending')
            ->whereNotNull('due_at')->where('due_at', '<', now())->count();
        $completedToday = WorkflowInstance::whereIn('status', ['approved', 'rejected'])
            ->whereDate('ended_at', today())->count();
        $myPendingCount = $myTasks->count();

        // All Instances — filterable, paginated
        $query = WorkflowInstance::with(['subject', 'comments.user']);

        if (request('process')) {
            $query->where('process_name', request('process'));
        }
        if (request('status') && request('status') !== 'all') {
            $query->where('status', request('status'));
        }

        $allInstances = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('started_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $processes = WorkflowInstance::distinct()->pluck('process_name')->sort()->values();

        return view('workflows.index', compact(
            'myTasks', 'allInstances', 'processes',
            'pendingCount', 'overdueCount', 'completedToday', 'myPendingCount'
        ));
    }

    // ── Show: detail view with subject info + comment timeline ────────────

    public function show(WorkflowInstance $workflow)
    {
        $workflow->load(['subject', 'comments.user']);

        match ($workflow->subject_type) {
            Loan::class            => $workflow->subject?->load(['customer', 'loanProduct']),
            Customer::class        => $workflow->subject?->load(['kycDocuments']),
            LoanTopup::class       => $workflow->subject?->load(['loan.customer', 'requestedBy']),
            LoanRestructure::class => $workflow->subject?->load(['loan.customer', 'requestedBy']),
            default                => null,
        };

        $stepDefs = config("workflows.processes.{$workflow->process_name}.steps", []);

        return view('workflows.show', compact('workflow', 'stepDefs'));
    }

    // ── Action: approve, reject, or comment ───────────────────────────────

    public function action(Request $request, WorkflowInstance $workflow)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,comment',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $action = $validated['action'];
        $notes  = $validated['notes'] ?? null;
        $actor  = auth()->user();

        if (!$actor->hasRole($workflow->assigned_role) && !$actor->hasRole('tenant_admin')) {
            abort(403, 'You do not have the required role to action this workflow.');
        }

        if ($workflow->status !== 'pending') {
            return back()->with('error', 'This workflow has already been resolved.');
        }

        if ($action === 'comment') {
            $this->workflowService->comment($workflow, $notes ?? '(no message)', $actor);
            return back()->with('success', 'Comment added.');
        }

        // For complex approvals (topup/restructure), redirect to dedicated page
        if ($action === 'approve' && in_array($workflow->subject_type, [LoanTopup::class, LoanRestructure::class])) {
            return redirect()->route('workflows.show', $workflow)
                ->with('info', 'This workflow requires the dedicated approval form. Use the button below.');
        }

        // Dispatch business logic
        $this->dispatchToBusinessObject($workflow, $action, $notes, $actor);

        // Advance workflow (multi-step or final)
        $resolved = $this->workflowService->advance($workflow, $action, $notes, $actor);

        if (!$resolved && $action === 'approve') {
            $workflow->refresh();
            return redirect()->route('workflows.show', $workflow)
                ->with('success', "Step approved — now at: {$workflow->currentStepLabel()}. Awaiting next approver.");
        }

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow ' . ($action === 'approve' ? 'approved' : 'rejected') . ' successfully.');
    }

    // ── Bulk Action ────────────────────────────────────────────────────────

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'uuid',
            'notes'  => 'nullable|string|max:500',
        ]);

        $actor     = auth()->user();
        $userRoles = $actor->getRoleNames()->toArray();
        $count     = 0;

        $instances = WorkflowInstance::whereIn('id', $validated['ids'])
            ->where('status', 'pending')
            ->whereIn('assigned_role', $userRoles)
            ->get();

        foreach ($instances as $instance) {
            // Skip complex approvals in bulk (only allow rejections and simple approvals)
            if ($validated['action'] === 'approve' &&
                in_array($instance->subject_type, [LoanTopup::class, LoanRestructure::class])) {
                continue;
            }
            $this->dispatchToBusinessObject($instance, $validated['action'], $validated['notes'], $actor);
            $this->workflowService->advance($instance, $validated['action'], $validated['notes'], $actor);
            $count++;
        }

        return redirect()->route('workflows.index')
            ->with('success', "{$count} workflow(s) {$validated['action']}d successfully.");
    }

    // ── Business Object Dispatch ───────────────────────────────────────────

    private function dispatchToBusinessObject(WorkflowInstance $workflow, string $action, ?string $notes, $actor): void
    {
        $subject = $workflow->subject;
        if (!$subject) return;

        match ($workflow->subject_type) {
            Loan::class            => $this->handleLoan($subject, $action, $notes, $actor),
            Customer::class        => $this->handleKyc($subject, $action, $notes, $actor),
            LoanTopup::class       => $this->handleTopupReject($subject, $notes, $actor),
            LoanRestructure::class => $this->handleRestructureReject($subject, $notes, $actor),
            default                => null,
        };
    }

    private function handleLoan(Loan $loan, string $action, ?string $notes, $actor): void
    {
        if ($loan->status !== 'pending') return;

        if ($action === 'approve') {
            $loan->update(['status' => 'approved', 'approved_by' => $actor->id, 'approved_at' => now()]);
            $loan->load('customer', 'loanProduct');
            if ($loan->customer) {
                app(NotificationService::class)->send($loan->customer, 'loan_approved', [
                    'customer_name' => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                    'amount'        => number_format((float) $loan->principal_amount, 2),
                    'loan_number'   => $loan->loan_number,
                    'product_name'  => $loan->loanProduct?->name ?? 'N/A',
                    'tenure'        => $loan->tenure_days . ' months',
                ]);
            }
        } else {
            $loan->update(['status' => 'rejected']);
            $loan->load('customer', 'loanProduct');
            if ($loan->customer) {
                app(NotificationService::class)->send($loan->customer, 'loan_rejected', [
                    'customer_name' => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                    'amount'        => number_format((float) $loan->principal_amount, 2),
                    'loan_number'   => $loan->loan_number,
                    'reason'        => $notes ?? 'Application did not meet credit requirements.',
                ]);
            }
        }
    }

    private function handleKyc(Customer $customer, string $action, ?string $notes, $actor): void
    {
        if ($action === 'approve') {
            $customer->update(['kyc_status' => 'approved', 'kyc_tier' => $customer->kyc_tier ?? 'level_1', 'status' => 'active']);
        } else {
            $customer->update(['kyc_status' => 'rejected', 'status' => 'inactive']);
        }
    }

    private function handleTopupReject(LoanTopup $topup, ?string $notes, $actor): void
    {
        if ($topup->status !== 'pending') return;
        $topup->update(['status' => 'rejected', 'officer_notes' => $notes ?? 'Rejected.', 'reviewed_by' => $actor->id, 'reviewed_at' => now()]);
        $topup->load('loan.customer');
        if ($topup->loan?->customer) {
            app(NotificationService::class)->send($topup->loan->customer, 'loan_topup_rejected', [
                'customer_name'        => $topup->loan->customer->first_name . ' ' . $topup->loan->customer->last_name,
                'topup_amount'         => number_format((float) $topup->topup_amount, 2),
                'original_loan_number' => $topup->loan->loan_number,
                'reason'               => $notes ?? 'Rejected by management.',
            ]);
        }
    }

    private function handleRestructureReject(LoanRestructure $restructure, ?string $notes, $actor): void
    {
        if ($restructure->status !== 'pending') return;
        $restructure->update(['status' => 'rejected', 'officer_notes' => $notes ?? 'Rejected.', 'reviewed_by' => $actor->id, 'reviewed_at' => now()]);
        $restructure->load('loan.customer');
        if ($restructure->loan?->customer) {
            app(NotificationService::class)->send($restructure->loan->customer, 'loan_restructure_rejected', [
                'customer_name' => $restructure->loan->customer->first_name . ' ' . $restructure->loan->customer->last_name,
                'loan_number'   => $restructure->loan->loan_number,
                'reason'        => $notes ?? 'Rejected by management.',
            ]);
        }
    }
}
