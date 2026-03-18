<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalRequestController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $query = ApprovalRequest::where('tenant_id', $tenantId)
            ->with(['matrix', 'initiatedBy', 'steps', 'finalActionedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        // If not admin, show only own + pending for this user
        if (!$user->hasAnyRole(['super_admin', 'tenant_admin', 'hr_manager'])) {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                  ->orWhereHas('steps', fn($s) => $s->where('assigned_to', $user->id)->where('status', 'pending'));
            });
        }

        $requests = $query->paginate(25)->withQueryString();
        $pending  = $this->approvalService->pendingForUser($user);

        return view('hr.approvals.requests', compact('requests', 'pending'));
    }

    public function show(ApprovalRequest $approvalRequest)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($approvalRequest->tenant_id === $tenantId, 403);

        $approvalRequest->load(['matrix.steps', 'steps.actionedBy', 'steps.assignedTo', 'initiatedBy', 'finalActionedBy']);

        return view('hr.approvals.show', compact('approvalRequest'));
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $user = auth()->user();
        abort_unless($approvalRequest->tenant_id === $user->tenant_id, 403);

        $request->validate(['notes' => 'nullable|string|max:500']);

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'This request is no longer pending.');
        }

        $currentStep = $approvalRequest->currentStepRecord();
        if ($currentStep && $currentStep->assigned_to && $currentStep->assigned_to !== $user->id) {
            if (!$user->hasAnyRole(['super_admin', 'tenant_admin'])) {
                return back()->with('error', 'You are not the assigned approver for this step.');
            }
        }

        $this->approvalService->approve($approvalRequest, $user, $request->notes ?? '');

        return back()->with('success', 'Request approved successfully.');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $user = auth()->user();
        abort_unless($approvalRequest->tenant_id === $user->tenant_id, 403);

        $request->validate(['notes' => 'required|string|max:500']);

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'This request is no longer pending.');
        }

        $this->approvalService->reject($approvalRequest, $user, $request->notes);

        return back()->with('success', 'Request rejected.');
    }

    public function cancel(ApprovalRequest $approvalRequest)
    {
        $user = auth()->user();
        abort_unless($approvalRequest->tenant_id === $user->tenant_id, 403);
        abort_unless($approvalRequest->initiated_by === $user->id || $user->hasRole('super_admin'), 403);

        if (!$approvalRequest->isPending()) {
            return back()->with('error', 'Cannot cancel a request that is not pending.');
        }

        $approvalRequest->update(['status' => 'cancelled']);
        return back()->with('success', 'Request cancelled.');
    }
}
