<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AiReviewService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
    }

    /**
     * Generate a comprehensive AI review for a customer.
     * Uses Claude API when available, falls back to enhanced rule-based analysis.
     */
    public function generateReview(Customer $customer, string $engine = 'auto'): string
    {
        $customer->load(['accounts', 'loans.loanProduct', 'insurancePolicies']);
        $context = $this->buildCustomerContext($customer);
        $startTime = microtime(true);

        // Engine selection: auto, claude, builtin
        if ($engine === 'builtin') {
            $result = $this->generateRuleBasedReview($customer, $context);
            $this->trackUsage('profile_review', 'standard', $customer->id, 'customer', 0, 0, 0, $startTime, true);
            return $result;
        }

        if ($engine === 'claude') {
            if (empty($this->apiKey)) {
                return "### ⚠️ Cortex Extended Not Available\n\nCortex Extended AI engine is not configured for this institution. Please contact your administrator to enable premium AI analysis.\n\nSelect **Cortex Standard (Free)** for built-in analysis.";
            }

            // Check billing limits
            $billingCheck = $this->checkBillingLimits();
            if ($billingCheck !== true) {
                return $billingCheck;
            }

            $result = $this->callClaudeApi($context, $customer);
            $this->trackUsage('profile_review', 'extended', $customer->id, 'customer', 1500, 0.02, $this->getChargeAmount(), $startTime, true);
            return $result;
        }

        // Auto: prefer Extended if available + within limits, fallback to Standard
        if (empty($this->apiKey)) {
            $result = $this->generateRuleBasedReview($customer, $context);
            $this->trackUsage('profile_review', 'standard', $customer->id, 'customer', 0, 0, 0, $startTime, true);
            return $result;
        }

        $billingCheck = $this->checkBillingLimits();
        if ($billingCheck !== true) {
            $result = $this->generateRuleBasedReview($customer, $context);
            $this->trackUsage('profile_review', 'standard', $customer->id, 'customer', 0, 0, 0, $startTime, true);
            return $result;
        }

        $cacheKey = "cortex_review_{$customer->id}_" . md5(json_encode($context));
        $result = Cache::remember($cacheKey, 3600, function () use ($context, $customer) {
            return $this->callClaudeApi($context, $customer);
        });
        $this->trackUsage('profile_review', 'extended', $customer->id, 'customer', 1500, 0.02, $this->getChargeAmount(), $startTime, true);
        return $result;
    }

    private function checkBillingLimits(): true|string
    {
        $tenantId = auth()->user()->tenant_id ?? null;
        if (!$tenantId) return true;

        $pricing = \Illuminate\Support\Facades\DB::table('cortex_pricing')->where('tenant_id', $tenantId)->first();

        if ($pricing && !$pricing->extended_enabled) {
            return "### ⚠️ Cortex Extended Disabled\n\nCortex Extended has been disabled for your institution. Contact your administrator.";
        }

        // Count this month's extended calls
        $monthlyUsage = \Illuminate\Support\Facades\DB::table('cortex_usage')
            ->where('tenant_id', $tenantId)
            ->where('engine', 'extended')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $limit = $pricing->monthly_call_limit ?? 100;
        if ($monthlyUsage >= $limit) {
            return "### ⚠️ Monthly Limit Reached\n\nYour institution has used all **{$limit}** Cortex Extended analyses this month. The limit resets on the 1st.\n\nSelect **Cortex Standard (Free)** for unlimited built-in analysis.";
        }

        $freeAllowance = $pricing->free_monthly_calls ?? 10;
        if ($monthlyUsage >= $freeAllowance) {
            $charge = $pricing->price_per_extended_call ?? 500;
            // Still allow but it's billable beyond free tier
        }

        return true;
    }

    private function getChargeAmount(): float
    {
        $tenantId = auth()->user()->tenant_id ?? null;
        if (!$tenantId) return 0;

        $pricing = \Illuminate\Support\Facades\DB::table('cortex_pricing')->where('tenant_id', $tenantId)->first();
        $freeAllowance = $pricing->free_monthly_calls ?? 10;

        $monthlyUsage = \Illuminate\Support\Facades\DB::table('cortex_usage')
            ->where('tenant_id', $tenantId)
            ->where('engine', 'extended')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        if ($monthlyUsage < $freeAllowance) return 0;

        return (float) ($pricing->price_per_extended_call ?? 500);
    }

    private function trackUsage(string $type, string $engine, ?string $subjectId, ?string $subjectType, int $tokens, float $cost, float $charge, float $startTime, bool $success, ?string $error = null): void
    {
        try {
            \Illuminate\Support\Facades\DB::table('cortex_usage')->insert([
                'tenant_id' => auth()->user()->tenant_id ?? '',
                'user_id' => auth()->id() ?? 0,
                'analysis_type' => $type,
                'engine' => $engine,
                'subject_id' => $subjectId,
                'subject_type' => $subjectType,
                'tokens_used' => $tokens,
                'cost' => $cost,
                'charge' => $charge,
                'response_time_ms' => (int) ((microtime(true) - $startTime) * 1000),
                'success' => $success,
                'error' => $error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silent fail — don't break the analysis if tracking fails
        }
    }

    /**
     * Build a comprehensive customer context array for analysis.
     */
    public function buildCustomerContext(Customer $customer): array
    {
        $accounts = $customer->accounts;
        $loans = $customer->loans;
        $insurance = $customer->insurancePolicies ?? collect();

        $totalBalance = $accounts->sum('available_balance');
        $activeLoans = $loans->where('status', 'active');
        $overdueLoans = $loans->where('status', 'overdue');
        $closedLoans = $loans->where('status', 'closed');

        // Transaction analysis (last 90 days)
        $recentTransactions = \App\Models\Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('created_at', '>=', now()->subDays(90))
            ->where('status', 'success')
            ->get();

        $monthlyIncome = $recentTransactions->where('amount', '>', 0)
            ->groupBy(fn($t) => $t->created_at->format('Y-m'))
            ->map->sum('amount')
            ->avg() ?? 0;

        $monthlyExpenses = abs(
            $recentTransactions->where('amount', '<', 0)
                ->groupBy(fn($t) => $t->created_at->format('Y-m'))
                ->map->sum('amount')
                ->avg() ?? 0
        );

        return [
            'name' => $customer->first_name . ' ' . $customer->last_name,
            'age' => $customer->date_of_birth ? Carbon::parse($customer->date_of_birth)->age : null,
            'gender' => $customer->gender,
            'occupation' => $customer->occupation,
            'kyc_status' => $customer->kyc_status,
            'kyc_tier' => $customer->kyc_tier,
            'customer_since' => $customer->created_at?->format('Y-m-d'),
            'account_count' => $accounts->count(),
            'total_balance' => round($totalBalance, 2),
            'active_loans_count' => $activeLoans->count(),
            'overdue_loans_count' => $overdueLoans->count(),
            'closed_loans_count' => $closedLoans->count(),
            'total_outstanding' => round($activeLoans->sum('outstanding_balance') + $overdueLoans->sum('outstanding_balance'), 2),
            'total_principal_borrowed' => round($loans->sum('principal_amount'), 2),
            'insurance_policies' => $insurance->count(),
            'monthly_income_estimate' => round($monthlyIncome, 2),
            'monthly_expense_estimate' => round($monthlyExpenses, 2),
            'transaction_count_90d' => $recentTransactions->count(),
            'avg_transaction_size' => $recentTransactions->count() > 0 ? round($recentTransactions->avg('amount'), 2) : 0,
            'largest_deposit' => $recentTransactions->where('amount', '>', 0)->max('amount') ?? 0,
            'loan_products_used' => $loans->pluck('loanProduct.name')->filter()->unique()->values()->toArray(),
        ];
    }

    /**
     * Call Claude API for intelligent analysis.
     */
    public function callClaudeApi(array $context, Customer $customer, ?string $customSystemPrompt = null, ?string $customUserPrompt = null): string
    {
        $systemPrompt = $customSystemPrompt ?? "You are BankOS Cortex, an AI financial analyst for a Nigerian microfinance bank. Analyze customer profiles and provide actionable insights. Use these sections with emojis:
1. 🤖 AI Executive Summary (risk level, sentiment, key finding)
2. 💰 Financial & Transactional Health (balance analysis, cash flow, liquidity)
3. 💳 Credit & Lending Profile (loan history, repayment behavior, eligibility)
4. 📊 Risk Assessment (debt-to-income, concentration risk, red flags)
5. 🎯 Product Recommendations (cross-sell opportunities based on profile)
6. 🛡️ Compliance & KYC (verification status, limits, regulatory notes)
7. 🔮 Predictive Insights (churn risk, growth potential, next best action)

Be specific with numbers. Use Nigerian Naira (₦). Bold key metrics. Provide 2-3 actionable recommendations per section. Rate risk as: Very Low / Low / Moderate / High / Critical.";

        $userPrompt = $customUserPrompt ?? "Analyze this bank customer profile:\n\n" . json_encode($context, JSON_PRETTY_PRINT);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => $this->model,
                'max_tokens' => 2000,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text', '');
                return $content ?: $this->generateRuleBasedReview($customer, $context);
            }

            \Log::warning('Cortex AI API error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->generateRuleBasedReview($customer, $context);
        } catch (\Exception $e) {
            \Log::warning('Cortex AI API exception', ['message' => $e->getMessage()]);
            return $this->generateRuleBasedReview($customer, $context);
        }
    }

    /**
     * Enhanced rule-based fallback — comprehensive 7-section analysis.
     */
    public function generateRuleBasedReview(Customer $customer, array $ctx): string
    {
        $review = [];

        // ── 1. Executive Summary ──
        $review[] = "### 🤖 AI Executive Summary";

        $riskLevel = 'Low';
        $sentiment = 'Stable';

        if ($ctx['overdue_loans_count'] > 0) {
            $riskLevel = $ctx['overdue_loans_count'] >= 2 ? 'High' : 'Moderate';
            $sentiment = 'Concerning — Active Delinquency';
        } elseif ($ctx['total_outstanding'] > $ctx['total_balance'] * 2) {
            $riskLevel = 'High';
            $sentiment = 'Elevated — Highly Leveraged';
        } elseif ($ctx['total_outstanding'] > $ctx['total_balance']) {
            $riskLevel = 'Moderate';
            $sentiment = 'Watch — Debt Exceeds Liquidity';
        } elseif ($ctx['kyc_status'] !== 'approved') {
            $riskLevel = 'Moderate';
            $sentiment = 'Pending Compliance Action';
        } elseif ($ctx['total_balance'] > 100000 && $ctx['overdue_loans_count'] === 0) {
            $riskLevel = 'Very Low';
            $sentiment = 'Excellent — High Value, Low Risk';
        } elseif ($ctx['total_balance'] > 50000 && $ctx['active_loans_count'] === 0) {
            $riskLevel = 'Very Low';
            $sentiment = 'Strong — Liquid & Unencumbered';
        }

        $review[] = "**Overall Risk Level:** {$riskLevel}";
        $review[] = "**Profile Sentiment:** {$sentiment}";

        $customerAge = $ctx['age'] ? "{$ctx['age']}-year-old" : 'Age unspecified';
        $gender = $ctx['gender'] ? ucfirst($ctx['gender']) : 'Gender unspecified';
        $tenure = $ctx['customer_since'] ? Carbon::parse($ctx['customer_since'])->diffForHumans(null, true) : 'Unknown';

        $review[] = "**{$ctx['name']}** is a {$customerAge} {$gender} customer" . ($ctx['occupation'] ? " working as a **{$ctx['occupation']}**" : "") . ". They have been banking with us for **{$tenure}** with KYC status: **{$ctx['kyc_status']}** (Tier: {$ctx['kyc_tier']}).";
        $review[] = "Key metrics: **{$ctx['account_count']}** account(s), **₦" . number_format($ctx['total_balance'], 2) . "** total balance, **{$ctx['active_loans_count']}** active loan(s), **{$ctx['transaction_count_90d']}** transactions in last 90 days.";

        // ── 2. Financial & Transactional Health ──
        $review[] = "### 💰 Financial & Transactional Health";

        if ($ctx['account_count'] === 0) {
            $review[] = "- **⚠️ Warning:** No active accounts found. The customer is currently unbanked within the platform.";
            $review[] = "- **Action:** Push account origination — recommend a basic savings account to begin relationship building.";
        } else {
            $review[] = "- **Total Liquidity:** ₦" . number_format($ctx['total_balance'], 2) . " across {$ctx['account_count']} account(s)";

            if ($ctx['monthly_income_estimate'] > 0) {
                $review[] = "- **Estimated Monthly Income:** ₦" . number_format($ctx['monthly_income_estimate'], 2);
                $review[] = "- **Estimated Monthly Expenses:** ₦" . number_format($ctx['monthly_expense_estimate'], 2);

                $savingsRate = $ctx['monthly_income_estimate'] > 0
                    ? round(($ctx['monthly_income_estimate'] - $ctx['monthly_expense_estimate']) / $ctx['monthly_income_estimate'] * 100, 1)
                    : 0;
                $review[] = "- **Savings Rate:** {$savingsRate}%" . ($savingsRate < 10 ? ' ⚠️ Below recommended threshold' : ($savingsRate > 30 ? ' ✅ Excellent' : ' Acceptable'));
            }

            if ($ctx['transaction_count_90d'] > 0) {
                $review[] = "- **Transaction Activity (90d):** {$ctx['transaction_count_90d']} transactions, avg size ₦" . number_format(abs($ctx['avg_transaction_size']), 2);
                if ($ctx['largest_deposit'] > 0) {
                    $review[] = "- **Largest Single Deposit:** ₦" . number_format($ctx['largest_deposit'], 2);
                }
            } else {
                $review[] = "- **⚠️ Dormancy Risk:** Zero transactions in the last 90 days. Account may be going dormant.";
            }

            if ($ctx['total_balance'] < 5000) {
                $review[] = "- **Recommendation:** Low balance customer. Consider high-yield savings products to increase deposit stickiness.";
            } elseif ($ctx['total_balance'] > 500000) {
                $review[] = "- **Recommendation:** High-value depositor. Introduce wealth management, fixed deposits, or treasury bill products.";
            } else {
                $review[] = "- **Recommendation:** Stable depositor. Cross-sell standing orders or target deposit products to grow balances.";
            }
        }

        // ── 3. Credit & Lending Profile ──
        $review[] = "### 💳 Credit & Lending Profile";

        $totalLoans = $ctx['active_loans_count'] + $ctx['overdue_loans_count'] + $ctx['closed_loans_count'];

        if ($totalLoans === 0) {
            $review[] = "- **Untapped Potential:** No borrowing history found.";
            $review[] = "- Based on profile metrics, customer may qualify for a **Tier-1 micro-loan** or **salary advance**.";
            $review[] = "- **Action:** Run soft credit check to pre-qualify and present tailored offers.";
        } else {
            $review[] = "- **Loan History:** {$totalLoans} total loan(s) — {$ctx['active_loans_count']} active, {$ctx['overdue_loans_count']} overdue, {$ctx['closed_loans_count']} closed";
            $review[] = "- **Total Principal Borrowed:** ₦" . number_format($ctx['total_principal_borrowed'], 2);
            $review[] = "- **Current Outstanding:** ₦" . number_format($ctx['total_outstanding'], 2);

            if (!empty($ctx['loan_products_used'])) {
                $review[] = "- **Products Used:** " . implode(', ', $ctx['loan_products_used']);
            }

            if ($ctx['overdue_loans_count'] > 0) {
                $review[] = "- **🚨 Delinquency Alert:** {$ctx['overdue_loans_count']} overdue loan(s) detected. Immediate collection follow-up required.";
                $review[] = "- **Action:** Escalate to collections desk. Consider restructuring if customer shows willingness to pay.";
            } elseif ($ctx['closed_loans_count'] > 0 && $ctx['overdue_loans_count'] === 0) {
                $review[] = "- **✅ Good Repayment History:** {$ctx['closed_loans_count']} loan(s) successfully closed with no current delinquency.";
                $review[] = "- **Action:** Pre-approve for higher-tier lending products with improved terms.";
            }

            if ($ctx['total_outstanding'] > $ctx['total_balance']) {
                $review[] = "- **⚠️ Leverage Warning:** Outstanding debt (₦" . number_format($ctx['total_outstanding'], 2) . ") exceeds available liquidity (₦" . number_format($ctx['total_balance'], 2) . ").";
            }
        }

        // ── 4. Risk Assessment ──
        $review[] = "### 📊 Risk Assessment";

        // Debt-to-income ratio
        if ($ctx['monthly_income_estimate'] > 0 && $ctx['total_outstanding'] > 0) {
            $monthlyDebtService = $ctx['total_outstanding'] / 12; // rough estimate
            $dti = round($monthlyDebtService / $ctx['monthly_income_estimate'] * 100, 1);
            $review[] = "- **Debt-to-Income Ratio (est.):** {$dti}%" . ($dti > 50 ? ' 🚨 Critical' : ($dti > 35 ? ' ⚠️ Elevated' : ' ✅ Healthy'));
        }

        // Concentration risk
        if ($ctx['account_count'] === 1 && $ctx['total_balance'] > 100000) {
            $review[] = "- **Concentration Risk:** All funds in a single account. Recommend diversification across savings and fixed deposit products.";
        }

        // Red flags
        $redFlags = [];
        if ($ctx['kyc_status'] !== 'approved') $redFlags[] = "KYC not approved ({$ctx['kyc_status']})";
        if ($ctx['overdue_loans_count'] > 0) $redFlags[] = "{$ctx['overdue_loans_count']} overdue loan(s)";
        if ($ctx['transaction_count_90d'] === 0 && $ctx['account_count'] > 0) $redFlags[] = "No transactions in 90 days (potential dormancy)";
        if ($ctx['total_outstanding'] > $ctx['total_balance'] * 3) $redFlags[] = "Debt-to-liquidity ratio extremely high";

        if (!empty($redFlags)) {
            $review[] = "- **Red Flags:** " . implode(' | ', $redFlags);
        } else {
            $review[] = "- **No Red Flags Detected.** Customer profile appears clean.";
        }

        $review[] = "- **Overall Risk Rating:** **{$riskLevel}**";

        // ── 5. Product Recommendations ──
        $review[] = "### 🎯 Product Recommendations";

        $recommendations = [];
        if ($ctx['total_balance'] > 100000 && $ctx['insurance_policies'] === 0) {
            $recommendations[] = "**Credit Life Insurance** — High balance without insurance coverage. Protect deposits with a micro-insurance product.";
        }
        if ($totalLoans === 0 && $ctx['total_balance'] > 10000) {
            $recommendations[] = "**Micro-Loan / Salary Advance** — No loan history but sufficient balance suggests creditworthiness for entry-level credit.";
        }
        if ($ctx['total_balance'] > 500000) {
            $recommendations[] = "**Fixed Deposit / Treasury Bills** — High liquidity customer suitable for term deposit products with premium rates.";
        }
        if ($ctx['closed_loans_count'] >= 2 && $ctx['overdue_loans_count'] === 0) {
            $recommendations[] = "**Premium Loan Product** — Proven repayment track record qualifies for higher-tier lending with better rates.";
        }
        if ($ctx['account_count'] === 1) {
            $recommendations[] = "**Additional Account** — Open a dedicated savings or target-savings account to improve deposit stickiness.";
        }
        if ($ctx['monthly_income_estimate'] > 50000 && $ctx['insurance_policies'] === 0) {
            $recommendations[] = "**Insurance Products** — Steady income earner without insurance coverage. Recommend health or life insurance.";
        }

        if (empty($recommendations)) {
            $recommendations[] = "**Basic Savings Product** — Maintain engagement through competitive savings rates.";
            $recommendations[] = "**Financial Literacy Program** — Enroll in digital banking awareness to increase platform usage.";
        }

        foreach ($recommendations as $rec) {
            $review[] = "- {$rec}";
        }

        // ── 6. Compliance & KYC ──
        $review[] = "### 🛡️ Compliance & KYC";

        if ($ctx['kyc_status'] === 'approved') {
            $review[] = "- **✅ KYC Fully Verified.** No compliance blockers for expanded transaction limits.";
            $review[] = "- Customer is on **Tier {$ctx['kyc_tier']}** — eligible for corresponding daily transaction limits.";
            $review[] = "- **Recommendation:** Schedule periodic KYC refresh per CBN guidelines (every 12 months for high-risk, 36 months for low-risk).";
        } else {
            $review[] = "- **🚨 Compliance Blocker:** Customer KYC status is **{$ctx['kyc_status']}**.";
            $review[] = "- Restrict outward transfers and high-value transactions until BVN/NIN verification is completed.";
            $review[] = "- **Action:** Flag for compliance desk review. Send SMS/email reminder to customer to complete documentation.";
        }

        if ($ctx['largest_deposit'] > 1000000) {
            $review[] = "- **Large Transaction Alert:** Deposits exceeding ₦1,000,000 detected. Ensure CTR (Currency Transaction Report) has been filed per CBN AML regulations.";
        }

        // ── 7. Predictive Insights ──
        $review[] = "### 🔮 Predictive Insights";

        // Churn risk
        $churnRisk = 'Low';
        if ($ctx['transaction_count_90d'] === 0 && $ctx['active_loans_count'] === 0) {
            $churnRisk = 'High';
        } elseif ($ctx['transaction_count_90d'] < 5) {
            $churnRisk = 'Moderate';
        }
        $review[] = "- **Churn Risk:** {$churnRisk}" . ($churnRisk !== 'Low' ? ' — Declining engagement detected. Proactive outreach recommended.' : ' — Customer shows consistent engagement.');

        // Growth potential
        if ($ctx['monthly_income_estimate'] > 100000 && $ctx['active_loans_count'] === 0) {
            $review[] = "- **Growth Potential:** **High** — Strong income earner with untapped lending potential. Projected 12-month value increase: 40-60%.";
        } elseif ($ctx['total_balance'] > 50000) {
            $review[] = "- **Growth Potential:** **Moderate** — Healthy balance. Cross-selling could increase relationship value by 20-35%.";
        } else {
            $review[] = "- **Growth Potential:** **Standard** — Focus on engagement and transaction volume growth.";
        }

        // Next best action
        $nba = match (true) {
            $ctx['overdue_loans_count'] > 0 => 'Initiate collections process and explore restructuring options.',
            $ctx['kyc_status'] !== 'approved' => 'Complete KYC verification to unlock full banking services.',
            $ctx['transaction_count_90d'] === 0 => 'Send reactivation campaign — offer incentives for first transaction this quarter.',
            $ctx['total_balance'] > 200000 && $ctx['active_loans_count'] === 0 => 'Present pre-approved loan offer based on deposit history.',
            $ctx['insurance_policies'] === 0 && $ctx['active_loans_count'] > 0 => 'Cross-sell credit life insurance to protect active loan.',
            default => 'Schedule quarterly relationship review call to maintain engagement.',
        };
        $review[] = "- **Next Best Action:** {$nba}";

        return implode("\n\n", $review);
    }
}
