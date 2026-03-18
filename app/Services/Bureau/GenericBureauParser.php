<?php

namespace App\Services\Bureau;

/**
 * Fallback parser for unknown bureau PDFs.
 * Attempts best-effort extraction of common credit report fields.
 */
class GenericBureauParser
{
    public function parse(string $text, string $bureau = 'unknown'): array
    {
        return [
            'bureau'   => $bureau,
            'subject'  => $this->parseSubject($text),
            'summary'  => $this->parseSummary($text),
            'accounts' => [],
        ];
    }

    private function parseSubject(string $text): array
    {
        $subject = [];
        if (preg_match('/BVN[:\s]+(\d{11})/i', $text, $m)) {
            $subject['bvn'] = $m[1];
        }
        if (preg_match('/(?:Full Name|Consumer Name|Name)[:\s]+([A-Z][A-Za-z ,\'-]+?)(?:\n|BVN)/is', $text, $m)) {
            $subject['name'] = trim($m[1]);
        }
        if (preg_match('/(?:Date of Birth|DOB)[:\s]+([\d]{2}[\/\-][\d]{2}[\/\-][\d]{4})/i', $text, $m)) {
            $subject['date_of_birth'] = $m[1];
        }
        if (preg_match('/Gender[:\s]+(Male|Female)/i', $text, $m)) {
            $subject['gender'] = $m[1];
        }
        return $subject;
    }

    private function parseSummary(string $text): array
    {
        $summary = [
            'total_accounts'  => 0,
            'active_accounts' => 0,
            'closed_accounts' => 0,
            'total_balance'   => 0,
            'total_overdue'   => 0,
            'max_dpd'         => 0,
            'credit_score'    => null,
            'worst_status'    => null,
        ];

        if (preg_match('/(?:Credit\s+)?Score[:\s]+(\d{3,4})/i', $text, $m)) {
            $summary['credit_score'] = (int) $m[1];
        }
        preg_match_all('/(\d+)\s*DPD/i', $text, $matches);
        if (!empty($matches[1])) {
            $summary['max_dpd'] = max(array_map('intval', $matches[1]));
        }

        return $summary;
    }
}
