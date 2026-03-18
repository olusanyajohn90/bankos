<?php

namespace App\Services\StandingOrder;

use App\Models\StandingOrder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class StandingOrderService
{
    public function process(StandingOrder $so): bool
    {
        return DB::transaction(function () use ($so) {
            $account = Account::findOrFail($so->source_account_id);

            if ($account->available_balance < $so->amount) {
                $so->update([
                    'status'               => 'failed',
                    'last_failure_reason'  => 'Insufficient balance on ' . now()->toDateString(),
                ]);
                return false;
            }

            $account->decrement('available_balance', $so->amount);
            $account->decrement('ledger_balance', $so->amount);

            if ($so->transfer_type === 'internal' && $so->internal_dest_account_id) {
                $dest = Account::find($so->internal_dest_account_id);
                $dest?->increment('available_balance', $so->amount);
                $dest?->increment('ledger_balance', $so->amount);
            }

            $nextRun      = $so->computeNextRunDate();
            $runsCompleted = $so->runs_completed + 1;
            $isDone = ($so->max_runs && $runsCompleted >= $so->max_runs)
                   || ($so->end_date && $nextRun->gt($so->end_date));

            $so->update([
                'runs_completed' => $runsCompleted,
                'last_run_at'    => now(),
                'next_run_date'  => $nextRun,
                'status'         => $isDone ? 'completed' : 'active',
            ]);

            return true;
        });
    }

    public function processDue(string $tenantId): array
    {
        $results = ['processed' => 0, 'failed' => 0];

        StandingOrder::where('tenant_id', $tenantId)->due()->each(function ($so) use (&$results) {
            $this->process($so) ? $results['processed']++ : $results['failed']++;
        });

        return $results;
    }
}
