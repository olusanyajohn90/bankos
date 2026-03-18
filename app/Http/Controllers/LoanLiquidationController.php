<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanLiquidation;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanLiquidationController extends Controller
{
    /**
     * Process a partial or full loan liquidation.
     *
     * Partial liquidation: lump sum > regular instalment that reduces principal.
     * Full liquidation: pays off entire outstanding balance; optional early
     * settlement discount is applied to the interest portion.
     */
    public function store(Request $request, Loan $loan)
    {
        if (!auth()->user()->can('transactions.create') && !auth()->user()->hasRole('tenant_admin')) {
            abort(403);
        }

        if (!in_array($loan->status, ['active', 'overdue'])) {
            return back()->with('error', 'Liquidation can only be posted against active or overdue loans.');
        }

        $validated = $request->validate([
            'type'         => 'required|in:partial,full',
            'gross_amount' => 'required_if:type,partial|nullable|numeric|min:1',
            'notes'        => 'nullable|string|max:500',
        ]);

        $type            = $validated['type'];
        $grossAmount     = (float) $loan->outstanding_balance; // full outstanding including interest

        if ($type === 'full') {
            // Customer only pays the outstanding PRINCIPAL — they are not charged
            // for months they won't use. The unearned interest is the bank's rebate.
            $outstandingPrincipal = (float) $loan->outstanding_principal;
            $unearnedInterest     = round($grossAmount - $outstandingPrincipal, 2);
            $netAmount            = $outstandingPrincipal;
            $discountAmount       = $unearnedInterest; // Interest rebate recorded for audit
        } else {
            // Partial liquidation: customer pays any amount they choose
            $grossAmount    = (float) $validated['gross_amount'];
            $discountAmount = 0;
            $netAmount      = $grossAmount;
        }

        $account = Account::findOrFail($loan->account_id);

        if ($account->available_balance < $netAmount) {
            return back()->with('error', 'Insufficient funds in the linked account.');
        }

        try {
            DB::beginTransaction();

            $reference = 'LQD-' . strtoupper(Str::random(10));

            // Record the liquidation event
            LoanLiquidation::create([
                'loan_id'         => $loan->id,
                'type'            => $type,
                'gross_amount'    => $grossAmount,
                'discount_amount' => $discountAmount,
                'net_amount'      => $netAmount,
                'reference'       => $reference,
                'notes'           => $validated['notes'] ?? null,
                'processed_by'    => auth()->id(),
            ]);

            // Create the transaction record
            Transaction::create([
                'account_id'  => $account->id,
                'reference'   => $reference,
                'type'        => 'liquidation',
                'amount'      => $netAmount,
                'currency'    => $account->currency,
                'description' => ucfirst($type) . " Liquidation: {$loan->loan_number}" . ($discountAmount > 0 ? " (Discount: ₦{$discountAmount})" : ''),
                'status'      => 'success',
            ]);

            // Debit the account
            $account->decrement('available_balance', $netAmount);
            $account->decrement('ledger_balance', $netAmount);

            // Update the loan's outstanding balance
            // For full settlement, zero out the balance entirely (the bank absorbs the unearned interest)
            if ($type === 'full') {
                $loanUpdate = ['outstanding_balance' => 0, 'status' => 'closed'];
            } else {
                $newOutstanding = max(0, (float) $loan->outstanding_balance - $netAmount);
                $loanUpdate = ['outstanding_balance' => $newOutstanding];
                if ($newOutstanding <= 0) $loanUpdate['status'] = 'closed';
            }

            $loan->update($loanUpdate);

            DB::commit();

            // Notify customer
            $loan->load('customer');
            if ($loan->customer) {
                $event = $type === 'full' ? 'loan_liquidation_full' : 'loan_liquidation_partial';
                app(NotificationService::class)->send($loan->customer, $event, [
                    'customer_name'   => $loan->customer->first_name . ' ' . $loan->customer->last_name,
                    'amount'          => number_format($netAmount, 2),
                    'loan_number'     => $loan->loan_number,
                    'reference'       => $reference,
                    'discount_amount' => number_format($discountAmount, 2),
                    'outstanding_balance' => number_format(max(0, (float) $loan->outstanding_balance - $netAmount), 2),
                ]);
            }

            $msg = "₦" . number_format($netAmount, 2) . " {$type} liquidation posted (Ref: {$reference}).";
            if ($type === 'full') {
                if ($discountAmount > 0) $msg .= " Unearned interest of ₦" . number_format($discountAmount, 2) . " waived.";
                $msg .= " 🎉 Loan fully settled and closed!";
            } elseif (isset($newOutstanding) && $newOutstanding <= 0) {
                $msg .= " 🎉 Loan fully settled and closed!";
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Liquidation failed: ' . $e->getMessage());
        }
    }
}
