<?php

namespace App\Services\Bureau;

/**
 * Auto-detects bureau from PDF text and dispatches to the correct parser.
 * Enriches the parsed result with computed summary fields.
 */
class BureauParserFactory
{
    /**
     * Auto-detect bureau and parse.
     */
    public static function parse(string $text): array
    {
        $bureau = self::detect($text);

        $parsed = match($bureau) {
            'firstcentral' => (new FirstCentralParser())->parse($text),
            'crc'          => (new CreditRegistryParser())->parse($text),
            default        => (new GenericBureauParser())->parse($text, $bureau),
        };

        $parsed['summary'] = self::buildSummary($parsed);

        return $parsed;
    }

    public static function detect(string $text): string
    {
        $lower = strtolower($text);

        if (str_contains($lower, 'firstcentral') || str_contains($lower, 'first central') || str_contains($lower, 'detailed credit profile')) {
            return 'firstcentral';
        }
        if (str_contains($lower, 'creditregistry') || str_contains($lower, 'credit registry') || str_contains($lower, 'crc credit bureau') || str_contains($lower, 'full\ncreditreport') || str_contains($lower, 'full creditreport') || str_contains($lower, 'creditregistry.ng')) {
            return 'crc';
        }
        if (str_contains($lower, 'xds') || str_contains($lower, 'creditinfo')) {
            return 'xds';
        }

        return 'unknown';
    }

    private static function buildSummary(array $parsed): array
    {
        $accounts    = $parsed['accounts']    ?? [];
        $perf        = $parsed['performance_summary'] ?? [];
        $aggregates  = $parsed['aggregate_summary']   ?? [];
        $inquiries   = $parsed['inquiries']   ?? [];

        // Account counts — prefer parsed performance_summary, fallback to counting accounts array
        $totalAccts  = ($perf['open_accounts']   ?? 0) + ($perf['closed_accounts'] ?? 0);
        $totalAccts  = $totalAccts ?: count($accounts);
        $activeAccts = $perf['open_accounts']   ?? count(array_filter($accounts, fn($a) => in_array($a['status'] ?? '', ['performing', 'non_performing'])));
        $closedAccts = $perf['closed_accounts'] ?? count(array_filter($accounts, fn($a) => ($a['status'] ?? '') === 'closed'));

        // Balances from aggregate summary or accounts
        $totalBalance = 0; $totalLimit = 0;
        if (!empty($aggregates)) {
            foreach ($aggregates as $k => $agg) {
                if ($k === '_total') { continue; }
                if (is_array($agg)) {
                    $totalBalance += $agg['balance'] ?? 0;
                    $totalLimit   += $agg['limit']   ?? 0;
                }
            }
            if (isset($aggregates['_total'])) {
                $totalBalance = $aggregates['_total']['balance'] ?? $totalBalance;
                $totalLimit   = $aggregates['_total']['limit']   ?? $totalLimit;
            }
        }
        if ($totalBalance === 0) {
            $totalBalance = array_sum(array_column($accounts, 'outstanding_balance'));
        }
        if ($totalLimit === 0) {
            $totalLimit = array_sum(array_column($accounts, 'credit_limit'));
        }

        // Overdue
        $totalOverdue = array_sum(array_column($accounts, 'overdue_amount'));

        // Max DPD
        $maxDpd = empty($accounts) ? 0 : max(array_column($accounts, 'dpd'));

        // Derogatory count
        $derogCount = count(array_filter($accounts, fn($a) => ($a['status'] ?? '') === 'non_performing'));
        $derogCount = max($derogCount, ($perf['derogatory_90'] ?? 0) + ($perf['derogatory_120'] ?? 0) + ($perf['derogatory_180'] ?? 0) + ($perf['derogatory_360'] ?? 0));

        // Worst classification
        $worstStatus = null;
        foreach ($accounts as $a) {
            $cls = strtoupper($a['classification'] ?? '');
            if (in_array($cls, ['LOSS', 'LOST']))         { $worstStatus = 'LOSS'; break; }
            if ($cls === 'DOUBTFUL' && $worstStatus !== 'LOSS') $worstStatus = 'DOUBTFUL';
            if (in_array($cls, ['SUBSTANDARD', 'SUB-STANDARD']) && !in_array($worstStatus, ['LOSS','DOUBTFUL'])) $worstStatus = 'SUBSTANDARD';
            if (in_array($cls, ['WATCHLIST', 'WATCH LIST']) && !in_array($worstStatus, ['LOSS','DOUBTFUL','SUBSTANDARD'])) $worstStatus = 'WATCHLIST';
        }
        if (!$worstStatus && ($perf['derogatory_360'] ?? 0) > 0) $worstStatus = 'LOSS';
        if (!$worstStatus && ($perf['derogatory_180'] ?? 0) > 0) $worstStatus = 'DOUBTFUL';
        if (!$worstStatus && ($perf['derogatory_90']  ?? 0) > 0) $worstStatus = 'SUBSTANDARD';
        if (!$worstStatus && $derogCount === 0) $worstStatus = ($closedAccts > 0 && $activeAccts === 0) ? 'CLOSED' : 'PERFORMING';

        // Credit utilization
        $utilization = ($totalLimit > 0) ? round($totalBalance / $totalLimit * 100, 1) : 0;

        // Inquiry count
        $inquiries12m = $perf['inquiries_12m'] ?? count($inquiries);
        $inquiries3m  = $perf['inquiries_3m']  ?? 0;
        $inquiries36m = $perf['inquiries_36m'] ?? count($inquiries);

        // Risk level
        $riskLevel = self::computeRisk($maxDpd, $derogCount, $worstStatus ?? '', $utilization);

        return [
            'total_accounts'        => $totalAccts,
            'active_accounts'       => $activeAccts,
            'closed_accounts'       => $closedAccts,
            'total_balance'         => $totalBalance,
            'total_credit_limit'    => $totalLimit,
            'credit_utilization'    => $utilization,
            'total_overdue'         => $totalOverdue,
            'max_dpd'               => $maxDpd,
            'credit_score'          => $parsed['summary']['credit_score'] ?? null,
            'worst_status'          => $worstStatus,
            'derogatory_count'      => $derogCount,
            'risk_level'            => $riskLevel,
            'inquiries_3m'          => $inquiries3m,
            'inquiries_12m'         => $inquiries12m,
            'inquiries_36m'         => $inquiries36m,
        ];
    }

    private static function computeRisk(int $maxDpd, int $derogCount, string $worst, float $utilization): string
    {
        if (in_array($worst, ['LOSS', 'LOST', 'DOUBTFUL']) || $maxDpd >= 180 || ($derogCount >= 2)) return 'high';
        if ($worst === 'SUBSTANDARD' || $maxDpd >= 90 || $derogCount >= 1 || $utilization >= 80) return 'medium';
        if ($worst === 'WATCHLIST'   || $maxDpd >= 30 || $utilization >= 60) return 'caution';
        return 'low';
    }
}
