<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanLiquidation;
use App\Models\LoanRestructure;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanRestructureController extends Controller
{
    public function index(Loan $loan)
    {
        $restructures = LoanRestructure::where('loan_id', $loan->id)
            ->with(['requestedBy', 'reviewedBy', 'newLoan'])
            ->orderByDesc('created_at')
            ->get();

        return view('loans.restructures.index', compact('loan', 'restructures'));
    }

    public function store(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Restructure can only be requested for active or overdue loans.');
        }

        $validated = $request->validate([
            'new_tenure'    => 'required|integer|min:1|max:360',
            'new_rate'      => 'required|numeric|min:0.1|max:100',
            'reason'        => 'required|string|min:20|max:1000',
            'officer_notes' => 'nullable|string|max:500',
        ]);

        $restructure = LoanRestructure::create([
            'loan_id'              => $loan->id,
            'status'               => 'pending',
            'previous_outstanding' => $loan->outstanding_principal,
            'previous_tenure'      => $loan->remaining_months,
            'previous_rate'        => $loan->interest_rate,
            'new_tenure'           => $validated['new_tenure'],
            'new_rate'             => $validated['new_rate'],
            'reason'               => $validated['reason'],
            'officer_notes'        => $validated['officer_notes'] ?? null,
            'requested_by'         => auth()->id(),
        ]);

        app(WorkflowService::class)->create('Loan Restructure', $restructure);

        return back()->with('success', 'Restructure request submitted. Pending management approval.');
    }

    /**
     * Approve: close the old loan and create a fresh one with the new terms.
     * The two loans are linked bidirectionally via parent_loan_id.
     */
    public function approve(LoanRestructure $restructure)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($restructure->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $oldLoan = $restructure->loan;

        try {
            DB::beginTransaction();

            // 1. Compute new loan figures
            $principal      = (float) $oldLoan->outstanding_principal;
            $newInterest    = round($principal * ($restructure->new_rate / 100 / 12) * $restructure->new_tenure, 2);
            $newOutstanding = $principal + $newInterest;

            // 2. Generate new loan number (append -RX suffix)
            $restructureCount = LoanRestructure::where('loan_id', $oldLoan->id)
                ->where('status', 'approved')->count() + 1;
            $newLoanNumber = $oldLoan->loan_number . '-R' . $restructureCount;

            // 3. Create the new (restructured) loan
            $newLoan = Loan::create([
                'tenant_id'          => $oldLoan->tenant_id,
                'customer_id'        => $oldLoan->customer_id,
                'account_id'         => $oldLoan->account_id,
                'product_id'         => $oldLoan->product_id,
                'group_id'           => $oldLoan->group_id,
                'parent_loan_id'     => $oldLoan->id,
                'loan_number'        => $newLoanNumber,
                'principal_amount'   => $principal,
                'outstanding_balance'=> $newOutstanding,
                'interest_rate'      => $restructure->new_rate,
                'interest_method'    => $oldLoan->interest_method,
                'amortization'       => $oldLoan->amortization,
                'tenure_days'        => $restructure->new_tenure,
                'repayment_frequency'=> $oldLoan->repayment_frequency,
                'purpose'            => $oldLoan->purpose,
                'source_channel'     => $oldLoan->source_channel,
                'collateral_desc'    => $oldLoan->collateral_desc,
                'collateral_value'   => $oldLoan->collateral_value,
                'status'             => 'active',
                'disbursed_at'       => now(),
            ]);

            // 4. Record a liquidation entry for the old loan (audit trail)
            LoanLiquidation::create([
                'loan_id'         => $oldLoan->id,
                'type'            => 'full',
                'gross_amount'    => $oldLoan->outstanding_balance,
                'discount_amount' => round((float)$oldLoan->outstanding_balance - $principal, 2),
                'net_amount'      => $principal,
                'reference'       => 'RST-' . strtoupper(Str::random(8)),
                'notes'           => "Loan restructured → {$newLoanNumber}. Old balance settled; new loan created.",
                'processed_by'    => auth()->id(),
            ]);

            // 5. Close the old loan
            $oldLoan->update([
                'status'              => 'restructured',
                'outstanding_balance' => 0,
            ]);

            // 6. Stamp the restructure record
            $restructure->update([
                'status'      => 'approved',
                'new_loan_id' => $newLoan->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // 7. Resolve the workflow instance
            app(WorkflowService::class)->resolveForSubject($restructure, 'approve', null, auth()->user());

            DB::commit();

            // Notify customer
            $newLoan->load('customer');
            if ($newLoan->customer) {
                app(NotificationService::class)->send($newLoan->customer, 'loan_restructured', [
                    'customer_name'        => $newLoan->customer->first_name . ' ' . $newLoan->customer->last_name,
                    'new_loan_number'      => $newLoanNumber,
                    'previous_outstanding' => number_format((float) $restructure->previous_outstanding, 2),
                    'new_outstanding'      => number_format($newOutstanding, 2),
                    'new_rate'             => number_format($restructure->new_rate, 2) . '%',
                    'new_tenure'           => $restructure->new_tenure . ' months',
                    'old_loan_number'      => $oldLoan->loan_number,
                ]);
            }

            return redirect()->route('loans.show', $newLoan)
                ->with('success', "✅ Restructure approved. New loan {$newLoanNumber} created with ₦" . number_format($newOutstanding, 2) . " over {$restructure->new_tenure} months.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Restructure approval failed: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, LoanRestructure $restructure)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($restructure->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $restructure->update([
            'status'        => 'rejected',
            'officer_notes' => $request->input('rejection_reason', 'Rejected by management.'),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        // Resolve the workflow instance
        app(WorkflowService::class)->resolveForSubject($restructure, 'reject', $request->input('rejection_reason'), auth()->user());

        // Notify customer
        $restructure->load('loan.customer');
        if ($restructure->loan?->customer) {
            app(NotificationService::class)->send($restructure->loan->customer, 'loan_restructure_rejected', [
                'customer_name' => $restructure->loan->customer->first_name . ' ' . $restructure->loan->customer->last_name,
                'loan_number'   => $restructure->loan->loan_number,
                'reason'        => $request->input('rejection_reason', 'Rejected by management.'),
            ]);
        }

        return back()->with('success', 'Restructure request has been rejected.');
    }
}
