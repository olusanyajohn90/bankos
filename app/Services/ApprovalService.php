<?php

namespace App\Services;

use App\Models\ApprovalMatrix;
use App\Models\ApprovalMatrixStep;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\StaffProfile;
use App\Models\User;

class ApprovalService
{
    public function initiate(
        string $tenantId,
        string $actionType,
        string $subjectType,
        string $subjectId,
        string $summary,
        User $initiator,
        float $amount = 0,
        array $metadata = []
    ): ApprovalRequest {
        $matrix = ApprovalMatrix::findForAction($tenantId, $actionType, $amount);

        if (!$matrix) {
            return $this->autoApprove($tenantId, $actionType, $subjectType, $subjectId, $summary, $initiator, $amount, $metadata);
        }

        $reference = $this->generateReference($tenantId, $actionType);

        $request = ApprovalRequest::create([
            'tenant_id'    => $tenantId,
            'matrix_id'    => $matrix->id,
            'action_type'  => $actionType,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'reference'    => $reference,
            'summary'      => $summary,
            'amount'       => $amount ?: null,
            'status'       => 'pending',
            'current_step' => 1,
            'total_steps'  => $matrix->total_steps,
            'initiated_by' => $initiator->id,
            'metadata'     => $metadata,
            'due_at'       => now()->addHours($matrix->escalation_hours),
        ]);

        foreach ($matrix->steps as $matrixStep) {
            $assignedTo = $this->resolveApprover($matrixStep, $initiator, $tenantId);
            ApprovalRequestStep::create([
                'request_id'    => $request->id,
                'step_number'   => $matrixStep->step_number,
                'step_name'     => $matrixStep->step_name,
                'assigned_to'   => $assignedTo?->id,
                'assigned_role' => $matrixStep->approver_type === 'role' ? $matrixStep->approver_value : null,
                'status'        => 'pending',
                'due_at'        => now()->addHours($matrixStep->timeout_hours),
            ]);
        }

        return $request;
    }

    public function approve(ApprovalRequest $request, User $approver, string $notes = ''): ApprovalRequest
    {
        $currentStep = $request->currentStepRecord();

        if (!$currentStep || $currentStep->status !== 'pending') {
            return $request;
        }

        $currentStep->update([
            'status'      => 'approved',
            'actioned_by' => $approver->id,
            'actioned_at' => now(),
            'notes'       => $notes,
        ]);

        $nextStep = $request->steps()->where('step_number', $request->current_step + 1)->first();

        if ($nextStep) {
            $request->update(['current_step' => $request->current_step + 1, 'status' => 'in_review']);
        } else {
            $request->update([
                'status'            => 'approved',
                'final_actioned_by' => $approver->id,
                'final_actioned_at' => now(),
                'final_notes'       => $notes,
            ]);
        }

        return $request->fresh();
    }

    public function reject(ApprovalRequest $request, User $rejector, string $notes = ''): ApprovalRequest
    {
        $currentStep = $request->currentStepRecord();

        if ($currentStep) {
            $currentStep->update([
                'status'      => 'rejected',
                'actioned_by' => $rejector->id,
                'actioned_at' => now(),
                'notes'       => $notes,
            ]);
        }

        $request->update([
            'status'            => 'rejected',
            'final_actioned_by' => $rejector->id,
            'final_actioned_at' => now(),
            'final_notes'       => $notes,
        ]);

        return $request->fresh();
    }

    public function pendingForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return ApprovalRequest::where('tenant_id', $user->tenant_id)
            ->whereIn('status', ['pending', 'in_review'])
            ->whereHas('steps', function ($q) use ($user) {
                $q->where('status', 'pending')
                  ->where(function ($q2) use ($user) {
                      $q2->where('assigned_to', $user->id)->orWhereNull('assigned_to');
                  });
            })
            ->with(['steps', 'initiatedBy', 'matrix'])
            ->latest()
            ->get();
    }

    private function autoApprove(string $tenantId, string $actionType, string $subjectType, string $subjectId, string $summary, User $initiator, float $amount, array $metadata): ApprovalRequest
    {
        $matrix = ApprovalMatrix::where('tenant_id', $tenantId)->first();

        return ApprovalRequest::create([
            'tenant_id'         => $tenantId,
            'matrix_id'         => $matrix?->id,
            'action_type'       => $actionType,
            'subject_type'      => $subjectType,
            'subject_id'        => $subjectId,
            'reference'         => $this->generateReference($tenantId, $actionType),
            'summary'           => $summary,
            'amount'            => $amount ?: null,
            'status'            => 'approved',
            'current_step'      => 1,
            'total_steps'       => 1,
            'initiated_by'      => $initiator->id,
            'final_actioned_by' => $initiator->id,
            'final_actioned_at' => now(),
            'final_notes'       => 'Auto-approved — no matrix configured for this action type.',
            'metadata'          => $metadata,
        ]);
    }

    private function resolveApprover(ApprovalMatrixStep $step, User $initiator, string $tenantId): ?User
    {
        return match ($step->approver_type) {
            'user'        => User::find($step->approver_value),
            'role'        => User::where('tenant_id', $tenantId)->role($step->approver_value)->first(),
            'any_manager' => $this->findManagerOf($initiator, $tenantId),
            default       => null,
        };
    }

    private function findManagerOf(User $user, string $tenantId): ?User
    {
        $profile = StaffProfile::where('user_id', $user->id)->where('tenant_id', $tenantId)->first();
        if ($profile && $profile->manager_id) {
            return User::find($profile->manager_id);
        }
        return null;
    }

    private function generateReference(string $tenantId, string $actionType): string
    {
        $prefix = strtoupper(substr(str_replace('_', '', $actionType), 0, 4));
        $year   = now()->year;
        $seq    = ApprovalRequest::where('tenant_id', $tenantId)
                    ->where('action_type', $actionType)
                    ->whereYear('created_at', $year)
                    ->count() + 1;
        return $prefix . '-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
