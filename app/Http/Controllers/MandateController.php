<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountMandate;
use App\Models\MandateApproval;
use App\Models\MandateSignatory;
use App\Models\User;
use App\Services\MandateService;
use Illuminate\Http\Request;

class MandateController extends Controller
{
    public function __construct(protected MandateService $mandateService)
    {
    }

    /**
     * List all account mandates for the current tenant.
     */
    public function index(Request $request)
    {
        $query = AccountMandate::with(['account.customer', 'signatories']);

        if ($request->filled('signing_rule')) {
            $query->where('signing_rule', $request->signing_rule);
        }

        $mandates = $query->latest()->paginate(20)->withQueryString();

        return view('mandates.index', compact('mandates'));
    }

    /**
     * Show the create mandate form.
     */
    public function create()
    {
        $accounts = Account::with('customer')
            ->where('status', 'active')
            ->orderBy('account_number')
            ->get();

        $staff = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('mandates.create', compact('accounts', 'staff'));
    }

    /**
     * Store a new mandate with optional signatories.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id'       => 'required|uuid|exists:accounts,id',
            'signing_rule'     => 'required|in:sole,any_one,any_two,a_and_b,a_and_any_b,all',
            'max_amount_sole'  => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:1000',
            'effective_from'   => 'nullable|date',
            'effective_to'     => 'nullable|date|after_or_equal:effective_from',
            'signatories'              => 'nullable|array',
            'signatories.*.name'       => 'required_with:signatories|string|max:150',
            'signatories.*.class'      => 'required_with:signatories|in:A,B,C',
            'signatories.*.phone'      => 'nullable|string|max:20',
            'signatories.*.email'      => 'nullable|email|max:100',
            'signatories.*.user_id'    => 'nullable|exists:users,id',
        ]);

        // Deactivate any existing active mandate on this account
        AccountMandate::where('account_id', $validated['account_id'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $mandate = AccountMandate::create([
            'account_id'      => $validated['account_id'],
            'signing_rule'    => $validated['signing_rule'],
            'max_amount_sole' => $validated['max_amount_sole'] ?? null,
            'description'     => $validated['description'] ?? null,
            'effective_from'  => $validated['effective_from'] ?? null,
            'effective_to'    => $validated['effective_to'] ?? null,
            'is_active'       => true,
            'created_by'      => auth()->id(),
        ]);

        if (! empty($validated['signatories'])) {
            foreach ($validated['signatories'] as $sig) {
                MandateSignatory::create([
                    'mandate_id'      => $mandate->id,
                    'signatory_name'  => $sig['name'],
                    'signatory_class' => $sig['class'],
                    'phone'           => $sig['phone'] ?? null,
                    'email'           => $sig['email'] ?? null,
                    'user_id'         => $sig['user_id'] ?? null,
                    'is_active'       => true,
                ]);
            }
        }

        return redirect()->route('mandates.show', $mandate)
            ->with('success', 'Mandate created successfully.');
    }

    /**
     * Show mandate detail with signatories and recent approvals.
     */
    public function show(AccountMandate $mandate)
    {
        $mandate->load([
            'account.customer',
            'signatories.user',
            'approvals' => fn($q) => $q->latest()->take(20),
            'approvals.requestedBy',
            'approvals.actions.actionedBy',
        ]);

        $staff = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('mandates.show', compact('mandate', 'staff'));
    }

    /**
     * Update signing rule, max_amount_sole, or description.
     */
    public function update(Request $request, AccountMandate $mandate)
    {
        $validated = $request->validate([
            'signing_rule'    => 'required|in:sole,any_one,any_two,a_and_b,a_and_any_b,all',
            'max_amount_sole' => 'nullable|numeric|min:0',
            'description'     => 'nullable|string|max:1000',
            'effective_from'  => 'nullable|date',
            'effective_to'    => 'nullable|date|after_or_equal:effective_from',
        ]);

        $mandate->update($validated);

        return back()->with('success', 'Mandate updated successfully.');
    }

    /**
     * Add a signatory to an existing mandate.
     */
    public function storeSig(Request $request, AccountMandate $mandate)
    {
        $validated = $request->validate([
            'signatory_name'  => 'required|string|max:150',
            'signatory_class' => 'required|in:A,B,C',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'user_id'         => 'nullable|exists:users,id',
        ]);

        MandateSignatory::create([
            'mandate_id'      => $mandate->id,
            'signatory_name'  => $validated['signatory_name'],
            'signatory_class' => $validated['signatory_class'],
            'phone'           => $validated['phone'] ?? null,
            'email'           => $validated['email'] ?? null,
            'user_id'         => $validated['user_id'] ?? null,
            'is_active'       => true,
        ]);

        return back()->with('success', 'Signatory added successfully.');
    }

    /**
     * Soft-deactivate a signatory.
     */
    public function destroySig(MandateSignatory $signatory)
    {
        $signatory->update(['is_active' => false]);

        return back()->with('success', 'Signatory removed from mandate.');
    }

    /**
     * List all pending approvals for the current tenant.
     */
    public function approvals(Request $request)
    {
        $approvals = MandateApproval::where('status', 'pending')
            ->with(['account.customer', 'mandate', 'requestedBy', 'actions.actionedBy', 'actions.signatory'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('mandates.approvals', compact('approvals'));
    }

    /**
     * Approve a mandate approval request.
     */
    public function approve(Request $request, MandateApproval $approval)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This approval request is no longer pending.');
        }

        $complete = $this->mandateService->approve($approval, auth()->user(), $request->notes);

        $message = $complete
            ? 'Approval recorded. The transaction has been fully approved.'
            : 'Approval recorded. Awaiting additional signatories.';

        return back()->with('success', $message);
    }

    /**
     * Reject a mandate approval request.
     */
    public function reject(Request $request, MandateApproval $approval)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($approval->status !== 'pending') {
            return back()->with('error', 'This approval request is no longer pending.');
        }

        $this->mandateService->reject($approval, auth()->user(), $request->notes);

        return back()->with('success', 'Approval request rejected.');
    }
}
