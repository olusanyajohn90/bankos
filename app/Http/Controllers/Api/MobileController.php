<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['phone' => 'Invalid credentials.']);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'phone'      => $user->phone,
                'tenant_id'  => $user->tenant_id,
            ],
        ]);
    }

    public function balance(Request $request): JsonResponse
    {
        $user     = Auth::user();
        $customer = Customer::where('user_id', $user->id)
                            ->orWhere('phone', $user->phone)
                            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $accounts = Account::where('customer_id', $customer->id)->get(['account_number', 'type', 'balance', 'status']);

        return response()->json(['accounts' => $accounts]);
    }

    public function statement(Request $request): JsonResponse
    {
        $request->validate(['account_id' => 'required|exists:accounts,id']);

        $account = Account::findOrFail($request->account_id);

        $txns = Transaction::where('account_id', $account->id)
            ->latest('transaction_date')
            ->limit(50)
            ->get(['id', 'type', 'amount', 'narration', 'transaction_date', 'balance_after']);

        return response()->json([
            'account'      => $account->account_number,
            'balance'      => $account->available_balance,
            'transactions' => $txns,
        ]);
    }

    public function loanSummary(Request $request): JsonResponse
    {
        $user     = Auth::user();
        $customer = Customer::where('phone', $user->phone)->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $loans = Loan::where('customer_id', $customer->id)
            ->whereIn('status', ['active', 'overdue'])
            ->get(['id', 'loan_account_number', 'status', 'outstanding_balance', 'next_due_date', 'loan_amount']);

        return response()->json(['loans' => $loans]);
    }

    public function repay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'loan_id'         => 'required|exists:loans,id',
            'amount'          => 'required|numeric|min:100',
            'account_id'      => 'required|exists:accounts,id',
        ]);

        $loan    = Loan::findOrFail($data['loan_id']);
        $account = Account::findOrFail($data['account_id']);

        if ($account->available_balance < $data['amount']) {
            return response()->json(['error' => 'Insufficient account balance.'], 422);
        }

        // Debit account
        $account->decrement('available_balance', $data['amount']);
        $account->decrement('ledger_balance', $data['amount']);
        $account->refresh();

        // Record repayment transaction
        Transaction::create([
            'tenant_id'        => $loan->tenant_id,
            'account_id'       => $account->id,
            'loan_id'          => $loan->id,
            'type'             => 'repayment',
            'amount'           => $data['amount'],
            'narration'        => 'Mobile app loan repayment',
            'transaction_date' => now()->toDateString(),
            'balance_after'    => $account->available_balance,
            'reference'        => 'MOB-' . strtoupper(\Illuminate\Support\Str::random(10)),
        ]);

        // Update loan outstanding
        $loan->decrement('outstanding_balance', $data['amount']);

        return response()->json(['message' => 'Repayment successful.', 'amount' => $data['amount']]);
    }
}
