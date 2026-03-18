<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UssdController extends Controller
{
    // Africa's Talking USSD session text responses
    private const CON = 'CON '; // continue — show menu
    private const END = 'END '; // end session

    public function handle(Request $request)
    {
        $sessionId   = $request->input('sessionId');
        $serviceCode = $request->input('serviceCode');
        $phoneNumber = $request->input('phoneNumber');
        $text        = $request->input('text', '');

        $parts = $text === '' ? [] : explode('*', $text);

        $response = $this->route($parts, $phoneNumber);

        return response($response, 200)->header('Content-Type', 'text/plain');
    }

    private function route(array $parts, string $phone): string
    {
        $level = count($parts);

        if ($level === 0) {
            return self::CON . "BankOS\n1. Account Balance\n2. Mini Statement\n3. Loan Balance\n4. Exit";
        }

        return match ($parts[0]) {
            '1' => $this->accountBalance($parts, $phone),
            '2' => $this->miniStatement($parts, $phone),
            '3' => $this->loanBalance($parts, $phone),
            '4' => self::END . "Thank you for using BankOS.",
            default => self::END . "Invalid option.",
        };
    }

    private function accountBalance(array $parts, string $phone): string
    {
        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            return self::END . "Phone number not registered.";
        }

        $account = Account::where('customer_id', $customer->id)
            ->where('type', 'savings')
            ->first();

        if (!$account) {
            return self::END . "No savings account found.";
        }

        return self::END . "Account: {$account->account_number}\nBalance: NGN " . number_format($account->available_balance, 2);
    }

    private function miniStatement(array $parts, string $phone): string
    {
        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            return self::END . "Phone number not registered.";
        }

        $account = Account::where('customer_id', $customer->id)->first();
        if (!$account) {
            return self::END . "No account found.";
        }

        $txns = \App\Models\Transaction::where('account_id', $account->id)
            ->latest()
            ->limit(3)
            ->get();

        if ($txns->isEmpty()) {
            return self::END . "No recent transactions.";
        }

        $lines = $txns->map(fn($t) => date('d/m', strtotime($t->transaction_date)) . ' ' . strtoupper($t->type[0]) . ' NGN' . number_format($t->amount, 0))->implode("\n");

        return self::END . "Last 3 transactions:\n{$lines}";
    }

    private function loanBalance(array $parts, string $phone): string
    {
        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) {
            return self::END . "Phone number not registered.";
        }

        $loan = Loan::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->first();

        if (!$loan) {
            return self::END . "No active loan found.";
        }

        return self::END . "Loan: {$loan->loan_account_number}\nOutstanding: NGN " . number_format($loan->outstanding_balance, 2) . "\nNext due: " . \Carbon\Carbon::parse($loan->next_due_date)->format('d M Y');
    }
}
