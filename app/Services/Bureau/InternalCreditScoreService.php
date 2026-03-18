<?php

namespace App\Services\Bureau;

use Carbon\Carbon;

/**
 * Computes an internal credit score (300–850, FICO-standard weights) from
 * one or more parsed bureau reports for a single customer.
 *
 * Factors & weights:
 *   35% — Payment History     (delinquencies, DPD, worst classification)
 *   30% — Credit Utilization  (balance ÷ limit)
 *   15% — History Length      (age of oldest account)
 *   10% — Credit Mix          (variety of account types)
 *   10% — New Credit          (recent enquiries)
 */
class InternalCreditScoreService
{
    public function compute(array $reports): array
    {
        // ── Aggregate all parsed data across bureau sources ──────────────────
        $allAccounts  = [];
        $allInquiries = [];
        $allSummaries = [];
        $bureauSources = [];
        $subject      = [];

        foreach ($reports as $report) {
            $parsed = $report->parsed_data ?? null;
            if (!$parsed) continue;

            $bureauSources[] = $report->bureau;

            foreach ($parsed['accounts']  ?? [] as $a) $allAccounts[]  = $a;
            foreach ($parsed['inquiries'] ?? [] as $i) $allInquiries[] = $i;

            $s = $parsed['summary'] ?? [];
            if (!empty($s)) $allSummaries[] = $s;

            if (empty($subject) && !empty($parsed['subject'])) {
                $subject = $parsed['subject'];
            }
        }

        // Deduplicate accounts by account_number (prefer most recent)
        $seen = [];
        $accounts = [];
        foreach ($allAccounts as $a) {
            $key = $a['account_number'] ?? '';
            if ($key && isset($seen[$key])) continue;
            if ($key) $seen[$key] = true;
            $accounts[] = $a;
        }

        // ── Score each factor ────────────────────────────────────────────────
        $payFactor   = $this->factorPaymentHistory($accounts);
        $utilFactor  = $this->factorUtilization($accounts, $allSummaries);
        $histFactor  = $this->factorHistoryLength($accounts);
        $mixFactor   = $this->factorCreditMix($accounts);
        $inqFactor   = $this->factorNewCredit($allInquiries, $allSummaries);

        // ── Weighted composite (0–1) → 300–850 ──────────────────────────────
        $weighted = ($payFactor['raw']  * 0.35)
                  + ($utilFactor['raw'] * 0.30)
                  + ($histFactor['raw'] * 0.15)
                  + ($mixFactor['raw']  * 0.10)
                  + ($inqFactor['raw']  * 0.10);

        $score = (int) round(300 + ($weighted * 550));
        $score = max(300, min(850, $score));

        $grade = $this->grade($score);

        return [
            'score'         => $score,
            'grade'         => $grade['label'],
            'grade_color'   => $grade['color'],
            'grade_desc'    => $grade['description'],
            'factors'       => [
                'payment_history' => $payFactor,
                'utilization'     => $utilFactor,
                'history_length'  => $histFactor,
                'credit_mix'      => $mixFactor,
                'new_credit'      => $inqFactor,
            ],
            'consolidated'  => [
                'accounts'       => $accounts,
                'inquiries'      => $allInquiries,
                'summaries'      => $allSummaries,
                'bureau_sources' => array_unique($bureauSources),
                'subject'        => $subject,
            ],
            'recommendation' => $this->recommend($score, $accounts),
        ];
    }

    // ─── Factor: Payment History (35%) ───────────────────────────────────────

    private function factorPaymentHistory(array $accounts): array
    {
        if (empty($accounts)) {
            return $this->factor(0.50, '35%', 'No History', 'No credit history found.');
        }

        $total = count($accounts);
        $derog = 0;
        $maxDpd = 0;
        $worst = 'PERFORMING';

        foreach ($accounts as $a) {
            $dpd = (int) ($a['dpd'] ?? 0);
            $cls = strtoupper($a['classification'] ?? 'PERFORMING');
            $maxDpd = max($maxDpd, $dpd);
            if (in_array($cls, ['LOSS', 'LOST', 'DOUBTFUL', 'SUBSTANDARD'])) $derog++;
            if (in_array($cls, ['LOSS', 'LOST'])) $worst = 'LOSS';
            elseif ($cls === 'DOUBTFUL'    && $worst !== 'LOSS') $worst = 'DOUBTFUL';
            elseif ($cls === 'SUBSTANDARD' && !in_array($worst, ['LOSS', 'DOUBTFUL'])) $worst = 'SUBSTANDARD';
            elseif ($cls === 'WATCHLIST'   && !in_array($worst, ['LOSS', 'DOUBTFUL', 'SUBSTANDARD'])) $worst = 'WATCHLIST';
        }

        $raw = 1.0;
        if ($maxDpd >= 360 || in_array($worst, ['LOSS', 'LOST'])) $raw = 0.08;
        elseif ($maxDpd >= 180 || $worst === 'DOUBTFUL')           $raw = 0.22;
        elseif ($maxDpd >= 90  || $worst === 'SUBSTANDARD')        $raw = 0.42;
        elseif ($maxDpd >= 30  || $worst === 'WATCHLIST')          $raw = 0.62;
        elseif ($maxDpd >= 1)                                      $raw = 0.82;

        if ($derog >= 2) $raw *= 0.70;
        elseif ($derog === 1) $raw *= 0.85;
        $raw = max(0.0, min(1.0, $raw));

        $label  = $this->ratingLabel($raw);
        $detail = "Max DPD: {$maxDpd} days | Worst classification: {$worst} | Derogatory: {$derog}/{$total}";

        return $this->factor($raw, '35%', $label, $detail);
    }

    // ─── Factor: Credit Utilization (30%) ────────────────────────────────────

    private function factorUtilization(array $accounts, array $summaries): array
    {
        $balance = 0.0;
        $limit   = 0.0;

        foreach ($summaries as $s) {
            $balance += (float) ($s['total_balance']       ?? 0);
            $limit   += (float) ($s['total_credit_limit']  ?? 0);
        }
        if ($balance == 0) $balance = (float) array_sum(array_column($accounts, 'outstanding_balance'));
        if ($limit   == 0) $limit   = (float) array_sum(array_column($accounts, 'credit_limit'));

        if ($limit == 0) {
            return $this->factor(0.50, '30%', 'Unknown', 'No credit limit data available.');
        }

        $util = $balance / $limit * 100;
        $raw  = $util <= 10  ? 1.00
              : ($util <= 30  ? 0.88
              : ($util <= 50  ? 0.72
              : ($util <= 75  ? 0.48
              : ($util <= 90  ? 0.28
              : 0.10))));

        $detail = sprintf('₦%s balance / ₦%s limit = %.1f%% utilization',
            number_format($balance, 0), number_format($limit, 0), $util);

        return $this->factor($raw, '30%', $this->ratingLabel($raw), $detail);
    }

    // ─── Factor: History Length (15%) ────────────────────────────────────────

    private function factorHistoryLength(array $accounts): array
    {
        $oldest = null;

        foreach ($accounts as $a) {
            $ds = $a['date_opened'] ?? null;
            if (!$ds) continue;
            try {
                $dt = Carbon::createFromFormat('d/m/Y', $ds);
                if (!$oldest || $dt->lt($oldest)) $oldest = $dt;
            } catch (\Exception $e) {
                // Try Y-m-d fallback
                try {
                    $dt = Carbon::parse($ds);
                    if (!$oldest || $dt->lt($oldest)) $oldest = $dt;
                } catch (\Exception $e2) {}
            }
        }

        if (!$oldest) {
            return $this->factor(0.40, '15%', 'Unknown', 'No account open dates available.');
        }

        $years = $oldest->diffInYears(now());
        $raw   = $years >= 7 ? 1.00
               : ($years >= 5 ? 0.85
               : ($years >= 3 ? 0.68
               : ($years >= 1 ? 0.52
               : 0.32)));

        $detail = "Oldest account: {$oldest->format('M Y')} ({$years} year(s))";

        return $this->factor($raw, '15%', $this->ratingLabel($raw), $detail);
    }

    // ─── Factor: Credit Mix (10%) ─────────────────────────────────────────────

    private function factorCreditMix(array $accounts): array
    {
        if (empty($accounts)) {
            return $this->factor(0.50, '10%', 'No History', 'No accounts found.');
        }

        $types = array_unique(array_filter(array_map(
            fn($a) => strtolower(trim($a['account_type'] ?? '')),
            $accounts
        )));
        $typeCount = count($types);

        $raw    = $typeCount >= 3 ? 1.00 : ($typeCount === 2 ? 0.78 : 0.55);
        $detail = count($accounts) . ' account(s) across ' . $typeCount . ' type(s): ' . implode(', ', $types);

        return $this->factor($raw, '10%', $this->ratingLabel($raw), $detail);
    }

    // ─── Factor: New Credit / Inquiries (10%) ────────────────────────────────

    private function factorNewCredit(array $inquiries, array $summaries): array
    {
        $count = 0;
        foreach ($summaries as $s) {
            $count = max($count, (int) ($s['inquiries_12m'] ?? 0));
        }
        if ($count === 0) $count = count($inquiries);

        $raw = $count === 0 ? 1.00
             : ($count <= 2  ? 0.85
             : ($count <= 4  ? 0.65
             : ($count <= 6  ? 0.45
             : 0.25)));

        $detail = "{$count} enquiry(ies) in the last 12 months";

        return $this->factor($raw, '10%', $this->ratingLabel($raw), $detail);
    }

    // ─── Grade, Recommendation, Helpers ──────────────────────────────────────

    private function grade(int $score): array
    {
        if ($score >= 750) return ['label' => 'Excellent', 'color' => 'emerald', 'description' => 'Very low risk. Eligible for best rates and all standard products.'];
        if ($score >= 700) return ['label' => 'Good',      'color' => 'teal',    'description' => 'Low risk. Eligible for most loan products.'];
        if ($score >= 650) return ['label' => 'Fair',      'color' => 'yellow',  'description' => 'Moderate risk. May qualify with conditions or reduced amount.'];
        if ($score >= 580) return ['label' => 'Poor',      'color' => 'orange',  'description' => 'High risk. Limited credit options; consider collateral.'];
        return               ['label' => 'Very Poor',  'color' => 'red',     'description' => 'Very high risk. Credit extension not recommended.'];
    }

    private function recommend(int $score, array $accounts): array
    {
        $hasLoss  = false;
        $hasDerog = false;

        foreach ($accounts as $a) {
            $cls = strtoupper($a['classification'] ?? '');
            if (in_array($cls, ['LOSS', 'LOST'])) $hasLoss = true;
            if (in_array($cls, ['LOSS', 'LOST', 'DOUBTFUL', 'SUBSTANDARD'])) $hasDerog = true;
        }

        if ($hasLoss || $score < 500) {
            return [
                'decision' => 'DECLINE',
                'color'    => 'red',
                'reason'   => 'Active loss/write-off accounts or score below minimum threshold of 500.',
                'actions'  => ['Do not extend new credit', 'Escalate to collections if existing exposure', 'Request full explanation from applicant'],
            ];
        }
        if ($hasDerog || $score < 580) {
            return [
                'decision' => 'MANUAL REVIEW',
                'color'    => 'orange',
                'reason'   => 'Derogatory accounts present or borderline score. Senior credit officer review required.',
                'actions'  => ['Request 6-month bank statements', 'Require guarantor or collateral', 'Reduce loan amount by 40–50%', 'Shorten tenor to ≤ 12 months'],
            ];
        }
        if ($score < 650) {
            return [
                'decision' => 'CONDITIONAL',
                'color'    => 'yellow',
                'reason'   => 'Fair credit profile. Loan may be approved with conditions.',
                'actions'  => ['Consider 20–30% reduction in requested amount', 'Request 3-month bank statements', 'Shorten tenor by 20%'],
            ];
        }
        if ($score >= 750) {
            return [
                'decision' => 'APPROVE',
                'color'    => 'emerald',
                'reason'   => 'Excellent credit profile. Eligible for standard products at best available rates.',
                'actions'  => ['Proceed with standard underwriting', 'No additional requirements'],
            ];
        }
        return [
            'decision' => 'APPROVE',
            'color'    => 'teal',
            'reason'   => 'Good credit profile. Eligible for standard loan products.',
            'actions'  => ['Proceed with standard underwriting', 'Routine documentation only'],
        ];
    }

    private function factor(float $raw, string $weight, string $label, string $detail): array
    {
        return [
            'raw'    => round($raw, 4),
            'score'  => (int) round($raw * 100),
            'label'  => $label,
            'detail' => $detail,
            'weight' => $weight,
        ];
    }

    private function ratingLabel(float $raw): string
    {
        if ($raw >= 0.90) return 'Excellent';
        if ($raw >= 0.75) return 'Good';
        if ($raw >= 0.55) return 'Fair';
        if ($raw >= 0.35) return 'Poor';
        return 'Very Poor';
    }
}
