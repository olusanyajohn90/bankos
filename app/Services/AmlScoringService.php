<?php

namespace App\Services;

use App\Models\AmlAlert;
use App\Models\AmlRule;
use App\Models\TransactionLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AmlScoringService
{
    /**
     * Score a transaction for AML risk.
     * Returns array: ['score' => int, 'alerts' => [...], 'blocked' => bool]
     */
    public static function scoreTransaction(
        string $tenantId,
        string $customerId,
        string $accountId,
        string $transactionType,
        float $amount,
        ?string $transactionId = null
    ): array {
        $score = 0;
        $flags = [];
        $blocked = false;

        // Load tenant-specific rules
        $rules = AmlRule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->keyBy('rule_type');

        // ── 1. LARGE AMOUNT ────────────────────────────────────────────────────
        $largeThreshold = optional($rules->get('amount_threshold'))->threshold_amount ?? 1_000_000;
        $veryLargeThreshold = 5_000_000;

        if ($amount >= $veryLargeThreshold) {
            $score += 70;
            $flags[] = [
                'rule'    => 'very_large_amount',
                'label'   => 'Very Large Amount',
                'score'   => 70,
                'detail'  => 'Transaction amount ₦' . number_format($amount, 2) . ' exceeds ₦5,000,000',
            ];
        } elseif ($amount >= $largeThreshold) {
            $score += 40;
            $flags[] = [
                'rule'    => 'large_amount',
                'label'   => 'Large Amount',
                'score'   => 40,
                'detail'  => 'Transaction amount ₦' . number_format($amount, 2) . ' exceeds ₦1,000,000',
            ];
        }

        // ── 2. ROUND AMOUNT ────────────────────────────────────────────────────
        if (fmod($amount, 100000) === 0.0 && $amount >= 100000) {
            $score += 10;
            $flags[] = [
                'rule'    => 'round_amount',
                'label'   => 'Round Amount',
                'score'   => 10,
                'detail'  => 'Transaction is an exact round amount: ₦' . number_format($amount, 2),
            ];
        }

        // ── 3. VELOCITY (hourly) ───────────────────────────────────────────────
        $velocityWindow = optional($rules->get('velocity'))->time_window_hours ?? 1;
        $velocityThreshold = optional($rules->get('velocity'))->threshold_count ?? 5;

        $recentCount = DB::table('transactions')
            ->where('account_id', $accountId)
            ->where('created_at', '>=', now()->subHours($velocityWindow))
            ->count();

        if ($recentCount >= $velocityThreshold) {
            $score += 30;
            $flags[] = [
                'rule'    => 'velocity',
                'label'   => 'High Velocity',
                'score'   => 30,
                'detail'  => "{$recentCount} transactions in last {$velocityWindow} hour(s) from this account",
            ];
        }

        // ── 4. DAILY VELOCITY ──────────────────────────────────────────────────
        $dailyCount = DB::table('transactions')
            ->where('account_id', $accountId)
            ->whereDate('created_at', today())
            ->count();

        if ($dailyCount >= 10) {
            $score += 20;
            $flags[] = [
                'rule'    => 'daily_velocity',
                'label'   => 'Daily Velocity',
                'score'   => 20,
                'detail'  => "{$dailyCount} transactions today from this account",
            ];
        }

        // ── 5. STRUCTURING ─────────────────────────────────────────────────────
        if ($amount >= 450_000 && $amount < 500_000) {
            $structuringCount = DB::table('transactions')
                ->where('account_id', $accountId)
                ->whereBetween('amount', [450000, 499999.99])
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            if ($structuringCount >= 2) {
                $score += 60;
                $flags[] = [
                    'rule'    => 'structuring',
                    'label'   => 'Structuring',
                    'score'   => 60,
                    'detail'  => "{$structuringCount} transactions just below ₦500,000 in the last 24 hours — possible structuring to avoid threshold reporting",
                ];
            }
        }

        // ── 6. DORMANCY REACTIVATION ───────────────────────────────────────────
        if ($amount >= $largeThreshold) {
            $lastTxn = DB::table('transactions')
                ->where('account_id', $accountId)
                ->orderByDesc('created_at')
                ->value('created_at');

            if ($lastTxn && now()->diffInDays($lastTxn) >= 180) {
                $score += 25;
                $flags[] = [
                    'rule'    => 'dormancy_reactivation',
                    'label'   => 'Dormancy Reactivation',
                    'score'   => 25,
                    'detail'  => 'First large transaction after ' . now()->diffInDays($lastTxn) . ' days of inactivity',
                ];
            }
        }

        // ── 7. RAPID MOVEMENT ──────────────────────────────────────────────────
        if (in_array($transactionType, ['transfer', 'nip_transfer'])) {
            $recentTransfers = DB::table('transactions')
                ->where('account_id', $accountId)
                ->whereIn('transaction_type', ['transfer', 'nip_transfer'])
                ->where('created_at', '>=', now()->subMinutes(30))
                ->select('beneficiary_account_number')
                ->distinct()
                ->count();

            if ($recentTransfers >= 3) {
                $score += 35;
                $flags[] = [
                    'rule'    => 'rapid_movement',
                    'label'   => 'Rapid Movement',
                    'score'   => 35,
                    'detail'  => "Transfers to {$recentTransfers} different accounts within 30 minutes",
                ];
            }
        }

        // ── DETERMINE SEVERITY & CREATE ALERT ─────────────────────────────────
        $alertsCreated = [];

        if ($score >= 20 && ! empty($flags)) {
            $severity = match (true) {
                $score >= 80 => 'critical',
                $score >= 60 => 'high',
                $score >= 40 => 'medium',
                default      => 'low',
            };

            // Check if auto_block applies
            foreach ($rules as $rule) {
                if ($rule->auto_block && $score >= 60) {
                    $blocked = true;
                    break;
                }
            }

            // Determine primary alert type from highest-scoring flag
            $primaryFlag = collect($flags)->sortByDesc('score')->first();
            $alertType = match ($primaryFlag['rule']) {
                'very_large_amount', 'large_amount' => 'large_amount',
                'structuring'                        => 'structuring',
                'velocity', 'daily_velocity'         => 'velocity',
                'round_amount'                       => 'round_amount',
                'dormancy_reactivation'              => 'unusual_pattern',
                'rapid_movement'                     => 'rapid_movement',
                default                              => 'unusual_pattern',
            };

            $alert = AmlAlert::create([
                'id'             => Str::uuid(),
                'tenant_id'      => $tenantId,
                'alert_type'     => $alertType,
                'severity'       => $severity,
                'status'         => 'open',
                'entity_type'    => 'transaction',
                'entity_id'      => $transactionId ?? $accountId,
                'customer_id'    => $customerId,
                'transaction_id' => $transactionId,
                'account_id'     => $accountId,
                'score'          => min($score, 100),
                'details'        => [
                    'flags'            => $flags,
                    'total_score'      => $score,
                    'amount'           => $amount,
                    'transaction_type' => $transactionType,
                ],
            ]);

            $alertsCreated[] = $alert->id;
        }

        return [
            'score'   => min($score, 100),
            'alerts'  => $alertsCreated,
            'blocked' => $blocked,
            'flags'   => $flags,
        ];
    }

    /**
     * Screen a name against the sanctions list.
     * Returns array: ['match' => bool, 'confidence' => int, 'matches' => [...]]
     */
    public static function screenSanctions(string $fullName, ?string $dateOfBirth = null): array
    {
        $normalized = self::normalizeName($fullName);
        $queryWords = array_filter(explode(' ', $normalized));
        $matches = [];

        $entries = DB::table('sanctions_list')->where('is_active', true)->get();

        foreach ($entries as $entry) {
            $confidence = 0;
            $matchReason = '';

            $entryNormalized = self::normalizeName($entry->full_name);

            // Exact match on full_name → 100
            if ($normalized === $entryNormalized) {
                $confidence = 100;
                $matchReason = 'Exact name match';
            } else {
                // Check aliases
                $aliases = json_decode($entry->aliases ?? '[]', true) ?? [];
                foreach ($aliases as $alias) {
                    $aliasNorm = self::normalizeName($alias);
                    if ($normalized === $aliasNorm) {
                        $confidence = 95;
                        $matchReason = "Exact alias match: {$alias}";
                        break;
                    }
                }

                // Levenshtein distance <= 2
                if ($confidence === 0) {
                    $lev = levenshtein($normalized, $entryNormalized);
                    if ($lev <= 2) {
                        $confidence = 85;
                        $matchReason = "Near-exact name match (distance: {$lev})";
                    }
                }

                // All query words appear in entry name
                if ($confidence === 0 && ! empty($queryWords)) {
                    $entryWords = array_filter(explode(' ', $entryNormalized));
                    $overlap = array_intersect($queryWords, $entryWords);
                    $overlapRatio = count($queryWords) > 0
                        ? count($overlap) / count($queryWords)
                        : 0;

                    if ($overlapRatio >= 1.0) {
                        $confidence = 75;
                        $matchReason = 'All query words found in sanctions name';
                    } elseif ($overlapRatio >= 0.7) {
                        $confidence = 70;
                        $matchReason = round($overlapRatio * 100) . '% word overlap with sanctions name';
                    }
                }
            }

            // DOB check boost
            if ($confidence >= 70 && $dateOfBirth && $entry->date_of_birth) {
                if ($dateOfBirth === $entry->date_of_birth) {
                    $confidence = min(100, $confidence + 5);
                    $matchReason .= ' + DOB match';
                }
            }

            if ($confidence >= 70) {
                $matches[] = [
                    'id'          => $entry->id,
                    'full_name'   => $entry->full_name,
                    'list_source' => $entry->list_source,
                    'entity_type' => $entry->entity_type,
                    'programs'    => json_decode($entry->programs ?? '[]', true),
                    'nationality' => $entry->nationality,
                    'confidence'  => $confidence,
                    'reason'      => $matchReason,
                ];
            }
        }

        // Sort by confidence descending
        usort($matches, fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $topConfidence = ! empty($matches) ? $matches[0]['confidence'] : 0;

        return [
            'match'      => ! empty($matches),
            'confidence' => $topConfidence,
            'matches'    => $matches,
        ];
    }

    /**
     * Check transaction limit for a customer tier.
     * Returns: ['allowed' => bool, 'reason' => string, 'limit' => float, 'used' => float]
     */
    public static function checkLimit(
        string $tenantId,
        string $customerId,
        string $transactionType,
        float $amount,
        string $channel = 'portal'
    ): array {
        // Determine KYC tier
        $customer = DB::table('customers')->where('id', $customerId)->first();
        $kycTier  = $customer ? ($customer->kyc_level ?? 'level_1') : 'level_1';

        // Map kyc_level integers or strings to tier enum
        if (is_numeric($kycTier)) {
            $kycTier = 'level_' . $kycTier;
        }
        if (! in_array($kycTier, ['level_1', 'level_2', 'level_3'])) {
            $kycTier = 'level_1';
        }

        // Fetch limit from DB — most specific first: channel+type, then channel+all, then all+type, then all+all
        $limit = TransactionLimit::where('tenant_id', $tenantId)
            ->where('kyc_tier', $kycTier)
            ->where(function ($q) use ($channel, $transactionType) {
                $q->where(function ($q2) use ($channel, $transactionType) {
                    $q2->where('channel', $channel)->where('transaction_type', $transactionType);
                })->orWhere(function ($q2) use ($channel) {
                    $q2->where('channel', $channel)->where('transaction_type', 'all');
                })->orWhere(function ($q2) use ($transactionType) {
                    $q2->where('channel', 'all')->where('transaction_type', $transactionType);
                })->orWhere(function ($q2) {
                    $q2->where('channel', 'all')->where('transaction_type', 'all');
                });
            })
            ->orderByRaw("CASE channel WHEN ? THEN 1 WHEN 'all' THEN 2 ELSE 3 END ASC", [$channel])
            ->orderByRaw("CASE transaction_type WHEN ? THEN 1 WHEN 'all' THEN 2 ELSE 3 END ASC", [$transactionType])
            ->first();

        // Default limits by tier
        $defaults = [
            'level_1' => ['single' => 50_000,    'daily' => 200_000],
            'level_2' => ['single' => 500_000,   'daily' => 1_000_000],
            'level_3' => ['single' => 5_000_000, 'daily' => 10_000_000],
        ];

        $singleLimit = $limit ? (float) $limit->single_limit : $defaults[$kycTier]['single'];
        $dailyLimit  = $limit ? (float) $limit->daily_limit  : $defaults[$kycTier]['daily'];

        // Check single transaction limit
        if ($amount > $singleLimit) {
            return [
                'allowed' => false,
                'reason'  => "Transaction amount ₦" . number_format($amount, 2) . " exceeds your KYC tier single transaction limit of ₦" . number_format($singleLimit, 2),
                'limit'   => $singleLimit,
                'used'    => $amount,
            ];
        }

        // Check daily cumulative limit — sum today's transactions for this customer
        $todayTotal = DB::table('transactions')
            ->where('customer_id', $customerId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['completed', 'pending'])
            ->sum('amount');

        $projectedTotal = (float) $todayTotal + $amount;

        if ($projectedTotal > $dailyLimit) {
            return [
                'allowed' => false,
                'reason'  => "Daily limit of ₦" . number_format($dailyLimit, 2) . " would be exceeded (used: ₦" . number_format($todayTotal, 2) . ")",
                'limit'   => $dailyLimit,
                'used'    => (float) $todayTotal,
            ];
        }

        return [
            'allowed' => true,
            'reason'  => 'Within limit',
            'limit'   => $dailyLimit,
            'used'    => (float) $todayTotal,
        ];
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private static function normalizeName(string $name): string
    {
        // Uppercase, remove special chars except spaces, collapse whitespace
        $name = strtoupper($name);
        $name = preg_replace('/[^A-Z0-9 ]/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        return $name;
    }
}
