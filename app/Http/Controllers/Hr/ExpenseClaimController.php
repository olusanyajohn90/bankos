<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\ExpenseClaim;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseClaimController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $tenantId = session('tenant_id');
        $query = ExpenseClaim::where('tenant_id', $tenantId)
            ->with('submittedBy', 'approvedBy')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        // Non-admins only see their own claims
        if (! auth()->user()->hasRole(['admin','hr_manager','super_admin'])) {
            $query->where('submitted_by', auth()->id());
        }

        $claims = $query->paginate(25)->withQueryString();

        $stats = [
            'pending'  => ExpenseClaim::where('tenant_id', $tenantId)->where('status', 'submitted')->count(),
            'approved' => ExpenseClaim::where('tenant_id', $tenantId)->where('status', 'approved')->count(),
            'paid'     => ExpenseClaim::where('tenant_id', $tenantId)->where('status', 'paid')->count(),
            'total_pending_amount' => ExpenseClaim::where('tenant_id', $tenantId)
                ->whereIn('status', ['submitted','approved'])->sum('amount'),
        ];

        $categories = ['travel','accommodation','meals','office_supplies','medical','entertainment','other'];

        return view('hr.expense-claims.index', compact('claims','stats','categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'category'     => 'required|string',
            'expense_date' => 'required|date|before_or_equal:today',
            'amount'       => 'required|numeric|min:1',
            'description'  => 'nullable|string|max:1000',
        ]);

        ExpenseClaim::create([
            'id'           => Str::uuid(),
            'tenant_id'    => session('tenant_id'),
            'submitted_by' => auth()->id(),
            'title'        => $request->title,
            'category'     => $request->category,
            'expense_date' => $request->expense_date,
            'amount'       => $request->amount,
            'description'  => $request->description,
            'status'       => 'draft',
        ]);

        return back()->with('success', 'Expense claim saved as draft.');
    }

    public function submit(ExpenseClaim $expenseClaim)
    {
        abort_unless($expenseClaim->submitted_by === auth()->id() || auth()->user()->hasRole(['admin','hr_manager']), 403);
        abort_unless($expenseClaim->status === 'draft', 422);

        $expenseClaim->update(['status' => 'submitted']);

        try {
            $result = $this->approvalService->initiate(
                tenantId:   $expenseClaim->tenant_id,
                actionType: 'expense_claim',
                subjectId:  $expenseClaim->id,
                requestedBy: auth()->id(),
                metadata:   [
                    'title'  => $expenseClaim->title,
                    'amount' => $expenseClaim->amount,
                ]
            );
            $expenseClaim->update(['approval_request_id' => $result->id ?? null]);
        } catch (\Throwable) {}

        return back()->with('success', 'Claim submitted for approval.');
    }

    public function approve(ExpenseClaim $expenseClaim)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','finance_manager']), 403);
        abort_unless($expenseClaim->status === 'submitted', 422);

        $expenseClaim->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Claim approved.');
    }

    public function reject(Request $request, ExpenseClaim $expenseClaim)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','finance_manager']), 403);

        $request->validate(['reason' => 'required|string|max:500']);
        $expenseClaim->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Claim rejected.');
    }

    public function markPaid(Request $request, ExpenseClaim $expenseClaim)
    {
        abort_unless(auth()->user()->hasRole(['admin','finance_manager']), 403);
        abort_unless($expenseClaim->status === 'approved', 422);

        $expenseClaim->update([
            'status'             => 'paid',
            'payment_reference'  => $request->payment_reference,
            'paid_at'            => now(),
        ]);

        return back()->with('success', 'Payment recorded.');
    }
}
