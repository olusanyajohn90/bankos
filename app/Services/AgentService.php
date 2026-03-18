<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentFloatTransaction;
use Illuminate\Support\Str;

class AgentService
{
    public function fundFloat(Agent $agent, float $amount, string $narration = 'Float top-up'): AgentFloatTransaction
    {
        $newBalance = $agent->float_balance + $amount;

        $tx = AgentFloatTransaction::create([
            'tenant_id'      => $agent->tenant_id,
            'agent_id'       => $agent->id,
            'type'           => 'fund',
            'amount'         => $amount,
            'balance_after'  => $newBalance,
            'reference'      => 'AFT-' . strtoupper(Str::random(10)),
            'narration'      => $narration,
        ]);

        $agent->update(['float_balance' => $newBalance]);

        return $tx;
    }

    public function debitFloat(Agent $agent, float $amount, string $narration = 'Float debit', ?string $transactionId = null): AgentFloatTransaction
    {
        $newBalance = $agent->float_balance - $amount;

        $tx = AgentFloatTransaction::create([
            'tenant_id'      => $agent->tenant_id,
            'agent_id'       => $agent->id,
            'type'           => 'debit',
            'amount'         => $amount,
            'balance_after'  => $newBalance,
            'reference'      => 'AFT-' . strtoupper(Str::random(10)),
            'narration'      => $narration,
            'transaction_id' => $transactionId,
        ]);

        $agent->update(['float_balance' => $newBalance]);

        return $tx;
    }

    public function creditCommission(Agent $agent, float $amount, ?string $transactionId = null): AgentFloatTransaction
    {
        $commission = $amount * $agent->commission_rate;
        $newBalance = $agent->float_balance + $commission;

        $tx = AgentFloatTransaction::create([
            'tenant_id'      => $agent->tenant_id,
            'agent_id'       => $agent->id,
            'type'           => 'commission',
            'amount'         => $commission,
            'balance_after'  => $newBalance,
            'reference'      => 'AFT-' . strtoupper(Str::random(10)),
            'narration'      => 'Commission on transaction',
            'transaction_id' => $transactionId,
        ]);

        $agent->update([
            'float_balance'           => $newBalance,
            'total_commission_earned' => $agent->total_commission_earned + $commission,
        ]);

        return $tx;
    }
}
