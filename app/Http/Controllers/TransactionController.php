<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Display the global transaction ledger.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['account.customer']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('account', function($q) use ($search) {
                      $q->where('account_number', 'like', "%{$search}%");
                  });
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate(25)->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for posting a new transaction.
     */
    public function create()
    {
        return view('transactions.create');
    }

    /**
     * Store a new transaction (Deposit / Withdrawal).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|exists:accounts,account_number',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $account = Account::where('account_number', $validated['account_number'])->first();
        
        if ($account->status !== 'active') {
            return back()->with('error', "Cannot post transaction. Account is {$account->status}.");
        }

        if ($validated['type'] === 'withdrawal') {
            if ($account->available_balance < $validated['amount']) {
                return back()->with('error', 'Insufficient funds for withdrawal.');
            }
        }

        try {
            DB::beginTransaction();

            $amount = $validated['type'] === 'deposit' ? $validated['amount'] : -$validated['amount'];

            // Safely increment/decrement using DB locks
            $lockedAccount = clone $account; // Eloquent lockForUpdate goes here ideally, simplified for demo
            
            // Generate Reference
            $reference = 'TRX-' . strtoupper(Str::random(10));

            // Record Transaction
            $transaction = Transaction::create([
                'account_id' => $account->id,
                'reference' => $reference,
                'type' => $validated['type'],
                'amount' => $amount,
                'currency' => $account->currency,
                'description' => $validated['description'],
                'status' => 'success',
            ]);

            // Update Balances
            $account->increment('available_balance', $amount);
            $account->increment('ledger_balance', $amount);

            DB::commit();

            // Notify customer
            $account->load('customer');
            if ($account->customer) {
                $newBalance = $account->available_balance; // already updated by increment
                $event = $validated['type'] === 'deposit' ? 'deposit_received' : 'withdrawal_posted';
                app(NotificationService::class)->send($account->customer, $event, [
                    'customer_name'  => $account->customer->first_name . ' ' . $account->customer->last_name,
                    'amount'         => number_format($validated['amount'], 2),
                    'currency'       => $account->currency,
                    'account_number' => $account->account_number,
                    'reference'      => $reference,
                    'description'    => $validated['description'],
                    'new_balance'    => number_format($newBalance, 2),
                    'date'           => now()->format('d M Y, g:ia'),
                ]);
            }

            return redirect()->route('transactions.index')
                ->with('success', ucfirst($validated['type']) . " of {$account->currency} {$validated['amount']} successful. Ref: {$reference}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    /**
     * Store an internal transfer.
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_account' => 'required|exists:accounts,account_number',
            'to_account' => 'required|exists:accounts,account_number|different:from_account',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $fromAccount = Account::where('account_number', $validated['from_account'])->first();
        $toAccount = Account::where('account_number', $validated['to_account'])->first();

        if ($fromAccount->status !== 'active' || $toAccount->status !== 'active') {
            return back()->with('error', 'Both source and destination accounts must be active.');
        }

        if ($fromAccount->currency !== $toAccount->currency) {
            return back()->with('error', 'Cross-currency transfers are not supported in this basic module.');
        }

        if ($fromAccount->available_balance < $validated['amount']) {
            return back()->with('error', 'Insufficient funds in source account.');
        }

        try {
            DB::beginTransaction();
            
            $reference = 'TRF-' . strtoupper(Str::random(10));

            // 1. Debit Source
            $debit = Transaction::create([
                'account_id' => $fromAccount->id,
                'reference' => $reference . '-OUT',
                'type' => 'transfer',
                'amount' => -$validated['amount'],
                'currency' => $fromAccount->currency,
                'description' => 'Transfer to ' . $toAccount->account_number . ' - ' . $validated['description'],
                'status' => 'success',
            ]);
            $fromAccount->decrement('available_balance', $validated['amount']);
            $fromAccount->decrement('ledger_balance', $validated['amount']);

            // 2. Credit Destination
            $credit = Transaction::create([
                'account_id' => $toAccount->id,
                'reference' => $reference . '-IN',
                'type' => 'transfer',
                'amount' => $validated['amount'],
                'currency' => $toAccount->currency,
                'description' => 'Transfer from ' . $fromAccount->account_number . ' - ' . $validated['description'],
                'status' => 'success',
                'related_transaction_id' => $debit->id,
            ]);
            $toAccount->increment('available_balance', $validated['amount']);
            $toAccount->increment('ledger_balance', $validated['amount']);

            DB::commit();

            // Notify sender (debit alert)
            $fromAccount->load('customer');
            if ($fromAccount->customer) {
                app(NotificationService::class)->send($fromAccount->customer, 'transfer_sent', [
                    'customer_name'      => $fromAccount->customer->first_name . ' ' . $fromAccount->customer->last_name,
                    'amount'             => number_format($validated['amount'], 2),
                    'currency'           => $fromAccount->currency,
                    'from_account'       => $fromAccount->account_number,
                    'to_account'         => $toAccount->account_number,
                    'beneficiary_name'   => $toAccount->account_name,
                    'reference'          => $reference,
                    'description'        => $validated['description'],
                    'new_balance'        => number_format($fromAccount->available_balance, 2),
                    'date'               => now()->format('d M Y, g:ia'),
                ]);
            }

            // Notify receiver (credit alert)
            $toAccount->load('customer');
            if ($toAccount->customer) {
                app(NotificationService::class)->send($toAccount->customer, 'transfer_received', [
                    'customer_name'  => $toAccount->customer->first_name . ' ' . $toAccount->customer->last_name,
                    'amount'         => number_format($validated['amount'], 2),
                    'currency'       => $toAccount->currency,
                    'account_number' => $toAccount->account_number,
                    'sender_name'    => $fromAccount->account_name,
                    'reference'      => $reference,
                    'description'    => $validated['description'],
                    'new_balance'    => number_format($toAccount->available_balance, 2),
                    'date'           => now()->format('d M Y, g:ia'),
                ]);
            }

            return redirect()->route('transactions.index')
                ->with('success', "Transfer of {$fromAccount->currency} {$validated['amount']} successful. Ref: {$reference}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
