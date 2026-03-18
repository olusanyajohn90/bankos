<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\SalaryAdvance;
use App\Models\StaffProfile;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalaryAdvanceController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $tenantId = session('tenant_id');
        $isAdmin  = auth()->user()->hasRole(['admin','hr_manager','finance_manager','super_admin']);

        $query = SalaryAdvance::where('tenant_id', $tenantId)
            ->with('user','approvedBy')
            ->orderByDesc('created_at');

        if (! $isAdmin) {
            $query->where('user_id', auth()->id());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $advances = $query->paginate(25)->withQueryString();

        $stats = [
            'pending'   => SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'approved'  => SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'approved')->count(),
            'disbursed' => SalaryAdvance::where('tenant_id', $tenantId)->where('status', 'disbursed')->count(),
            'total_outstanding' => SalaryAdvance::where('tenant_id', $tenantId)
                ->whereIn('status', ['approved','disbursed'])->sum('balance_remaining'),
        ];

        return view('hr.salary-advances.index', compact('advances','stats','isAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount_requested' => 'required|numeric|min:1000',
            'reason'           => 'required|string|max:500',
            'repayment_months' => 'required|integer|min:1|max:12',
        ]);

        $tenantId = session('tenant_id');

        // Check if staff already has a pending/approved advance
        $existing = SalaryAdvance::where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending','approved','disbursed'])
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have an outstanding salary advance.');
        }

        $profile = StaffProfile::where('user_id', auth()->id())
            ->where('tenant_id', $tenantId)->first();

        SalaryAdvance::create([
            'id'               => Str::uuid(),
            'tenant_id'        => $tenantId,
            'user_id'          => auth()->id(),
            'staff_profile_id' => $profile?->id,
            'amount_requested' => $request->amount_requested,
            'reason'           => $request->reason,
            'repayment_months' => $request->repayment_months,
            'status'           => 'pending',
            'balance_remaining'=> 0,
        ]);

        return back()->with('success', 'Salary advance request submitted.');
    }

    public function approve(Request $request, SalaryAdvance $salaryAdvance)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','finance_manager']), 403);
        abort_unless($salaryAdvance->status === 'pending', 422);

        $request->validate(['amount_approved' => 'required|numeric|min:1']);

        $amountApproved   = $request->amount_approved;
        $repaymentMonths  = $salaryAdvance->repayment_months;
        $monthlyDeduction = round($amountApproved / $repaymentMonths, 2);

        $salaryAdvance->update([
            'status'            => 'approved',
            'amount_approved'   => $amountApproved,
            'monthly_deduction' => $monthlyDeduction,
            'balance_remaining' => $amountApproved,
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
        ]);

        return back()->with('success', 'Salary advance approved. Monthly deduction: ₦' . number_format($monthlyDeduction, 2));
    }

    public function reject(Request $request, SalaryAdvance $salaryAdvance)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','finance_manager']), 403);

        $request->validate(['reason' => 'required|string|max:500']);
        $salaryAdvance->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Request rejected.');
    }

    public function disburse(SalaryAdvance $salaryAdvance)
    {
        abort_unless(auth()->user()->hasRole(['admin','finance_manager']), 403);
        abort_unless($salaryAdvance->status === 'approved', 422);

        $salaryAdvance->update([
            'status'       => 'disbursed',
            'disbursed_at' => now(),
        ]);

        return back()->with('success', 'Advance marked as disbursed.');
    }
}
