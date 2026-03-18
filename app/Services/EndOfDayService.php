<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EndOfDayService
{
    /**
     * Run the End of Day processing for all eligible accounts.
     * 
     * @return array Summary of processed accounts and posted interest.
     */
    public function processSavingsInterest($date = null)
    {
        $processDate = $date ? Carbon::parse($date) : now();
        $summary = [
            'accounts_processed' => 0,
            'total_interest_accrued' => 0,
            'total_interest_posted' => 0,
            'errors' => 0,
        ];

        // 1. Get all active Savings Accounts with their associated Products
        $accounts = Account::where('status', 'active')
            ->where('type', 'savings')
            ->whereNotNull('savings_product_id')
            ->with('savingsProduct')
            ->get();

        foreach ($accounts as $account) {
            try {
                $product = $account->savingsProduct;
                if (!$product) continue;

                // 2. Calculate daily interest
                // Formula: (Current Balance * Annual Interest Rate) / 365
                $dailyRate = ($product->interest_rate / 100) / 365;
                $dailyInterest = $account->current_balance * $dailyRate;

                if ($dailyInterest <= 0) continue;

                DB::beginTransaction();

                // 3. Post the interest transaction
                $reference = 'INT-' . strtoupper(Str::random(10));
                
                $transaction = Transaction::create([
                    'tenant_id' => $account->tenant_id,
                    'account_id' => $account->id,
                    'reference' => $reference,
                    // Typically EOD interest is categorized specifically, e.g., 'interest_credit'
                    'type' => 'interest_credit', 
                    'amount' => $dailyInterest,
                    'currency' => $account->currency,
                    'description' => "Daily Interest Accrual for " . $processDate->format('Y-m-d'),
                    'status' => 'success',
                    'created_at' => $processDate, // Set the date to the EOD date
                    'updated_at' => $processDate,
                ]);

                // 4. Update the account balances
                $account->increment('available_balance', $dailyInterest);
                $account->increment('ledger_balance', $dailyInterest);

                DB::commit();

                $summary['accounts_processed']++;
                $summary['total_interest_accrued'] += $dailyInterest;
                $summary['total_interest_posted'] += $dailyInterest;

                Log::info("EOD Interest Posted: Account {$account->account_number}, Amount: {$dailyInterest}");

            } catch (\Exception $e) {
                DB::rollBack();
                $summary['errors']++;
                Log::error("EOD Error for Account {$account->account_number}: " . $e->getMessage());
            }
        }

        return $summary;
    }
}
