<?php

namespace App\Services\Bureau;

/**
 * Precision parser for CreditRegistry (CRC) "Full Credit Report" PDF.
 *
 * Tested against real CRC report format (Obligor Information / Account Summary /
 * Performing Accounts / Closed Accounts / Inquiry History).
 */
class CreditRegistryParser
{
    public function parse(string $text): array
    {
        // Strip repeated page headers to clean text
        $clean = $this->stripPageHeaders($text);

        return [
            'bureau'              => 'crc',
            'report_reference'    => $this->extractReportRef($text),
            'report_date'         => $this->extractReportDate($text),
            'subject'             => $this->parseSubject($clean),
            'summary'             => [],   // filled by factory
            'performance_summary' => $this->parsePerformanceSummary($clean),
            'aggregate_summary'   => $this->parseAggregateSummary($clean),
            'accounts'            => $this->parseAccounts($clean),
            'inquiries'           => $this->parseInquiries($clean),
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function stripPageHeaders(string $text): string
    {
        // Remove repeated bureau header block that appears on each page
        $text = preg_replace('/support@creditregistry\.ng.*?All rights reserved\.\s*Page\d+\|\d+/s', '', $text);
        return trim($text);
    }

    private function extractReportRef(string $text): ?string
    {
        if (preg_match('/Report Reference Number[:\s]+(\d+)/i', $text, $m)) return $m[1];
        return null;
    }

    private function extractReportDate(string $text): ?string
    {
        if (preg_match('/((?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2},\s+\d{4}[.\s]+\d{1,2}:\d{2}\s*(?:AM|PM)?)/i', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    // ─── Subject ──────────────────────────────────────────────────────────────

    private function parseSubject(string $text): array
    {
        $subject = [];

        // Extract obligor section
        if (!preg_match('/Obligor Information\s*(.*?)(?:Account Summary|$)/si', $text, $sec)) {
            // Try without header
            $sec[1] = $text;
        }
        $oblText = $sec[1];

        // Name: first all-caps line(s) in obligor section
        if (preg_match('/^([A-Z][A-Z\s]+?)\s*\n/m', $oblText, $m)) {
            $name = trim($m[1]);
            // CRC sometimes repeats it - just take first occurrence
            if (strlen($name) > 3) {
                $subject['name'] = $name;
            }
        }

        // BVN
        if (preg_match('/BVN\/RC\s*#?\s*(\d{11})/i', $oblText, $m)) {
            $subject['bvn'] = $m[1];
        }

        // DOB — format: "DOB/I 2002-May-18"
        if (preg_match('/DOB\/I\s+(\d{4}-[A-Za-z]+-\d{2})/i', $oblText, $m)) {
            $subject['date_of_birth'] = $m[1];
            // Compute age
            try {
                $dob = \Carbon\Carbon::parse(str_replace('-', ' ', $m[1]));
                $subject['age'] = $dob->age;
            } catch (\Exception $e) {}
        }

        // Phone
        if (preg_match('/Phone\s+([\d]{10,11})/i', $oblText, $m)) {
            $subject['phone'] = $m[1];
        }

        // Address: first non-name, non-blank line(s) before Reg ID / Nigeria
        $oblLines = array_values(array_filter(array_map('trim', explode("\n", $oblText))));
        $addrLines = [];
        $pastName  = false;
        foreach ($oblLines as $ln) {
            // Skip lines that are all-caps (name lines) or look like field labels
            if (preg_match('/^[A-Z\s]{5,}$/', $ln)) { $pastName = true; continue; }
            if (!$pastName) continue;
            if (preg_match('/^(Reg ID|BVN|DOB|Nat ID|Phone|Nigeria)/i', $ln)) break;
            if ($ln && strlen($ln) > 3) $addrLines[] = $ln;
            if (count($addrLines) >= 2) break; // take max 2 address lines
        }
        if ($addrLines) {
            $subject['address'] = implode(', ', $addrLines);
        }

        // Registration ID
        if (preg_match('/Reg ID\s+([\d\-]+)/i', $oblText, $m)) {
            $subject['registration_id'] = $m[1];
        }

        return $subject;
    }

    // ─── Aggregate Summary ────────────────────────────────────────────────────

    private function parseAggregateSummary(string $text): array
    {
        $aggregates = [];
        if (!preg_match('/Aggregate Summary.*?\n(.+?)Performance Summary/si', $text, $sec)) {
            return $aggregates;
        }
        $lines = array_filter(array_map('trim', explode("\n", $sec[1])));
        foreach ($lines as $line) {
            // "Revolving\t1\t0.00\t2,812.00\t4,000.00" or "Revolving  1  0.00  2,812.00  4,000.00"
            if (preg_match('/^([A-Za-z\s\/]+?)\s+(\d+)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})/i', $line, $m)) {
                $aggregates[] = [
                    'type'         => trim($m[1]),
                    'count'        => (int) $m[2],
                    'min_payments' => (float) str_replace(',', '', $m[3]),
                    'balance'      => (float) str_replace(',', '', $m[4]),
                    'limit'        => (float) str_replace(',', '', $m[5]),
                ];
            }
            // Total row
            if (preg_match('/^Total.*?\s+(\d+)\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})\s+([\d,]+\.\d{2})/i', $line, $m)) {
                $aggregates['_total'] = [
                    'count'        => (int) $m[1],
                    'min_payments' => (float) str_replace(',', '', $m[2]),
                    'balance'      => (float) str_replace(',', '', $m[3]),
                    'limit'        => (float) str_replace(',', '', $m[4]),
                ];
            }
        }
        return $aggregates;
    }

    // ─── Performance Summary ──────────────────────────────────────────────────

    private function parsePerformanceSummary(string $text): array
    {
        $perf = [
            'open_accounts'      => 0, 'closed_accounts'    => 0,
            'performing'         => 0, 'notice'             => 0,
            'collection'         => 0, 'litigation'         => 0,
            'judgment'           => 0, 'inquiries_12m'      => 0,
            'delinquent_lt30'    => 0, 'delinquent_30_60'   => 0,
            'derogatory_90'      => 0, 'derogatory_120'     => 0,
            'derogatory_150'     => 0, 'derogatory_180'     => 0,
            'derogatory_360'     => 0, 'non_performing'     => 0,
            'written_off'        => 0, 'inquiries_3m'       => 0,
            'inquiries_36m'      => 0,
        ];

        if (!preg_match('/Performance Summary.*?\n(.*?)(?:Performing Accounts|Closed Accounts|\*\s*Totals)/si', $text, $sec)) {
            return $perf;
        }
        $block = $sec[1];

        $patterns = [
            'open_accounts'   => '/Number of open accounts\s+(\d+)/i',
            'closed_accounts' => '/Number of closed.*?accounts\s+(\d+)/i',
            'performing'      => '/Accounts in good standing.*?\s+(\d+)/i',
            'notice'          => '/Accounts in notice\s+(\d+)/i',
            'collection'      => '/Accounts in collection.*?\s+(\d+)/i',
            'litigation'      => '/Accounts in litigation\s+(\d+)/i',
            'judgment'        => '/Accounts in judgment\s+(\d+)/i',
            'written_off'     => '/Accounts written-off.*?\s+(\d+)/i',
            'delinquent_lt30' => '/Delinquent.*?less than 30.*?\s+(\d+)/i',
            'delinquent_30_60'=> '/Delinquent.*?30 to.*?60.*?\s+(\d+)/i',
            'derogatory_90'   => '/Derogatory.*?90 days.*?\s+(\d+)/i',
            'derogatory_120'  => '/Derogatory.*?120 days.*?\s+(\d+)/i',
            'derogatory_150'  => '/Derogatory.*?150 days.*?\s+(\d+)/i',
            'derogatory_180'  => '/Derogatory.*?180 days.*?\s+(\d+)/i',
            'derogatory_360'  => '/Derogatory.*?360 days.*?\s+(\d+)/i',
            'non_performing'  => '/Non-performing accounts.*?\s+(\d+)/i',
            'inquiries_12m'   => '/Credit inquiries in last 12 months\s+(\d+)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $block, $m)) {
                $perf[$key] = (int) $m[1];
            }
        }

        // Also scan inquiry summary for 3m and 36m
        if (preg_match('/Last 3 months\s+(\d+)/i', $text, $m))  $perf['inquiries_3m']  = (int) $m[1];
        if (preg_match('/Last 36 months\s+(\d+)/i', $text, $m)) $perf['inquiries_36m'] = (int) $m[1];

        return $perf;
    }

    // ─── Accounts / Tradelines ────────────────────────────────────────────────

    private function parseAccounts(string $text): array
    {
        $accounts = [];

        // Process Performing and Closed sections
        foreach (['performing', 'closed'] as $section) {
            $sectionAccts = $this->parseTradelines($text, $section);
            $accounts = array_merge($accounts, $sectionAccts);
        }

        return $accounts;
    }

    private function parseTradelines(string $text, string $section): array
    {
        $accounts = [];

        // Extract the section
        if ($section === 'performing') {
            // Between "Performing Accounts" section and "Closed Accounts" or end
            if (!preg_match('/Performing Accounts\s*\n\s*\d+.*?(?=Creditor\s*\/\s*Account Number)(.*?)(?=Closed Accounts|\z)/si', $text, $sec)) {
                return [];
            }
        } else {
            // Between "Closed Accounts" and "Inquiry History" or end
            if (!preg_match('/Closed Accounts\s*\n\s*\d+.*?(?=Creditor\s*\/\s*Account Number)(.*?)(?=Inquiry History|\z)/si', $text, $sec)) {
                return [];
            }
        }

        $block = $sec[1];

        // Each tradeline starts with \n(\d+)(InstitutionName) — e.g., "1OPay Microfinance"
        // Split on numbered entries
        $parts = preg_split('/\n(?=\d+[A-Z])/m', trim($block));

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $acct = $this->parseSingleTradeline($part, $section);
            if ($acct && !empty($acct['account_number'])) {
                $accounts[] = $acct;
            }
        }

        return $accounts;
    }

    private function parseSingleTradeline(string $block, string $section): ?array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block))));
        if (count($lines) < 3) return null;

        // Line 0: "1OPay Microfinance" — strip leading number
        $institutionLines = [];
        $line0 = preg_replace('/^\d+/', '', $lines[0]);
        $institutionLines[] = trim($line0);

        // Collect institution name lines until we hit a line that looks like an account number
        $i = 1;
        while ($i < count($lines)) {
            $l = $lines[$i];
            // Account number: all digits, underscores, no spaces, min 5 chars
            if (preg_match('/^[\d_]{5,}$/', $l)) {
                break;
            }
            // If line ends with "/" it's the start of slash-delimited values (obligor line)
            if (str_ends_with($l, '/')) break;
            $institutionLines[] = $l;
            $i++;
        }

        $institution = implode(' ', $institutionLines);

        // Account number line
        $accountNumber = null;
        if ($i < count($lines) && preg_match('/^[\d_]{5,}$/', $lines[$i])) {
            $accountNumber = $lines[$i];
            $i++;
        }

        // Remaining lines are slash-delimited field values
        // Collect all slash-terminated values
        $remaining = implode("\n", array_slice($lines, $i));
        $slashParts = preg_split('/\s*\/\s*\n/', $remaining . "\n");
        $fields = array_map('trim', $slashParts);

        // Fields index mapping (per CRC column header order):
        // 0: obligors
        // 1: date_opened
        // 2: date_first_reported
        // 3: date_last_updated
        // 4: type
        // 5: purpose
        // 6: term
        // 7: payment_cycle
        // 8: last_payment (NGN amount)
        // 9: last_balance / outstanding
        // 10: account_status
        // 11: legal_status
        // 12: last_payment_date
        // 13: last_balance_date
        // 14: account_status_date
        // 15: legal_status_date
        // 16: term_or_min_payment
        // 17: credit_amount_or_limit

        $get = fn(int $idx) => trim($fields[$idx] ?? '');
        $money = function(string $val): float {
            $val = preg_replace('/NGN\s*/', '', $val);
            $val = str_replace(',', '', trim($val));
            return is_numeric($val) ? (float) $val : 0.0;
        };

        $accountStatus = $get(10);
        $status = $this->resolveStatus($accountStatus, $section);
        $classification = $this->resolveClassification($accountStatus, $section);

        return [
            'institution'        => trim($institution),
            'account_number'     => $accountNumber,
            'obligors'           => $get(0),
            'date_opened'        => $get(1),
            'date_first_reported'=> $get(2),
            'date_last_updated'  => $get(3),
            'account_type'       => $get(4) ?: ($section === 'closed' ? 'Loan' : 'Loan'),
            'purpose'            => $get(5),
            'term'               => $get(6),
            'payment_cycle'      => $get(7),
            'last_payment'       => $money($get(8)),
            'outstanding_balance'=> $money($get(9)),
            'account_status'     => $accountStatus,
            'legal_status'       => $get(11),
            'last_payment_date'  => $get(12),
            'last_balance_date'  => $get(13),
            'account_status_date'=> $get(14),
            'legal_status_date'  => $get(15),
            'min_payment'        => $money($get(16)),
            'credit_limit'       => $money($get(17)),
            'status'             => $status,
            'classification'     => $classification,
            'dpd'                => 0,
            'overdue_amount'     => 0.0,
            'section'            => $section,
        ];
    }

    private function resolveStatus(string $accountStatus, string $section): string
    {
        $s = strtolower($accountStatus);
        if (str_contains($s, 'paid') || str_contains($s, 'closed') || $section === 'closed') return 'closed';
        if (str_contains($s, 'performing') || str_contains($s, 'good')) return 'performing';
        if (str_contains($s, 'loss') || str_contains($s, 'doubt') || str_contains($s, 'substandard') || str_contains($s, 'overdue')) return 'non_performing';
        return 'performing';
    }

    private function resolveClassification(string $accountStatus, string $section): string
    {
        $s = strtolower($accountStatus);
        if (str_contains($s, 'paid') || str_contains($s, 'closed')) return 'CLOSED';
        if (str_contains($s, 'loss') || str_contains($s, 'lost'))   return 'LOSS';
        if (str_contains($s, 'doubtful'))    return 'DOUBTFUL';
        if (str_contains($s, 'substandard')) return 'SUBSTANDARD';
        if (str_contains($s, 'watchlist') || str_contains($s, 'notice')) return 'WATCHLIST';
        if (str_contains($s, 'performing'))  return 'PERFORMING';
        if ($section === 'closed') return 'CLOSED';
        return 'PERFORMING';
    }

    // ─── Inquiries ────────────────────────────────────────────────────────────

    private function parseInquiries(string $text): array
    {
        $inquiries = [];

        if (!preg_match('/Inquiry History.*?\n(.*?)(?:Inquiry Summary|Creditor Information|\z)/si', $text, $sec)) {
            return [];
        }

        $block = $sec[1];
        $lines = array_filter(array_map('trim', explode("\n", $block)));

        foreach ($lines as $line) {
            // Real CRC format: "12025-Oct-30PersonalLoan\tLittleant Ltd\t09162562626"
            // Row number is a digit immediately before the 4-digit year — strip it
            // Pattern: optional_row_num + YYYY-Mon-DD + reason + \t + institution + \t + phone
            if (!preg_match('/^\d*(\d{4}-[A-Za-z]+-\d{2})(.*)$/s', $line, $dm)) {
                continue;
            }

            $date      = $dm[1];
            $remainder = $dm[2]; // "PersonalLoan\tLittleant Ltd\t09162562626"

            // Split remainder by tab
            $parts = explode("\t", $remainder);
            $reason      = trim($parts[0] ?? '');
            $institution = trim($parts[1] ?? '');
            $phone       = trim($parts[2] ?? '');

            if (!$institution) continue;

            $inquiries[] = compact('date', 'reason', 'institution', 'phone');
        }

        return $inquiries;
    }
}
