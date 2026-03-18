<?php

namespace App\Services\FixedDeposit;

use App\Models\FixedDeposit;
use App\Models\FixedDepositProduct;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixedDepositService
{
    public function create(array $data, $tenantId): FixedDeposit
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $product    = FixedDepositProduct::findOrFail($data['product_id']);
            $startDate  = Carbon::parse($data['start_date'] ?? now());
            $maturityDate = $startDate->copy()->addDays($data['tenure_days']);
            $expectedInterest = $data['principal_amount'] * ($data['interest_rate'] / 100 / 365) * $data['tenure_days'];

            $fd = FixedDeposit::create([
                'tenant_id'         => $tenantId,
                'product_id'        => $data['product_id'],
                'customer_id'       => $data['customer_id'],
                'source_account_id' => $data['source_account_id'],
                'fd_number'         => 'FD-' . strtoupper(substr(uniqid(), -8)),
                'principal_amount'  => $data['principal_amount'],
                'interest_rate'     => $data['interest_rate'],
                'tenure_days'       => $data['tenure_days'],
                'start_date'        => $startDate,
                'maturity_date'     => $maturityDate,
                'expected_interest' => round($expectedInterest, 2),
                'status'            => 'active',
                'auto_rollover'     => $data['auto_rollover'] ?? $product->auto_rollover,
                'created_by'        => auth()->id(),
                'branch_id'         => $data['branch_id'] ?? null,
            ]);

            // Debit source account
            $account = Account::findOrFail($data['source_account_id']);
            $account->decrement('available_balance', $data['principal_amount']);
            $account->decrement('ledger_balance', $data['principal_amount']);

            return $fd;
        });
    }

    public function liquidate(FixedDeposit $fd, string $reason = ''): FixedDeposit
    {
        return DB::transaction(function () use ($fd, $reason) {
            $isEarly    = now()->startOfDay()->lt($fd->maturity_date);
            $penaltyRate = $isEarly ? ($fd->product->early_liquidation_penalty / 100) : 0;
            $grossInterest = $fd->accrued_interest;
            $penalty    = round($grossInterest * $penaltyRate, 2);
            $netInterest = $grossInterest - $penalty;
            $payout     = $fd->principal_amount + $netInterest;

            $account = Account::findOrFail($fd->source_account_id);
            $account->increment('available_balance', $payout);
            $account->increment('ledger_balance', $payout);

            $fd->update([
                'status'             => 'liquidated',
                'liquidated_at'      => now(),
                'liquidation_amount' => $payout,
                'penalty_amount'     => $penalty,
                'liquidation_reason' => $reason,
                'paid_interest'      => $netInterest,
            ]);

            return $fd->fresh();
        });
    }

    public function accrueInterest(FixedDeposit $fd): void
    {
        if ($fd->status !== 'active') {
            return;
        }
        $dailyRate     = $fd->interest_rate / 100 / 365;
        $dailyInterest = round($fd->principal_amount * $dailyRate, 2);
        $fd->increment('accrued_interest', $dailyInterest);
    }

    public function processMaturities($tenantId): int
    {
        $count = 0;
        $due   = FixedDeposit::where('tenant_id', $tenantId)->maturingBefore(now())->get();
        foreach ($due as $fd) {
            if ($fd->auto_rollover) {
                $this->rollover($fd);
            } else {
                $fd->update(['status' => 'matured']);
            }
            $count++;
        }
        return $count;
    }

    public function rollover(FixedDeposit $fd): FixedDeposit
    {
        return DB::transaction(function () use ($fd) {
            $this->liquidate($fd, 'Auto-rollover');

            $newFd = $this->create([
                'product_id'        => $fd->product_id,
                'customer_id'       => $fd->customer_id,
                'source_account_id' => $fd->source_account_id,
                'principal_amount'  => $fd->principal_amount,
                'interest_rate'     => $fd->interest_rate,
                'tenure_days'       => $fd->tenure_days,
                'auto_rollover'     => true,
                'branch_id'         => $fd->branch_id,
            ], $fd->tenant_id);

            $fd->update(['status' => 'rolled_over']);

            return $newFd;
        });
    }
}
