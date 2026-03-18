<?php
namespace App\Services;
use App\Models\{Account, Transaction};
use Illuminate\Support\Facades\DB;

class AccountLifecycleService {
    public function checkDormancy(string $tenantId, int $inactiveMonths = 6): int {
        $cutoff = now()->subMonths($inactiveMonths);
        $count = 0;
        Account::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed','dormant'])
            ->whereNull('dormant_since')
            ->each(function (Account $account) use ($cutoff, &$count) {
                $hasRecentTx = Transaction::where('account_id', $account->id)
                    ->where('created_at', '>=', $cutoff)->exists();
                if (!$hasRecentTx) {
                    $account->update(['status'=>'dormant','dormant_since'=>now()->toDateString()]);
                    $count++;
                }
            });
        return $count;
    }

    public function reactivate(Account $account): void {
        if ($account->status === 'dormant') {
            $account->update(['status'=>'active','dormant_since'=>null]);
        }
    }

    public function close(Account $account, string $reason, $closedBy): Account {
        if ($account->ledger_balance != 0) {
            throw new \RuntimeException('Account must have zero balance before closure. Current balance: ' . number_format($account->ledger_balance, 2));
        }
        if ($account->pnd_active || $account->activeLiens()->exists()) {
            throw new \RuntimeException('Account has active PND or lien restrictions. Remove before closing.');
        }
        $account->update([
            'status'         => 'closed',
            'closed_at'      => now()->toDateString(),
            'closure_reason' => $reason,
            'closed_by'      => $closedBy,
        ]);
        return $account->fresh();
    }
}
