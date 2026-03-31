<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CortexService
{
    private AiReviewService $aiReview;
    private string $apiKey;
    private string $model;

    public function __construct(AiReviewService $aiReview)
    {
        $this->aiReview = $aiReview;
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
    }

    /**
     * Score a loan application — AI-enhanced credit decision.
     */
    public function scoreLoan(Loan $loan): array
    {
        $loan->load(['customer.accounts', 'customer.loans', 'loanProduct']);
        $customer = $loan->customer;
        $ctx = $this->aiReview->buildCustomerContext($customer);

        $loanContext = [
            'principal_amount' => $loan->principal_amount,
            'interest_rate' => $loan->interest_rate,
            'tenure_days' => $loan->tenure_days,
            'product' => $loan->loanProduct?->name ?? 'Unknown',
        ];

        // Rule-based scoring
        $score = 50;
        $reasons = [];

        // KYC check
        if ($ctx['kyc_status'] === 'approved') {
            $score += 10;
            $reasons[] = ['type' => 'positive', 'text' => 'KYC fully verified'];
        } else {
            $score -= 20;
            $reasons[] = ['type' => 'negative', 'text' => "KYC status: {$ctx['kyc_status']}"];
        }

        // Repayment history
        if ($ctx['closed_loans_count'] > 0 && $ctx['overdue_loans_count'] === 0) {
            $score += 15;
            $reasons[] = ['type' => 'positive', 'text' => "{$ctx['closed_loans_count']} loans repaid successfully"];
        }
        if ($ctx['overdue_loans_count'] > 0) {
            $score -= 25;
            $reasons[] = ['type' => 'negative', 'text' => "{$ctx['overdue_loans_count']} overdue loan(s) currently active"];
        }

        // Balance vs loan amount
        if ($ctx['total_balance'] >= $loan->principal_amount * 0.3) {
            $score += 10;
            $reasons[] = ['type' => 'positive', 'text' => 'Balance covers 30%+ of loan principal'];
        } elseif ($ctx['total_balance'] < $loan->principal_amount * 0.1) {
            $score -= 10;
            $reasons[] = ['type' => 'negative', 'text' => 'Very low balance relative to loan request'];
        }

        // Income check
        if ($ctx['monthly_income_estimate'] > 0) {
            $monthlyRepayment = $loan->principal_amount / max($loan->tenure_days / 30, 1);
            $dti = $monthlyRepayment / $ctx['monthly_income_estimate'];
            if ($dti < 0.3) {
                $score += 10;
                $reasons[] = ['type' => 'positive', 'text' => 'Debt-to-income ratio healthy at ' . round($dti * 100, 1) . '%'];
            } elseif ($dti > 0.5) {
                $score -= 15;
                $reasons[] = ['type' => 'negative', 'text' => 'Debt-to-income ratio high at ' . round($dti * 100, 1) . '%'];
            }
        }

        // Transaction activity
        if ($ctx['transaction_count_90d'] > 20) {
            $score += 5;
            $reasons[] = ['type' => 'positive', 'text' => 'Active transaction history (' . $ctx['transaction_count_90d'] . ' in 90 days)'];
        } elseif ($ctx['transaction_count_90d'] === 0) {
            $score -= 10;
            $reasons[] = ['type' => 'negative', 'text' => 'No recent transaction activity'];
        }

        // Customer tenure
        if ($ctx['customer_since']) {
            $months = Carbon::parse($ctx['customer_since'])->diffInMonths(now());
            if ($months >= 12) {
                $score += 5;
                $reasons[] = ['type' => 'positive', 'text' => "Long-standing customer ({$months} months)"];
            }
        }

        // Insurance bonus
        if ($ctx['insurance_policies'] > 0) {
            $score += 5;
            $reasons[] = ['type' => 'positive', 'text' => 'Has active insurance coverage'];
        }

        $score = max(0, min(100, $score));

        $recommendation = match (true) {
            $score >= 75 => 'approve',
            $score >= 50 => 'review',
            default => 'decline',
        };

        $suggestedTerms = [];
        if ($recommendation === 'review') {
            $suggestedTerms = [
                'reduce_principal' => round($loan->principal_amount * 0.75, 2),
                'require_guarantor' => true,
                'require_collateral' => $loan->principal_amount > 500000,
            ];
        }

        // If API key available, enhance with AI
        if (!empty($this->apiKey)) {
            $cacheKey = "cortex_loan_score_{$loan->id}_" . md5(json_encode($ctx));
            $aiInsight = Cache::remember($cacheKey, 3600, function () use ($ctx, $loanContext, $customer) {
                return $this->callCortexApi(
                    "You are a credit risk analyst for a Nigerian microfinance bank. Provide a brief (3-4 sentences) credit assessment for this loan application. Be specific with numbers. End with a clear recommendation.",
                    "Customer profile:\n" . json_encode($ctx, JSON_PRETTY_PRINT) . "\n\nLoan application:\n" . json_encode($loanContext, JSON_PRETTY_PRINT),
                    $customer
                );
            });
        }

        return [
            'score' => $score,
            'recommendation' => $recommendation,
            'reasons' => $reasons,
            'suggested_terms' => $suggestedTerms,
            'ai_insight' => $aiInsight ?? null,
        ];
    }

    /**
     * Detect potential fraud patterns for a customer.
     */
    public function detectFraud(Customer $customer): array
    {
        $customer->load(['accounts', 'loans']);
        $accounts = $customer->accounts;

        $recentTransactions = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('created_at', '>=', now()->subDays(90))
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->get();

        $alerts = [];
        $suspicious = [];

        // Check for unusually large transactions
        if ($recentTransactions->count() > 5) {
            $avg = $recentTransactions->avg('amount');
            $stdDev = $this->standardDeviation($recentTransactions->pluck('amount')->toArray());

            foreach ($recentTransactions as $txn) {
                if (abs($txn->amount) > abs($avg) + (3 * $stdDev) && $stdDev > 0) {
                    $suspicious[] = [
                        'id' => $txn->id,
                        'amount' => $txn->amount,
                        'date' => $txn->created_at->format('Y-m-d H:i'),
                        'reason' => 'Amount exceeds 3 standard deviations from mean',
                    ];
                }
            }
        }

        // Frequency spike detection
        $dailyCounts = $recentTransactions->groupBy(fn($t) => $t->created_at->format('Y-m-d'))->map->count();
        $avgDaily = $dailyCounts->avg() ?: 0;
        foreach ($dailyCounts as $date => $count) {
            if ($count > $avgDaily * 3 && $avgDaily > 1) {
                $alerts[] = [
                    'type' => 'frequency_spike',
                    'severity' => 'medium',
                    'description' => "Unusual activity on {$date}: {$count} transactions vs avg {$avgDaily}/day",
                ];
            }
        }

        // Round-amount structuring detection (potential structuring to avoid CTR)
        $roundAmounts = $recentTransactions->filter(fn($t) => $t->amount > 0 && fmod($t->amount, 100000) === 0.0 && $t->amount >= 900000);
        if ($roundAmounts->count() >= 3) {
            $alerts[] = [
                'type' => 'structuring',
                'severity' => 'high',
                'description' => "{$roundAmounts->count()} round-amount deposits near ₦1M threshold detected — potential structuring",
            ];
        }

        // Rapid succession transactions
        $times = $recentTransactions->pluck('created_at')->sort()->values();
        for ($i = 1; $i < min($times->count(), 50); $i++) {
            if ($times[$i]->diffInMinutes($times[$i - 1]) < 2) {
                $alerts[] = [
                    'type' => 'rapid_succession',
                    'severity' => 'low',
                    'description' => "Rapid transactions detected at " . $times[$i]->format('Y-m-d H:i'),
                ];
                break; // Report once
            }
        }

        // KYC mismatch
        if ($customer->kyc_status !== 'approved' && $recentTransactions->where('amount', '>', 100000)->count() > 0) {
            $alerts[] = [
                'type' => 'kyc_mismatch',
                'severity' => 'high',
                'description' => 'High-value transactions on non-verified account',
            ];
        }

        // Determine overall risk
        $riskLevel = 'low';
        if (collect($alerts)->where('severity', 'high')->count() > 0) {
            $riskLevel = 'high';
        } elseif (collect($alerts)->where('severity', 'medium')->count() > 0 || count($suspicious) > 0) {
            $riskLevel = 'medium';
        }

        return [
            'risk_level' => $riskLevel,
            'alerts' => $alerts,
            'suspicious_transactions' => $suspicious,
            'transactions_analyzed' => $recentTransactions->count(),
            'analysis_period' => '90 days',
        ];
    }

    /**
     * Predict customer churn probability.
     */
    public function predictChurn(Customer $customer): array
    {
        $customer->load(['accounts', 'loans']);
        $accounts = $customer->accounts;

        // Transaction trend analysis — last 90 days vs prior 90 days
        $recentCount = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('created_at', '>=', now()->subDays(90))
            ->where('status', 'success')
            ->count();

        $priorCount = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('created_at', '>=', now()->subDays(180))
            ->where('created_at', '<', now()->subDays(90))
            ->where('status', 'success')
            ->count();

        // Balance trend
        $currentBalance = $accounts->sum('available_balance');

        // Scoring factors
        $churnScore = 0;
        $riskFactors = [];
        $retentionActions = [];

        // Activity decline
        if ($recentCount === 0) {
            $churnScore += 0.35;
            $riskFactors[] = 'No transactions in last 90 days';
            $retentionActions[] = 'Send personalized reactivation campaign with incentive';
        } elseif ($priorCount > 0 && $recentCount < $priorCount * 0.5) {
            $churnScore += 0.2;
            $riskFactors[] = 'Transaction frequency declined by ' . round((1 - $recentCount / $priorCount) * 100) . '%';
            $retentionActions[] = 'Proactive outreach — schedule relationship manager call';
        }

        // Low balance
        if ($currentBalance < 1000 && $accounts->count() > 0) {
            $churnScore += 0.2;
            $riskFactors[] = 'Very low account balance (under ₦1,000)';
            $retentionActions[] = 'Offer fee waiver or minimum balance reduction';
        }

        // No active products
        $activeLoans = $customer->loans->where('status', 'active')->count();
        if ($activeLoans === 0 && $accounts->count() <= 1) {
            $churnScore += 0.15;
            $riskFactors[] = 'Minimal product engagement (single account, no loans)';
            $retentionActions[] = 'Cross-sell targeted product — savings goal or micro-loan';
        }

        // Overdue loans (dissatisfaction indicator)
        $overdueLoans = $customer->loans->where('status', 'overdue')->count();
        if ($overdueLoans > 0) {
            $churnScore += 0.1;
            $riskFactors[] = 'Active loan delinquency may indicate distress';
            $retentionActions[] = 'Offer loan restructuring and financial counseling';
        }

        // Customer tenure (new customers churn more)
        if ($customer->created_at && $customer->created_at->diffInMonths(now()) < 3) {
            $churnScore += 0.1;
            $riskFactors[] = 'New customer (under 3 months) — higher attrition window';
            $retentionActions[] = 'Activate onboarding nurture sequence';
        }

        // KYC incomplete
        if ($customer->kyc_status !== 'approved') {
            $churnScore += 0.1;
            $riskFactors[] = 'Incomplete KYC limiting available services';
            $retentionActions[] = 'Assist with KYC completion — send branch invite or agent visit';
        }

        $churnScore = min(1.0, $churnScore);

        if (empty($retentionActions)) {
            $retentionActions[] = 'Continue regular engagement — no immediate churn risk detected';
        }

        return [
            'churn_probability' => round($churnScore, 2),
            'risk_level' => $churnScore >= 0.6 ? 'high' : ($churnScore >= 0.3 ? 'medium' : 'low'),
            'risk_factors' => $riskFactors,
            'retention_actions' => $retentionActions,
            'activity_trend' => [
                'recent_90d' => $recentCount,
                'prior_90d' => $priorCount,
                'change_pct' => $priorCount > 0 ? round(($recentCount - $priorCount) / $priorCount * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Calculate Customer Lifetime Value (CLV).
     */
    public function calculateCLV(Customer $customer): array
    {
        $customer->load(['accounts', 'loans']);

        $totalBalance = $customer->accounts->sum('available_balance');
        $totalInterestPaid = $customer->loans->sum(fn($l) => $l->amount_paid > $l->principal_amount ? $l->amount_paid - $l->principal_amount : 0);
        $loanRevenue = $totalInterestPaid;
        $depositValue = $totalBalance * 0.03; // estimated annual deposit margin
        $feeIncome = Transaction::whereIn('account_id', $customer->accounts->pluck('id'))
            ->where('type', 'fee')
            ->where('status', 'success')
            ->sum('amount');

        $currentValue = round(abs($loanRevenue) + abs($depositValue) + abs($feeIncome), 2);

        // Growth projection
        $monthsAsCustomer = $customer->created_at ? max($customer->created_at->diffInMonths(now()), 1) : 1;
        $monthlyValue = $currentValue / $monthsAsCustomer;
        $projectedValue12m = round($monthlyValue * 12 * 1.1, 2); // 10% growth factor

        $segment = match (true) {
            $currentValue >= 100000 => 'high',
            $currentValue >= 20000 => 'medium',
            default => 'low',
        };

        $drivers = [];
        if ($loanRevenue > 0) $drivers[] = "Loan interest income: ₦" . number_format($loanRevenue, 2);
        if ($depositValue > 0) $drivers[] = "Deposit margin value: ₦" . number_format($depositValue, 2);
        if (abs($feeIncome) > 0) $drivers[] = "Fee income: ₦" . number_format(abs($feeIncome), 2);
        if (empty($drivers)) $drivers[] = "New customer — value building";

        return [
            'current_value' => $currentValue,
            'projected_value_12m' => $projectedValue12m,
            'segment' => $segment,
            'drivers' => $drivers,
            'monthly_revenue' => round($monthlyValue, 2),
        ];
    }

    /**
     * Portfolio risk summary for the dashboard.
     */
    public function portfolioRiskSummary(string $tenantId): array
    {
        $customers = Customer::where('tenant_id', $tenantId)->with(['accounts', 'loans'])->get();

        $riskDistribution = ['low' => 0, 'medium' => 0, 'high' => 0];
        $watchlist = [];
        $totalAnalyzed = 0;

        foreach ($customers as $customer) {
            $totalAnalyzed++;
            $activeLoans = $customer->loans->where('status', 'active');
            $overdueLoans = $customer->loans->where('status', 'overdue');
            $balance = $customer->accounts->sum('available_balance');

            $risk = 'low';
            $riskReasons = [];

            if ($overdueLoans->count() > 0) {
                $risk = 'high';
                $riskReasons[] = "{$overdueLoans->count()} overdue loan(s)";
            }
            if ($customer->kyc_status !== 'approved' && $activeLoans->count() > 0) {
                $risk = $risk === 'high' ? 'high' : 'medium';
                $riskReasons[] = 'KYC incomplete with active loans';
            }
            if ($activeLoans->sum('outstanding_balance') > $balance * 3 && $activeLoans->count() > 0) {
                $risk = $risk === 'low' ? 'medium' : $risk;
                $riskReasons[] = 'High leverage ratio';
            }

            $riskDistribution[$risk]++;

            if ($risk === 'high') {
                $watchlist[] = [
                    'id' => $customer->id,
                    'name' => $customer->first_name . ' ' . $customer->last_name,
                    'risk_level' => $risk,
                    'reasons' => $riskReasons,
                    'outstanding' => $activeLoans->sum('outstanding_balance') + $overdueLoans->sum('outstanding_balance'),
                    'balance' => $balance,
                ];
            }
        }

        // Sort watchlist by outstanding amount desc
        usort($watchlist, fn($a, $b) => $b['outstanding'] <=> $a['outstanding']);

        // Portfolio health score (0-100)
        $healthScore = $totalAnalyzed > 0
            ? round(($riskDistribution['low'] * 100 + $riskDistribution['medium'] * 50) / $totalAnalyzed)
            : 100;

        // Concentration risks
        $concentrationRisks = [];
        $totalOutstanding = Loan::where('tenant_id', $tenantId)->whereIn('status', ['active', 'overdue'])->sum('outstanding_balance');
        if ($totalOutstanding > 0 && !empty($watchlist)) {
            $topBorrowerOutstanding = $watchlist[0]['outstanding'] ?? 0;
            $concentration = round($topBorrowerOutstanding / $totalOutstanding * 100, 1);
            if ($concentration > 20) {
                $concentrationRisks[] = "Top borrower represents {$concentration}% of total portfolio";
            }
        }

        return [
            'total_customers' => $totalAnalyzed,
            'risk_distribution' => $riskDistribution,
            'health_score' => $healthScore,
            'concentration_risks' => $concentrationRisks,
            'watchlist' => array_slice($watchlist, 0, 10),
        ];
    }

    /**
     * Smart product recommendations for a customer.
     */
    public function recommendProducts(Customer $customer): array
    {
        $ctx = $this->aiReview->buildCustomerContext($customer);
        $recommendations = [];

        if ($ctx['total_balance'] > 100000 && $ctx['insurance_policies'] === 0) {
            $recommendations[] = [
                'product' => 'Credit Life Insurance',
                'reason' => 'High balance depositor without insurance coverage',
                'confidence' => 0.85,
                'estimated_value' => round($ctx['total_balance'] * 0.005, 2),
            ];
        }

        if ($ctx['closed_loans_count'] >= 1 && $ctx['overdue_loans_count'] === 0 && $ctx['active_loans_count'] === 0) {
            $recommendations[] = [
                'product' => 'Premium Loan',
                'reason' => 'Proven repayment history with no current loans',
                'confidence' => 0.8,
                'estimated_value' => round($ctx['total_balance'] * 2, 2),
            ];
        }

        if ($ctx['total_balance'] > 500000) {
            $recommendations[] = [
                'product' => 'Fixed Deposit',
                'reason' => 'High idle liquidity — earn better returns on term deposit',
                'confidence' => 0.9,
                'estimated_value' => round($ctx['total_balance'] * 0.5, 2),
            ];
        }

        if ($ctx['account_count'] === 1 && $ctx['total_balance'] > 20000) {
            $recommendations[] = [
                'product' => 'Target Savings Account',
                'reason' => 'Single account holder — diversify with goal-based savings',
                'confidence' => 0.7,
                'estimated_value' => round($ctx['total_balance'] * 0.3, 2),
            ];
        }

        if ($ctx['monthly_income_estimate'] > 50000 && $ctx['active_loans_count'] === 0) {
            $recommendations[] = [
                'product' => 'Salary Advance',
                'reason' => 'Steady income earner without active credit',
                'confidence' => 0.75,
                'estimated_value' => round($ctx['monthly_income_estimate'] * 0.5, 2),
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'product' => 'Basic Savings Account',
                'reason' => 'Entry-level product to build banking relationship',
                'confidence' => 0.6,
                'estimated_value' => 5000,
            ];
        }

        // Sort by confidence
        usort($recommendations, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $recommendations;
    }

    /**
     * Helper: call Claude API for CortexService specific analysis.
     */
    private function callCortexApi(string $systemPrompt, string $userPrompt, Customer $customer): string
    {
        if (empty($this->apiKey)) {
            return '';
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => 500,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text', '');
            }
        } catch (\Exception $e) {
            \Log::warning('Cortex API call failed', ['message' => $e->getMessage()]);
        }

        return '';
    }

    /**
     * Helper: calculate standard deviation.
     */
    private function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) return 0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / ($count - 1);

        return sqrt($variance);
    }
}
