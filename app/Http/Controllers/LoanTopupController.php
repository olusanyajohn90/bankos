<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanLiquidation;
use App\Models\LoanTopup;
use App\Models\Transaction;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanTopupController extends Controller
{
    public function index(Loan $loan)
    {
        $topups = $loan->topups()
            ->with(['requestedBy', 'reviewedBy', 'newLoan'])
            ->orderByDesc('created_at')
            ->get();

        return view('loans.topups.index', compact('loan', 'topups'));
    }

    public function store(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Top-up can only be requested for active or overdue loans.');
        }

        $validated = $request->validate([
            'topup_amount'  => 'required|numeric|min:1',
            'new_tenure'    => 'required|integer|min:1|max:360',
            'new_rate'      => 'required|numeric|min:0.1|max:100',
            'reason'        => 'required|string|min:20|max:1000',
            'officer_notes' => 'nullable|string|max:500',
        ]);

        $topup = LoanTopup::create([
            'loan_id'      => $loan->id,
            'status'       => 'pending',
            'topup_amount' => $validated['topup_amount'],
            'new_tenure'   => $validated['new_tenure'],
            'new_rate'     => $validated['new_rate'],
            'reason'       => $validated['reason'],
            'officer_notes'=> $validated['officer_notes'] ?? null,
            'requested_by' => auth()->id(),
        ]);

        app(WorkflowService::class)->create('Loan Top-up', $topup, [
            'amount' => $validated['topup_amount'],
        ]);

        return back()->with('success', 'Top-up request submitted. Pending management approval.');
    }

    /**
     * Approve: Close the old loan, create a fresh one with combined principal,
     * and disburse ONLY the top-up amount to the customer's account.
     */
    public function approve(LoanTopup $topup)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($topup->status !== 'pending') {
            return back()->with('error', 'This top-up request has already been reviewed.');
        }

        $oldLoan = $topup->loan;

        try {
            DB::beginTransaction();

            // 1. Compute new loan figures (Old Principal + Top-up Amount)
            $oldPrincipal   = (float) $oldLoan->outstanding_principal;
            $newPrincipal   = $oldPrincipal + (float) $topup->topup_amount;
            
            $newInterest    = round($newPrincipal * ($topup->new_rate / 100 / 12) * $topup->new_tenure, 2);
            $newOutstanding = $newPrincipal + $newInterest;

            // 2. Generate new loan number (append -T1 suffix)
            $topupCount = LoanTopup::where('loan_id', $oldLoan->id)
                ->where('status', 'approved')->count() + 1;
            $newLoanNumber = $oldLoan->loan_number . '-T' . $topupCount;

            // 3. Create the new (topped-up) loan
            $newLoan = Loan::create([
                'tenant_id'          => $oldLoan->tenant_id,
                'customer_id'        => $oldLoan->customer_id,
                'account_id'         => $oldLoan->account_id,
                'product_id'         => $oldLoan->product_id,
                'group_id'           => $oldLoan->group_id,
                'parent_loan_id'     => $oldLoan->id,
                'loan_number'        => $newLoanNumber,
                'principal_amount'   => $newPrincipal,
                'outstanding_balance'=> $newOutstanding,
                'interest_rate'      => $topup->new_rate,
                'interest_method'    => $oldLoan->interest_method,
                'amortization'       => $oldLoan->amortization,
                'tenure_days'        => $topup->new_tenure,
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
                'discount_amount' => round((float)$oldLoan->outstanding_balance - $oldPrincipal, 2),
                'net_amount'      => $oldPrincipal,
                'reference'       => 'TOP-' . strtoupper(Str::random(8)),
                'notes'           => "Loan topped up → {$newLoanNumber}. Old balance wrapped into new principal.",
                'processed_by'    => auth()->id(),
            ]);

            // 5. Close the old loan
            $oldLoan->update([
                'status'              => 'restructured', // Settled and replaced
                'outstanding_balance' => 0,
            ]);

            // 6. Stamp the top-up record
            $topup->update([
                'status'      => 'approved',
                'new_loan_id' => $newLoan->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // 6.5 Resolve the workflow
            app(WorkflowService::class)->resolveForSubject($topup, 'approve', null, auth()->user());

            // 7. Disburse the TOP-UP PORTION ONLY to the customer's account
            if ($oldLoan->account_id) {
                $account = Account::find($oldLoan->account_id);
                if ($account) {
                    $account->increment('available_balance', $topup->topup_amount);
                    $account->increment('ledger_balance', $topup->topup_amount);

                    Transaction::create([
                        'tenant_id'   => $oldLoan->tenant_id,
                        'account_id'  => $account->id,
                        'reference'   => 'TDS-' . strtoupper(Str::random(10)),
                        'type'        => 'disbursement',
                        'amount'      => $topup->topup_amount,
                        'currency'    => 'NGN',
                        'status'      => 'success',
                        'narration'   => "Top-up disbursement for loan {$newLoanNumber}",
                        'created_by'  => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // Notify customer
            $newLoan->load('customer');
            if ($newLoan->customer) {
                app(NotificationService::class)->send($newLoan->customer, 'loan_topup_approved', [
                    'customer_name'   => $newLoan->customer->first_name . ' ' . $newLoan->customer->last_name,
                    'topup_amount'    => number_format((float) $topup->topup_amount, 2),
                    'new_loan_number' => $newLoanNumber,
                    'new_principal'   => number_format($newPrincipal, 2),
                    'new_tenure'      => $topup->new_tenure . ' months',
                    'new_rate'        => number_format($topup->new_rate, 2) . '%',
                    'account_number'  => optional(Account::find($oldLoan->account_id))->account_number ?? 'N/A',
                ]);
            }

            return redirect()->route('loans.show', $newLoan)
                ->with('success', "✅ Top-up approved. New loan {$newLoanNumber} created and ₦" . number_format($topup->topup_amount, 2) . " disbursed to account.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Top-up approval failed: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, LoanTopup $topup)
    {
        if (!auth()->user()->can('loans.approve_l1') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if ($topup->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $topup->update([
            'status'        => 'rejected',
            'officer_notes' => $request->input('rejection_reason', 'Rejected by management.'),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        // Resolve the workflow
        app(WorkflowService::class)->resolveForSubject($topup, 'reject', $request->input('rejection_reason'), auth()->user());

        // Notify customer
        $topup->load('loan.customer');
        if ($topup->loan?->customer) {
            app(NotificationService::class)->send($topup->loan->customer, 'loan_topup_rejected', [
                'customer_name'       => $topup->loan->customer->first_name . ' ' . $topup->loan->customer->last_name,
                'topup_amount'        => number_format((float) $topup->topup_amount, 2),
                'original_loan_number'=> $topup->loan->loan_number,
                'reason'              => $request->input('rejection_reason', 'Rejected by management.'),
            ]);
        }

        return back()->with('success', 'Top-up request has been rejected.');
    }
}
