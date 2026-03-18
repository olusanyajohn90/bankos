<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalMatrixStep;
use App\Models\ApprovalRequest;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalMatrixController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $matrices = ApprovalMatrix::where('tenant_id', $tenantId)
            ->with(['steps', 'createdBy'])
            ->withCount('requests')
            ->orderBy('action_type')
            ->get()
            ->groupBy('action_type');

        $actionTypes = $this->actionTypes();

        return view('hr.approvals.matrix', compact('matrices', 'actionTypes'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name'              => 'required|string|max:150',
            'action_type'       => 'required|string|max:80',
            'description'       => 'nullable|string|max:300',
            'min_amount'        => 'nullable|numeric|min:0',
            'max_amount'        => 'nullable|numeric|gt:min_amount',
            'total_steps'       => 'required|integer|min:1|max:5',
            'requires_checker'  => 'boolean',
            'escalation_hours'  => 'required|integer|min:1',
            'steps'             => 'required|array|min:1',
            'steps.*.step_name'     => 'required|string|max:100',
            'steps.*.approver_type' => 'required|in:role,user,department_head,any_manager',
            'steps.*.approver_value'=> 'nullable|string|max:150',
            'steps.*.timeout_hours' => 'required|integer|min:1',
            'steps.*.on_timeout'    => 'required|in:escalate,auto_approve,auto_reject',
        ]);

        $matrix = ApprovalMatrix::create([
            'tenant_id'        => $tenantId,
            'name'             => $validated['name'],
            'action_type'      => $validated['action_type'],
            'description'      => $validated['description'] ?? null,
            'min_amount'       => $validated['min_amount'] ?? null,
            'max_amount'       => $validated['max_amount'] ?? null,
            'total_steps'      => $validated['total_steps'],
            'requires_checker' => $request->boolean('requires_checker', true),
            'escalation_hours' => $validated['escalation_hours'],
            'is_active'        => true,
            'created_by'       => auth()->id(),
        ]);

        foreach ($validated['steps'] as $i => $step) {
            ApprovalMatrixStep::create([
                'matrix_id'      => $matrix->id,
                'step_number'    => $i + 1,
                'step_name'      => $step['step_name'],
                'approver_type'  => $step['approver_type'],
                'approver_value' => $step['approver_value'] ?? null,
                'is_mandatory'   => true,
                'timeout_hours'  => $step['timeout_hours'],
                'on_timeout'     => $step['on_timeout'],
            ]);
        }

        return back()->with('success', "Approval matrix \"{$matrix->name}\" created successfully.");
    }

    public function update(Request $request, ApprovalMatrix $approvalMatrix)
    {
        abort_unless($approvalMatrix->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'name'             => 'required|string|max:150',
            'is_active'        => 'boolean',
            'escalation_hours' => 'required|integer|min:1',
            'requires_checker' => 'boolean',
        ]);

        $approvalMatrix->update([
            'name'             => $request->name,
            'is_active'        => $request->boolean('is_active', true),
            'escalation_hours' => $request->escalation_hours,
            'requires_checker' => $request->boolean('requires_checker', true),
        ]);

        return back()->with('success', 'Approval matrix updated.');
    }

    public function destroy(ApprovalMatrix $approvalMatrix)
    {
        abort_unless($approvalMatrix->tenant_id === auth()->user()->tenant_id, 403);

        if ($approvalMatrix->requests()->whereIn('status', ['pending', 'in_review'])->exists()) {
            return back()->with('error', 'Cannot delete — there are pending requests using this matrix.');
        }

        $approvalMatrix->delete();
        return back()->with('success', 'Approval matrix deleted.');
    }

    public function toggle(ApprovalMatrix $approvalMatrix)
    {
        abort_unless($approvalMatrix->tenant_id === auth()->user()->tenant_id, 403);
        $approvalMatrix->update(['is_active' => !$approvalMatrix->is_active]);
        return back()->with('success', 'Matrix ' . ($approvalMatrix->is_active ? 'activated' : 'deactivated') . '.');
    }

    private function actionTypes(): array
    {
        return [
            'loan_disbursal'     => 'Loan Disbursement',
            'loan_writeoff'      => 'Loan Write-Off',
            'expense_claim'      => 'Expense Claim',
            'leave_request'      => 'Leave Request (Extended)',
            'asset_purchase'     => 'Asset Purchase',
            'vendor_payment'     => 'Vendor Payment',
            'payroll_run'        => 'Payroll Run Approval',
            'staff_hire'         => 'New Staff Hire',
            'staff_termination'  => 'Staff Termination',
            'budget_amendment'   => 'Budget Amendment',
            'interest_waiver'    => 'Loan Interest Waiver',
            'account_closure'    => 'Account Closure',
            'limit_override'     => 'Transaction Limit Override',
            'custom'             => 'Custom / Other',
        ];
    }
}
