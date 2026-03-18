<?php

namespace App\Services;

use App\Models\FeeRule;
use Illuminate\Support\Collection;

class FeeEngineService
{
    /**
     * Calculate the total fee for a transaction.
     * Returns fee amount (0.00 if no rule matches).
     */
    public static function calculate(
        string $tenantId,
        string $transactionType,
        float $amount,
        ?string $accountType = null
    ): float {
        $rules = static::getMatchingRules($tenantId, $transactionType, $amount, $accountType);

        if ($rules->isEmpty()) {
            return 0.00;
        }

        $totalFee = 0.0;

        foreach ($rules as $rule) {
            if ($rule->fee_type === 'flat') {
                $totalFee += $rule->amount;
            } else {
                // percentage
                $fee = $amount * ($rule->amount / 100);

                // clamp to min_fee floor
                if ($rule->min_fee !== null && $fee < $rule->min_fee) {
                    $fee = $rule->min_fee;
                }

                // clamp to max_fee cap
                if ($rule->max_fee !== null && $fee > $rule->max_fee) {
                    $fee = $rule->max_fee;
                }

                $totalFee += $fee;
            }
        }

        return round($totalFee, 2);
    }

    /**
     * Get all matching active rules for a given transaction.
     * Returns Collection of FeeRule objects.
     */
    public static function getRules(
        string $tenantId,
        string $transactionType,
        ?string $accountType = null
    ): Collection {
        $query = FeeRule::where('tenant_id', $tenantId)
            ->where('transaction_type', $transactionType)
            ->where('is_active', true);

        // account_type filter: rule's account_type must be null (all types) OR match the given accountType
        if ($accountType !== null) {
            $query->where(function ($q) use ($accountType) {
                $q->whereNull('account_type')
                  ->orWhere('account_type', $accountType);
            });
        }

        return $query->get();
    }

    /**
     * Get all matching active rules filtered also by transaction amount.
     * Used internally by calculate().
     */
    protected static function getMatchingRules(
        string $tenantId,
        string $transactionType,
        float $amount,
        ?string $accountType = null
    ): Collection {
        $rules = static::getRules($tenantId, $transactionType, $accountType);

        return $rules->filter(function (FeeRule $rule) use ($amount) {
            if ($rule->min_transaction_amount !== null && $amount < $rule->min_transaction_amount) {
                return false;
            }
            if ($rule->max_transaction_amount !== null && $amount > $rule->max_transaction_amount) {
                return false;
            }
            return true;
        });
    }
}
