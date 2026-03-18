<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProxyActionController extends Controller
{
    // -------------------------------------------------------------------------
    // Helper: assert customer belongs to acting staff's tenant
    // -------------------------------------------------------------------------
    private function assertTenant(Customer $customer): void
    {
        if ($customer->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Customer does not belong to your institution.');
        }
    }

    // -------------------------------------------------------------------------
    // Helper: write a proxy_actions_log row
    // -------------------------------------------------------------------------
    private function logAction(Customer $customer, string $action, string $reason, array $payload = []): void
    {
        DB::table('proxy_actions_log')->insert([
            'id'          => (string) Str::uuid(),
            'tenant_id'   => auth()->user()->tenant_id,
            'actor_id'    => auth()->id(),
            'customer_id' => $customer->id,
            'action'      => $action,
            'payload'     => json_encode($payload),
            'reason'      => $reason,
            'ip_address'  => request()->ip(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Transfer Funds on behalf of customer
    // -------------------------------------------------------------------------
    public function transfer(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'from_account_id'  => 'required|string',
            'to_account_number'=> 'required|string',
            'amount'           => 'required|numeric|min:1',
            'reason'           => 'required|string|max:500',
            'description'      => 'nullable|string|max:255',
        ]);

        $fromAccount = Account::where('id', $validated['from_account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $fromAccount) {
            return back()->withErrors(['from_account_id' => 'Source account not found or does not belong to this customer.']);
        }

        if ($fromAccount->status !== 'active') {
            return back()->withErrors(['from_account_id' => 'Source account is not active.']);
        }

        if ($fromAccount->available_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient available balance.']);
        }

        $toAccount = Account::where('account_number', $validated['to_account_number'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $toAccount) {
            return back()->withErrors(['to_account_number' => 'Destination account not found.']);
        }

        $reference = 'PROXY-' . strtoupper(Str::random(8));
        $amount     = $validated['amount'];
        $desc       = $validated['description'] ?? "Proxy transfer to {$toAccount->account_number}";

        DB::transaction(function () use ($fromAccount, $toAccount, $amount, $reference, $desc, $customer, $validated) {
            // Debit source
            $fromAccount->decrement('available_balance', $amount);
            $fromAccount->decrement('ledger_balance', $amount);

            // Credit destination
            $toAccount->increment('available_balance', $amount);
            $toAccount->increment('ledger_balance', $amount);

            // Debit transaction
            Transaction::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'account_id'  => $fromAccount->id,
                'reference'   => $reference,
                'type'        => 'transfer',
                'amount'      => -$amount,
                'currency'    => $fromAccount->currency,
                'description' => $desc,
                'status'      => 'completed',
                'performed_by'=> auth()->id(),
            ]);

            // Credit transaction
            Transaction::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'account_id'  => $toAccount->id,
                'reference'   => $reference,
                'type'        => 'transfer',
                'amount'      => $amount,
                'currency'    => $toAccount->currency,
                'description' => "Proxy transfer from {$fromAccount->account_number}",
                'status'      => 'completed',
                'performed_by'=> auth()->id(),
            ]);

            $this->logAction($customer, 'transfer', $validated['reason'], [
                'from_account' => $fromAccount->account_number,
                'to_account'   => $toAccount->account_number,
                'amount'       => $amount,
                'reference'    => $reference,
            ]);
        });

        return back()->with('success', "Transfer of ₦" . number_format($amount, 2) . " completed. Ref: {$reference}");
    }

    // -------------------------------------------------------------------------
    // Freeze an account
    // -------------------------------------------------------------------------
    public function freezeAccount(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'account_id' => 'required|string',
            'reason'     => 'required|string|max:500',
        ]);

        $updated = Account::where('id', $validated['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->update(['status' => 'frozen']);

        if (! $updated) {
            return back()->withErrors(['account_id' => 'Account not found or does not belong to this customer.']);
        }

        $account = Account::find($validated['account_id']);

        Transaction::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'account_id'  => $validated['account_id'],
            'reference'   => 'SYS-' . strtoupper(Str::random(8)),
            'type'        => 'system_action',
            'amount'      => 0,
            'currency'    => $account->currency ?? 'NGN',
            'description' => 'Account frozen by admin: ' . $validated['reason'],
            'status'      => 'completed',
            'performed_by'=> auth()->id(),
        ]);

        $this->logAction($customer, 'freeze_account', $validated['reason'], [
            'account_id' => $validated['account_id'],
        ]);

        return back()->with('success', 'Account has been frozen successfully.');
    }

    // -------------------------------------------------------------------------
    // Unfreeze an account
    // -------------------------------------------------------------------------
    public function unfreezeAccount(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'account_id' => 'required|string',
            'reason'     => 'required|string|max:500',
        ]);

        $updated = Account::where('id', $validated['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->update(['status' => 'active']);

        if (! $updated) {
            return back()->withErrors(['account_id' => 'Account not found or does not belong to this customer.']);
        }

        $account = Account::find($validated['account_id']);

        Transaction::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'account_id'  => $validated['account_id'],
            'reference'   => 'SYS-' . strtoupper(Str::random(8)),
            'type'        => 'system_action',
            'amount'      => 0,
            'currency'    => $account->currency ?? 'NGN',
            'description' => 'Account unfrozen by admin: ' . $validated['reason'],
            'status'      => 'completed',
            'performed_by'=> auth()->id(),
        ]);

        $this->logAction($customer, 'unfreeze_account', $validated['reason'], [
            'account_id' => $validated['account_id'],
        ]);

        return back()->with('success', 'Account has been unfrozen successfully.');
    }

    // -------------------------------------------------------------------------
    // Reset PIN
    // -------------------------------------------------------------------------
    public function updatePin(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'new_pin' => 'required|digits_between:4,6',
            'reason'  => 'required|string|max:500',
        ]);

        $customer->update([
            'portal_pin' => Hash::make($validated['new_pin']),
        ]);

        $this->logAction($customer, 'reset_pin', $validated['reason'], [
            'note' => 'PIN reset',
        ]);

        return back()->with('success', 'Customer PIN has been reset successfully.');
    }

    // -------------------------------------------------------------------------
    // Open a new account for a customer
    // -------------------------------------------------------------------------
    public function openAccount(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'type'   => 'required|in:savings,current,domiciliary,kids',
            'reason' => 'required|string|max:500',
        ]);

        // Check customer doesn't already have an active account of that type
        $existing = Account::where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('type', $validated['type'])
            ->where('status', 'active')
            ->exists();

        if ($existing) {
            return back()->withErrors(['type' => "Customer already has an active {$validated['type']} account."]);
        }

        $accountNumber = '20' . str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $currency      = $validated['type'] === 'domiciliary' ? 'USD' : 'NGN';
        $accountName   = strtoupper(trim($customer->first_name . ' ' . $customer->last_name));

        $account = Account::create([
            'id'                => (string) Str::uuid(),
            'tenant_id'         => auth()->user()->tenant_id,
            'customer_id'       => $customer->id,
            'account_number'    => $accountNumber,
            'account_name'      => $accountName,
            'type'              => $validated['type'],
            'currency'          => $currency,
            'available_balance' => 0,
            'ledger_balance'    => 0,
            'status'            => 'active',
            'opened_by'         => auth()->id(),
        ]);

        $this->logAction($customer, 'open_account', $validated['reason'], [
            'account_number' => $accountNumber,
            'type'           => $validated['type'],
            'currency'       => $currency,
        ]);

        return back()->with('success', "New {$validated['type']} account ({$accountNumber}) opened successfully.");
    }

    // -------------------------------------------------------------------------
    // Close an account
    // -------------------------------------------------------------------------
    public function closeAccount(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'account_id' => 'required|string',
            'reason'     => 'required|string|max:500',
        ]);

        $account = Account::where('id', $validated['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $account) {
            return back()->withErrors(['account_id' => 'Account not found or does not belong to this customer.']);
        }

        if ($account->available_balance != 0 || $account->ledger_balance != 0) {
            return back()->withErrors(['account_id' => 'Account balance must be zero before closing.']);
        }

        $account->update([
            'status'          => 'closed',
            'closed_at'       => now(),
            'closure_reason'  => $validated['reason'],
            'closed_by'       => auth()->id(),
        ]);

        $this->logAction($customer, 'close_account', $validated['reason'], [
            'account_id'     => $account->id,
            'account_number' => $account->account_number,
        ]);

        return back()->with('success', "Account {$account->account_number} has been closed.");
    }

    // -------------------------------------------------------------------------
    // Waive a fee transaction
    // -------------------------------------------------------------------------
    public function waiveFee(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'reason'         => 'required|string|max:500',
        ]);

        $customerAccountIds = Account::where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->pluck('id');

        $transaction = Transaction::where('id', $validated['transaction_id'])
            ->whereIn('account_id', $customerAccountIds)
            ->where('type', 'fee')
            ->where('status', '!=', 'reversed')
            ->first();

        if (! $transaction) {
            return back()->withErrors(['transaction_id' => 'Fee transaction not found or already reversed.']);
        }

        $reversalAmount = abs($transaction->amount);

        DB::transaction(function () use ($transaction, $reversalAmount, $customer, $validated) {
            // Credit account back
            Account::where('id', $transaction->account_id)->increment('available_balance', $reversalAmount);
            Account::where('id', $transaction->account_id)->increment('ledger_balance', $reversalAmount);

            // Create reversal transaction
            Transaction::create([
                'tenant_id'              => auth()->user()->tenant_id,
                'account_id'             => $transaction->account_id,
                'reference'              => 'REV-' . strtoupper(Str::random(8)),
                'type'                   => 'fee_reversal',
                'amount'                 => $reversalAmount,
                'currency'               => $transaction->currency,
                'description'            => 'Fee waived by admin: ' . $validated['reason'],
                'status'                 => 'completed',
                'performed_by'           => auth()->id(),
                'related_transaction_id' => $transaction->id,
            ]);

            // Mark original as reversed
            $transaction->update(['status' => 'reversed']);

            $this->logAction($customer, 'waive_fee', $validated['reason'], [
                'transaction_id' => $transaction->id,
                'amount'         => $reversalAmount,
            ]);
        });

        return back()->with('success', "Fee of ₦" . number_format($reversalAmount, 2) . " has been waived successfully.");
    }

    // -------------------------------------------------------------------------
    // Proxy Loan Repayment
    // -------------------------------------------------------------------------
    public function proxyLoanRepayment(Request $request, Customer $customer)
    {
        $this->assertTenant($customer);

        $validated = $request->validate([
            'loan_id'    => 'required|string',
            'account_id' => 'required|string',
            'amount'     => 'required|numeric|min:1',
            'reason'     => 'required|string|max:500',
        ]);

        $loan = Loan::where('id', $validated['loan_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['active', 'overdue'])
            ->first();

        if (! $loan) {
            return back()->withErrors(['loan_id' => 'Loan not found or not in repayable status.']);
        }

        $account = Account::where('id', $validated['account_id'])
            ->where('customer_id', $customer->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (! $account) {
            return back()->withErrors(['account_id' => 'Account not found or does not belong to this customer.']);
        }

        if ($account->available_balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Insufficient account balance.']);
        }

        $amount = $validated['amount'];

        DB::transaction(function () use ($account, $loan, $amount, $customer, $validated) {
            // Debit account
            $account->decrement('available_balance', $amount);
            $account->decrement('ledger_balance', $amount);

            // Create debit transaction
            Transaction::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'account_id'  => $account->id,
                'reference'   => 'LRPY-' . strtoupper(Str::random(8)),
                'type'        => 'loan_repayment',
                'amount'      => -$amount,
                'currency'    => $account->currency,
                'description' => "Proxy loan repayment for loan {$loan->loan_number}",
                'status'      => 'completed',
                'performed_by'=> auth()->id(),
            ]);

            // Reduce outstanding balance
            $newOutstanding = max(0, $loan->outstanding_balance - $amount);
            $newStatus      = $newOutstanding <= 0 ? 'settled' : $loan->status;

            $loan->update([
                'outstanding_balance' => $newOutstanding,
                'status'              => $newStatus,
            ]);

            $this->logAction($customer, 'loan_repayment', $validated['reason'], [
                'loan_id'          => $loan->id,
                'loan_number'      => $loan->loan_number,
                'amount'           => $amount,
                'outstanding_after'=> $newOutstanding,
                'settled'          => $newStatus === 'settled',
            ]);
        });

        return back()->with('success', "Loan repayment of ₦" . number_format($amount, 2) . " processed for loan {$loan->loan_number}.");
    }

    // -------------------------------------------------------------------------
    // Action Log view
    // -------------------------------------------------------------------------
    public function actionLog(Customer $customer)
    {
        $this->assertTenant($customer);

        $proxyActions = DB::table('proxy_actions_log as pal')
            ->join('users as u', 'u.id', '=', 'pal.actor_id')
            ->where('pal.customer_id', $customer->id)
            ->where('pal.tenant_id', auth()->user()->tenant_id)
            ->select('pal.*', 'u.name as actor_name')
            ->orderByDesc('pal.created_at')
            ->paginate(20);

        return view('customers.proxy-log', compact('customer', 'proxyActions'));
    }
}
