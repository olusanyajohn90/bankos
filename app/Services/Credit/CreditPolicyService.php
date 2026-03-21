<?php

namespace App\Services\Credit;

use App\Models\BureauReport;
use App\Models\CreditDecision;
use App\Models\CreditPolicy;
use App\Models\Loan;
use App\Services\Bureau\InternalCreditScoreService;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CreditPolicyService
{
    /**
     * Evaluate credit policy rules for a given loan and create a CreditDecision.
     */
    public function evaluate(Loan $loan): CreditDecision
    {
        // ── 1. Load relationships ────────────────────────────────────────────
        $loan->loadMissing(['customer', 'loanProduct']);
        $customer = $loan->customer;

        // Customer's active loans (excluding current)
        $activeLoans = $customer
            ? $customer->loans()
                ->whereIn('status', ['active', 'overdue', 'approved'])
                ->where('id', '!=', $loan->id)
                ->get()
            : collect();

        // Latest bureau report for this customer
        $bureauReport = BureauReport::where('customer_id', $loan->customer_id)
            ->whereIn('status', ['retrieved', 'parsed'])
            ->latest()
            ->first();

        // ── 2. Find active policy for this tenant + product ──────────────────
        $tenantId = $loan->tenant_id;
        $productId = $loan->product_id;

        $policy = CreditPolicy::active()
            ->where('tenant_id', $tenantId)
            ->where('loan_product_id', $productId)
            ->first();

        // Fall back to tenant-level policy (no product filter)
        if (!$policy) {
            $policy = CreditPolicy::active()
                ->where('tenant_id', $tenantId)
                ->whereNull('loan_product_id')
                ->first();
        }

        // ── 3. No policy found — refer ───────────────────────────────────────
        if (!$policy) {
            return CreditDecision::create([
                'tenant_id'      => $tenantId,
                'loan_id'        => $loan->id,
                'policy_id'      => null,
                'bureau_score'   => $bureauReport?->credit_score,
                'internal_score' => null,
                'final_score'    => $bureauReport?->credit_score,
                'recommendation' => 'refer',
                'auto_decided'   => false,
                'rules_passed'   => [],
                'rules_failed'   => [],
                'conditions'     => [],
                'notes'          => 'No active credit policy found for this tenant/product. Referred for manual review.',
            ]);
        }

        // ── 4. Compute internal score ────────────────────────────────────────
        $internalScore = null;
        $bureauScore   = $bureauReport?->credit_score;

        if ($bureauReport && !empty($bureauReport->parsed_data)) {
            try {
                $scoreResult   = app(InternalCreditScoreService::class)->compute([$bureauReport]);
                $internalScore = $scoreResult['score'] ?? null;
            } catch (\Exception $e) {
                Log::warning('CreditPolicyService: InternalCreditScoreService failed', ['error' => $e->getMessage()]);
            }
        }

        // ── 5. Final score ───────────────────────────────────────────────────
        if ($internalScore !== null && $bureauScore !== null) {
            // Weighted average: 60% internal, 40% bureau
            $finalScore = (int) round($internalScore * 0.6 + $bureauScore * 0.4);
        } elseif ($internalScore !== null) {
            $finalScore = $internalScore;
        } elseif ($bureauScore !== null) {
            $finalScore = $bureauScore;
        } else {
            $finalScore = null;
        }

        // ── 6. Evaluate rules ────────────────────────────────────────────────
        $activeRules = $policy->rules()->active()->get();
        $rulesPassed = [];
        $rulesFailed = [];
        $conditions  = [];

        foreach ($activeRules as $rule) {
            $result = $this->evaluateRule($rule, $loan, $customer, $bureauReport, $activeLoans, $finalScore);

            if ($result['passed']) {
                $rulesPassed[] = [
                    'rule_id'   => $rule->id,
                    'rule_type' => $rule->rule_type,
                    'threshold' => $rule->threshold_value,
                    'actual'    => $result['actual'],
                    'detail'    => $result['detail'],
                ];
            } else {
                $rulesFailed[] = [
                    'rule_id'         => $rule->id,
                    'rule_type'       => $rule->rule_type,
                    'threshold'       => $rule->threshold_value,
                    'actual'          => $result['actual'],
                    'action_on_fail'  => $rule->action_on_fail,
                    'action_param'    => $rule->action_param,
                    'severity'        => $rule->severity,
                    'detail'          => $result['detail'],
                ];

                if ($rule->action_on_fail === 'reduce_amount' && $rule->action_param) {
                    $factor    = (float) $rule->action_param;
                    $newAmount = round((float) $loan->principal_amount * $factor, 2);
                    $conditions[] = [
                        'type'        => 'reduce_amount',
                        'description' => "Reduce principal to ₦" . number_format($newAmount, 2) . " ({$rule->action_param} of requested)",
                        'value'       => $newAmount,
                        'rule_type'   => $rule->rule_type,
                    ];
                }
            }
        }

        // ── 7. Determine recommendation ──────────────────────────────────────
        $recommendation = 'refer';
        $autoDeci       = false;

        // Check hard failures
        $hardDecline = collect($rulesFailed)->where('severity', 'hard')->where('action_on_fail', 'decline')->isNotEmpty();
        $hardRefer   = collect($rulesFailed)->where('severity', 'hard')->where('action_on_fail', 'refer')->isNotEmpty();

        if ($hardDecline) {
            $recommendation = 'decline';
        } elseif ($finalScore !== null && $policy->auto_decline_below !== null && $finalScore < $policy->auto_decline_below) {
            $recommendation = 'decline';
            $autoDeci       = true;
        } elseif ($finalScore !== null && $policy->auto_approve_above !== null && $finalScore >= $policy->auto_approve_above && !$hardRefer && collect($rulesFailed)->where('severity', 'hard')->isEmpty()) {
            $recommendation = 'approve';
            $autoDeci       = true;
        } elseif ($hardRefer) {
            $recommendation = 'refer';
        } elseif (!empty($conditions)) {
            $recommendation = 'conditional';
        } else {
            $recommendation = 'refer';
        }

        // ── 8. Persist CreditDecision ────────────────────────────────────────
        return CreditDecision::create([
            'tenant_id'      => $tenantId,
            'loan_id'        => $loan->id,
            'policy_id'      => $policy->id,
            'bureau_score'   => $bureauScore,
            'internal_score' => $internalScore,
            'final_score'    => $finalScore,
            'recommendation' => $recommendation,
            'auto_decided'   => $autoDeci,
            'rules_passed'   => $rulesPassed,
            'rules_failed'   => $rulesFailed,
            'conditions'     => $conditions,
            'notes'          => $this->buildNotes($recommendation, $autoDeci, $rulesFailed, $finalScore),
        ]);
    }

    /**
     * Apply the credit decision: auto-approve, auto-decline, or create workflow.
     */
    public function applyDecision(CreditDecision $decision): void
    {
        $loan = $decision->loan ?? Loan::find($decision->loan_id);
        if (!$loan) {
            return;
        }

        if ($decision->recommendation === 'decline' && $decision->auto_decided) {
            $loan->update([
                'status'        => 'rejected',
                'ai_credit_score' => $decision->final_score,
            ]);
            return;
        }

        if ($decision->recommendation === 'approve' && $decision->auto_decided) {
            $loan->update([
                'status'          => 'approved',
                'ai_credit_score' => $decision->final_score,
            ]);
            return;
        }

        // Refer, conditional, or non-auto decisions → create workflow
        $metadata = [
            'credit_decision_id' => $decision->id,
            'final_score'        => $decision->final_score,
            'internal_score'     => $decision->internal_score,
            'bureau_score'       => $decision->bureau_score,
            'recommendation'     => $decision->recommendation,
            'auto_decided'       => $decision->auto_decided,
            'rules_failed_count' => count($decision->rules_failed ?? []),
            'conditions'         => $decision->conditions ?? [],
        ];

        try {
            app(WorkflowService::class)->create('Loan Approval', $loan, [
                'amount'   => $loan->principal_amount,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::warning('CreditPolicyService: WorkflowService failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Return existing decision if one already exists, otherwise evaluate.
     */
    public function getOrEvaluate(Loan $loan): CreditDecision
    {
        $existing = CreditDecision::where('loan_id', $loan->id)->first();
        if ($existing) {
            return $existing;
        }

        return $this->evaluate($loan);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Evaluate a single policy rule against the loan/customer/bureau data.
     *
     * @return array{passed: bool, actual: mixed, detail: string}
     */
    private function evaluateRule(
        \App\Models\CreditPolicyRule $rule,
        Loan $loan,
        $customer,
        ?BureauReport $bureauReport,
        $activeLoans,
        ?int $finalScore
    ): array {
        $threshold = $rule->threshold_value;

        switch ($rule->rule_type) {

            case 'min_bureau_score':
                if (!$bureauReport || $bureauReport->credit_score === null) {
                    return ['passed' => $rule->severity === 'soft', 'actual' => null, 'detail' => 'No bureau score available'];
                }
                $actual = (int) $bureauReport->credit_score;
                return [
                    'passed' => $actual >= $threshold,
                    'actual' => $actual,
                    'detail' => "Bureau score {$actual} vs minimum {$threshold}",
                ];

            case 'max_dti_ratio':
                // monthly_repayment / monthly_income
                $tenureMonths   = max(1, (int) $loan->tenure_days);
                $monthlyRepay   = (float) $loan->principal_amount / $tenureMonths;
                $monthlyIncome  = $this->estimateMonthlyIncome($customer, $bureauReport);

                if ($monthlyIncome <= 0) {
                    // No income data — hard rule fails, soft rule passes
                    return [
                        'passed' => $rule->severity === 'soft',
                        'actual' => null,
                        'detail' => 'Monthly income data unavailable; cannot compute DTI ratio',
                    ];
                }
                $dti    = round($monthlyRepay / $monthlyIncome, 4);
                return [
                    'passed' => $dti <= $threshold,
                    'actual' => $dti,
                    'detail' => sprintf('DTI = %.2f%% (monthly repayment ₦%s / income ₦%s)', $dti * 100, number_format($monthlyRepay, 2), number_format($monthlyIncome, 2)),
                ];

            case 'max_loan_to_income':
                $annualIncome = $this->estimateMonthlyIncome($customer, $bureauReport) * 12;
                if ($annualIncome <= 0) {
                    return [
                        'passed' => $rule->severity === 'soft',
                        'actual' => null,
                        'detail' => 'Annual income data unavailable; cannot compute LTI ratio',
                    ];
                }
                $lti = round((float) $loan->principal_amount / $annualIncome, 4);
                return [
                    'passed' => $lti <= $threshold,
                    'actual' => $lti,
                    'detail' => sprintf('LTI = %.2f (principal ₦%s / annual income ₦%s)', $lti, number_format((float)$loan->principal_amount, 2), number_format($annualIncome, 2)),
                ];

            case 'min_customer_age':
                if (!$customer || !$customer->date_of_birth) {
                    return ['passed' => $rule->severity === 'soft', 'actual' => null, 'detail' => 'Date of birth not available'];
                }
                $age = Carbon::parse($customer->date_of_birth)->age;
                return [
                    'passed' => $age >= $threshold,
                    'actual' => $age,
                    'detail' => "Customer age {$age} years vs minimum {$threshold} years",
                ];

            case 'max_active_loans':
                $count = $activeLoans->count();
                return [
                    'passed' => $count < $threshold,
                    'actual' => $count,
                    'detail' => "Active loans: {$count} vs maximum " . ((int) $threshold - 1),
                ];

            case 'min_bvn_verified':
                if (!$customer) {
                    return ['passed' => false, 'actual' => false, 'detail' => 'Customer record not found'];
                }
                $verified = (bool) $customer->bvn_verified;
                return [
                    'passed' => $threshold >= 1 ? $verified : true,
                    'actual' => $verified,
                    'detail' => 'BVN verified: ' . ($verified ? 'Yes' : 'No'),
                ];

            case 'max_delinquency_count':
                if (!$bureauReport) {
                    return ['passed' => $rule->severity === 'soft', 'actual' => null, 'detail' => 'No bureau report available'];
                }
                $delinquency = (int) ($bureauReport->delinquency_count ?? 0);
                return [
                    'passed' => $delinquency <= $threshold,
                    'actual' => $delinquency,
                    'detail' => "Delinquency count: {$delinquency} vs maximum {$threshold}",
                ];

            case 'max_outstanding_ratio':
                if (!$bureauReport) {
                    return ['passed' => $rule->severity === 'soft', 'actual' => null, 'detail' => 'No bureau report available'];
                }
                $outstanding = (float) ($bureauReport->total_outstanding ?? 0);
                $principal   = (float) $loan->principal_amount;
                if ($principal <= 0) {
                    return ['passed' => true, 'actual' => 0, 'detail' => 'Principal amount is zero'];
                }
                $ratio = round($outstanding / $principal, 4);
                return [
                    'passed' => $ratio <= $threshold,
                    'actual' => $ratio,
                    'detail' => sprintf('Outstanding ratio = %.2f (bureau outstanding ₦%s / principal ₦%s)', $ratio, number_format($outstanding, 2), number_format($principal, 2)),
                ];

            case 'collateral_required':
                $collateralValue = (float) ($loan->collateral_value ?? 0);
                return [
                    'passed' => $threshold >= 1 ? $collateralValue > 0 : true,
                    'actual' => $collateralValue,
                    'detail' => 'Collateral value: ₦' . number_format($collateralValue, 2),
                ];

            case 'min_kyc_tier':
                if (!$customer) {
                    return ['passed' => false, 'actual' => null, 'detail' => 'Customer record not found'];
                }
                $tierMap = ['level_1' => 1, 'level_2' => 2, 'level_3' => 3];
                $kycTier = $tierMap[$customer->kyc_tier ?? 'level_1'] ?? 0;
                return [
                    'passed' => $kycTier >= $threshold,
                    'actual' => $customer->kyc_tier,
                    'detail' => "KYC tier: {$customer->kyc_tier} vs minimum {$threshold}",
                ];

            default:
                return ['passed' => true, 'actual' => null, 'detail' => "Unknown rule type: {$rule->rule_type}"];
        }
    }

    /**
     * Estimate monthly income from bureau report or customer data.
     * Returns 0 if no data is available.
     */
    private function estimateMonthlyIncome($customer, ?BureauReport $bureauReport): float
    {
        // Try bureau parsed_data for income field
        if ($bureauReport && !empty($bureauReport->parsed_data)) {
            $subject = $bureauReport->parsed_data['subject'] ?? [];
            $income  = (float) ($subject['monthly_income'] ?? $subject['income'] ?? 0);
            if ($income > 0) {
                return $income;
            }

            $summaries = $bureauReport->parsed_data['summaries'] ?? ($bureauReport->parsed_data['summary'] ?? []);
            if (!empty($summaries) && isset($summaries['monthly_income'])) {
                return (float) $summaries['monthly_income'];
            }
        }

        // Try customer model income field if it exists
        if ($customer && isset($customer->monthly_income) && $customer->monthly_income > 0) {
            return (float) $customer->monthly_income;
        }

        return 0.0;
    }

    /**
     * Build a human-readable notes string for the decision.
     */
    private function buildNotes(string $recommendation, bool $autoDeci, array $rulesFailed, ?int $finalScore): string
    {
        $parts = [];

        if ($finalScore !== null) {
            $parts[] = "Final score: {$finalScore}";
        }

        if (!empty($rulesFailed)) {
            $failedTypes = implode(', ', array_column($rulesFailed, 'rule_type'));
            $parts[] = count($rulesFailed) . ' rule(s) failed: ' . $failedTypes;
        }

        $decisionLabel = match($recommendation) {
            'approve'     => 'Auto-approved',
            'decline'     => $autoDeci ? 'Auto-declined (score below threshold)' : 'Declined (hard rule failure)',
            'refer'       => 'Referred for manual review',
            'conditional' => 'Conditional approval — see conditions',
            default       => ucfirst($recommendation),
        };

        array_unshift($parts, $decisionLabel);

        return implode('. ', $parts) . '.';
    }
}
