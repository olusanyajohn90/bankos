<?php

namespace App\Services\Bureau;

/**
 * Precision parser for FirstCentral "Detailed Credit Profile Report" PDF.
 *
 * FirstCentral PDFs have a peculiar extraction pattern where table columns are
 * concatenated as "ValueLabel" (value precedes its own label) due to right-to-left
 * reading order in their multi-column layout. Empty cells appear as "<>".
 *
 * Tested against real FC report for Ayodele Ebenezer.
 */
class FirstCentralParser
{
    public function parse(string $text): array
    {
        $clean = $this->stripPageHeaders($text);

        return [
            'bureau'              => 'firstcentral',
            'report_reference'    => $this->extractRef($text),
            'report_date'         => $this->extractDate($text),
            'subject'             => $this->parseSubject($clean),
            'summary'             => [],   // filled by factory
            'performance_summary' => $this->parsePerformanceSummary($clean),
            'aggregate_summary'   => [],
            'accounts'            => $this->parseAccounts($clean),
            'inquiries'           => $this->parseInquiries($clean),
        ];
    }

    // ─── Header cleaning ──────────────────────────────────────────────────────

    private function stripPageHeaders(string $text): string
    {
        // Remove repeated FC page header blocks
        $text = preg_replace(
            '/Detailed Credit Profile Report\s*No 37\/37A Raymond Njoku.*?Email\s*:.*?firstcentralcreditbureau\.com/si',
            '',
            $text
        );
        $text = preg_replace('/Page \d+ of \d+ pages.*?Report Extracted on:.*?\n/i', '', $text);
        return trim($text);
    }

    private function extractRef(string $text): ?string
    {
        // "18045096FirstCentral Reference Number" or "FirstCentral Ref No."
        if (preg_match('/(\d{7,12})FirstCentral Ref(?:erence)? No\.?/i', $text, $m)) return $m[1];
        if (preg_match('/FirstCentral Reference Number\s*\n(\d+)/i', $text, $m))      return $m[1];
        return null;
    }

    private function extractDate(string $text): ?string
    {
        // "10/03/2026 03:01 PMEnquiry Date" or "Report Extracted on: 10-03-2026 15:02:04"
        if (preg_match('/Report Extracted on:\s*([\d\-]+\s+[\d:]+)/i', $text, $m)) return trim($m[1]);
        if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4}\s+[\d:]+\s*(?:AM|PM)?)Enquiry Date/i', $text, $m)) return trim($m[1]);
        return null;
    }

    // ─── Subject ──────────────────────────────────────────────────────────────

    private function parseSubject(string $text): array
    {
        $subject = [];

        // FC format: "ValueLabel" concatenated
        // BVN + First Name: "22561761602Bank Verification Number(BVN)EbenezerFirst Name"
        if (preg_match('/(\d{11})Bank Verification Number/i', $text, $m)) {
            $subject['bvn'] = $m[1];
        }

        // First name
        if (preg_match('/([A-Za-z][A-Za-z\s\-\']{1,30})First Name/i', $text, $m)) {
            $subject['first_name'] = trim($m[1]);
        }

        // Surname — format: "AyodeleSurname". The text has "<>Other NamesAyodeleSurname"
        // so we capture only the capitalised word directly before the label (no spaces, starts uppercase)
        if (preg_match('/(?<![a-z])([A-Z][a-z\-\']+)Surname/i', $text, $m)) {
            $subject['surname'] = trim($m[1]);
        }

        // Compose full name
        if (!empty($subject['surname']) || !empty($subject['first_name'])) {
            $subject['name'] = trim(($subject['surname'] ?? '') . ' ' . ($subject['first_name'] ?? ''));
        }

        // DOB: "18/05/2002Date of Birth"
        if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4})Date of Birth/i', $text, $m)) {
            $subject['date_of_birth'] = $m[1];
            try {
                $subject['age'] = \Carbon\Carbon::createFromFormat('d/m/Y', $m[1])->age;
            } catch (\Exception $e) {}
        }

        // Gender: "MaleGender" or "FemaleGender"
        if (preg_match('/(Male|Female)Gender/i', $text, $m)) {
            $subject['gender'] = $m[1];
        }

        // Phone: "09064595119Mobile Number"
        if (preg_match('/(0[\d]{9,10})Mobile Number/i', $text, $m)) {
            $subject['phone'] = $m[1];
        }

        // Address: Latest Residential Address block
        if (preg_match('/Latest Residential Address\s*\n(.*?)(?:Latest Postal|Nationality|NigeriaNationality|<>Unique Tracking|\z)/si', $text, $addr)) {
            $addrClean = preg_replace('/<>|\n+/', ' ', $addr[1]);
            $addrClean = trim(preg_replace('/\s{2,}/', ' ', $addrClean));
            if (strlen($addrClean) > 3) {
                $subject['address'] = $addrClean;
            }
        }

        // FC Reference Number: "18045096FirstCentral Reference Number"
        if (preg_match('/(\d+)FirstCentral Reference Number/i', $text, $m)) {
            $subject['fc_reference'] = $m[1];
        }

        // Employer (if any): "CompanyNameCurrent Employer"
        if (preg_match('/([A-Z][A-Za-z\s&.,\'-]{3,60})Current Employer/i', $text, $m)) {
            $emp = trim($m[1]);
            if ($emp && $emp !== '<>') $subject['employer'] = $emp;
        }

        return $subject;
    }

    // ─── Performance Summary ──────────────────────────────────────────────────

    private function parsePerformanceSummary(string $text): array
    {
        $perf = [
            'open_accounts'      => 0,
            'closed_accounts'    => 0,
            'performing'         => 0,
            'non_performing'     => 0,
            'in_arrears'         => 0,
            'dishonoured_cheques'=> 0,
            'judgments'          => 0,
            'written_off'        => 0,
            'inquiries_12m'      => 0,
        ];

        // FC uses "ValueLabel" pattern but often with two values merged: "01Label" = [0, 1] (US$, NGN columns)
        // We take the NGN (last digit group) for the count
        $extract = function(string $label, string $text): int {
            if (preg_match('/(\d+)' . preg_quote($label, '/') . '/i', $text, $m)) {
                // Could be "01" — take the last digit = NGN column
                $raw = $m[1];
                return (int) substr($raw, -1); // last char is the NGN figure
            }
            return 0;
        };

        $perf['open_accounts']   = $extract('Total Number of Accounts Taken', $text);
        $perf['performing']      = $extract('Total Number of Accounts in Good Standing', $text);
        $perf['in_arrears']      = $extract('Total Number of Accounts in Arrears', $text);
        $perf['non_performing']  = $perf['in_arrears']; // FC: in arrears ≡ non-performing
        $perf['judgments']       = $extract('Total Number of Judgements', $text);
        $perf['dishonoured_cheques'] = $extract('Total Number of Dishonoured Cheques', $text);

        // Overdraft/Other Loans count: "10Total Number of Overdraft/Other Loans" = 0 NP + 1 P
        $perf['overdraft_count'] = $extract('Total Number of Overdraft/Other Loans', $text);
        $perf['credit_card_count'] = $extract('Total Number of Credit Cards', $text);
        $perf['personal_loan_count'] = $extract('Total Number of Personal Loans/Facilities', $text);
        $perf['mortgage_count']  = $extract('Total Number of Home Loans/Building/Mortgage Facilities', $text);
        $perf['auto_count']      = $extract('Total Number of Auto Loans/Facilities', $text);

        // Enquiry count from "Enquiry History in Last 12 Months"
        $inqSection = '';
        if (preg_match('/Enquiry History in Last 12 Months(.*?)(?:Identification|Address History|\z)/si', $text, $sec)) {
            $inqSection = $sec[1];
            $lines = array_filter(array_map('trim', explode("\n", $inqSection)));
            $perf['inquiries_12m'] = count(array_filter($lines, fn($l) => stripos($l, 'Application for credit') !== false || stripos($l, 'Enquiry') !== false));
            // Actually count lines with institution names (non-header, non-empty)
        }
        // Fallback: count from enquiry input at top
        if ($perf['inquiries_12m'] === 0 && stripos($text, 'Enquiry Date') !== false) {
            $perf['inquiries_12m'] = 1;
        }

        return $perf;
    }

    // ─── Accounts ─────────────────────────────────────────────────────────────

    private function parseAccounts(string $text): array
    {
        $accounts = [];

        // Step 1: Get the high-level delinquency info from the top section
        // "106Days in Arrears", "20220531Year/Month", "20220209192523618Account Number", "LCreditSubscriber Name"
        $topDpd = 0; $topAccount = null; $topInstitution = null;
        if (preg_match('/(\d+)Days in Arrears/i', $text, $m))    $topDpd         = (int) $m[1];
        if (preg_match('/(\d{15,20})Account Number/i', $text, $m)) $topAccount   = $m[1];
        if (preg_match('/([A-Za-z][A-Za-z0-9\s&.,\'-]{1,40})Subscriber Name/i', $text, $m)) {
            $topInstitution = trim($m[1]);
        }

        // Step 2: Parse the Credit Agreements Summary table (page 3 block)
        // Row pattern: Open|Closed + Classification + ArrearAmt + <> + Outstanding + AvailedLimit + NGN + AccountNumber + SubscriberName
        // e.g.: "OpenLost38.00<>38.009,000.00NGN20220209192523618LCredit"
        $summaryAccounts = [];
        if (preg_match('/Credit Agreements Summary\s*\n.*?Account\s*Status\s*\n?(.*?)(?:Page \d+|\z)/si', $text, $sec)) {
            $block = $sec[1];
            // Row format (right-to-left columns extracted left-to-right):
            // "OpenLost38.00<>38.009,000.00NGN20220209192523618LCredit\n"
            //  Status + Classification + ArrearAmt + Instalment(<>) + Outstanding + AvailedLimit + NGN + AcctNo + Subscriber
            // Amounts end at .XX — use ([\d,]+\.\d{2}) to avoid greedy overlap
            $rowPattern = '/(Open|Closed|Active)\s*(LOST|PERFORMING|SUBSTANDARD|DOUBTFUL|WATCHLIST)\s*([\d,]+\.\d{2})\s*(?:<>\s*)?([\d,]+\.\d{2})\s*([\d,]+\.\d{2})\s*NGN\s*(\d{10,20})\s*([A-Za-z][A-Za-z0-9\s&.,\'-]{1,40}?)(?:\n|$)/im';
            if (preg_match_all($rowPattern, $block, $rows, PREG_SET_ORDER)) {
                foreach ($rows as $row) {
                    $summaryAccounts[] = [
                        'account_status'     => trim($row[1]),
                        'classification'     => strtoupper(trim($row[2])),
                        'arrear_amount'      => (float) str_replace(',', '', $row[3]),
                        'outstanding_balance'=> (float) str_replace(',', '', $row[4]),
                        'credit_limit'       => (float) str_replace(',', '', $row[5]),
                        'account_number'     => trim($row[6]),
                        'institution'        => trim($row[7]),
                    ];
                }
            }
        }

        // Step 3: Parse per-account detail blocks from "Credit Agreements" section
        // Each block is headed: "Details of Credit Agreement with "..." for Account Number: ..."
        $detailBlocks = [];
        preg_match_all('/Details of Credit Agreement with "([^"]+)" for Account Number:\s*([\w]+)\s*(.*?)(?=Details of Credit Agreement with|Page \d+|\z)/si', $text, $dBlocks, PREG_SET_ORDER);
        foreach ($dBlocks as $db) {
            $detailBlocks[$db[2]] = ['institution' => $db[1], 'block' => $db[3]];
        }

        // Step 4: Parse 24-month payment history per account
        // History appears as two rows of 12 month labels + 12 status codes
        // "2025 APR2025 MAY...\nNDNDND..."
        $historyByAccount = [];
        if (!empty($detailBlocks)) {
            foreach ($detailBlocks as $accNum => $detail) {
                $historyByAccount[$accNum] = $this->parsePaymentHistory($detail['block']);
            }
        }

        // Step 5: Merge summary + detail into account records
        if (!empty($summaryAccounts)) {
            foreach ($summaryAccounts as $sa) {
                $accNum  = $sa['account_number'];
                $detail  = $detailBlocks[$accNum] ?? null;
                $history = $historyByAccount[$accNum] ?? [];

                // Extract detail fields from "ValueLabel" block.
                // IMPORTANT: In FC PDFs, field values (dates, balances) are extracted by smalot/pdfparser
                // BEFORE the "Details of Credit Agreement" section header due to right-to-left column order.
                // We therefore build a search block that includes the text immediately preceding the header
                // as well as the text following it (the post-header block containing payment history).
                $dateOpened    = null; $expiry = null; $lastPayment = null;
                $term = null; $payFreq = null; $bureauUpdated = null;
                if ($detail) {
                    $postBlock = $detail['block'];
                    // Build pre-header chunk: up to 3 000 chars before this account's detail header
                    $headerPat = '/Details of Credit Agreement with "[^"]+" for Account Number:\s*'
                                . preg_quote($accNum, '/') . '/si';
                    $preBlock = '';
                    if (preg_match($headerPat, $text, $hm, PREG_OFFSET_CAPTURE)) {
                        $headerPos = $hm[0][1];
                        $preStart  = max(0, $headerPos - 3000);
                        $preBlock  = substr($text, $preStart, $headerPos - $preStart);
                    }
                    $b = $preBlock . $postBlock; // search both sides of the header

                    // FC "ValueLabel" format: value immediately precedes its label
                    if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4})Effective Date/i',          $b, $m)) $dateOpened  = $m[1];
                    if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4})Expiry Date/i',              $b, $m)) $expiry      = $m[1];
                    if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4})Last Payment Date/i',        $b, $m)) $lastPayment = $m[1];
                    if (preg_match('/([\d]{2}\/[\d]{2}\/[\d]{4})Bureau Updated Date/i',      $b, $m)) $bureauUpdated = $m[1];
                    if (preg_match('/(\d+\s*(?:Day|Month|Week)\(s\))Loan Duration/i',        $b, $m)) $term = $m[1];
                    if (preg_match('/([A-Za-z0-9\s\-]+)Repayment\s*Frequency/i',             $b, $m)) $payFreq = trim($m[1]);
                    // Balance and limit override summary values if present
                    if (preg_match('/([\d,]+\.\d{2})Current Balance/i',                      $b, $m)) {
                        $sa['outstanding_balance'] = (float) str_replace(',', '', $m[1]);
                    }
                    if (preg_match('/([\d,]+\.\d{2})Loan Amount\/Credit Limit/i',            $b, $m)) {
                        $sa['credit_limit'] = (float) str_replace(',', '', $m[1]);
                    }
                    if (preg_match('/([\d,]+\.\d{2})Arrear Amount/i',                        $b, $m)) {
                        $sa['arrear_amount'] = (float) str_replace(',', '', $m[1]);
                    }
                }

                $cls    = $sa['classification'];
                $status = $this->resolveStatus($sa['account_status'], $cls);

                $accounts[] = [
                    'institution'         => $sa['institution'],
                    'account_number'      => $accNum,
                    'account_type'        => 'Overdraft/Other Loan',
                    'date_opened'         => $dateOpened,
                    'expiry_date'         => $expiry,
                    'last_payment_date'   => $lastPayment,
                    'bureau_updated_date' => $bureauUpdated,
                    'account_status'      => $sa['account_status'],
                    'classification'      => $cls,
                    'outstanding_balance' => $sa['outstanding_balance'],
                    'credit_limit'        => $sa['credit_limit'],
                    'overdue_amount'      => $sa['arrear_amount'],
                    'amount_availed'      => $sa['credit_limit'],
                    'min_payment'         => 0,
                    'last_payment'        => 0,
                    'term'                => $term,
                    'payment_cycle'       => $payFreq,
                    'dpd'                 => ($status === 'non_performing') ? $topDpd : 0,
                    'status'              => $status,
                    'section'             => $status === 'closed' ? 'closed' : ($status === 'performing' ? 'performing' : 'non_performing'),
                    'payment_history'     => $history,
                ];
            }
        } elseif ($topInstitution && $topAccount) {
            // Fallback: build from top-level delinquency data only
            $topCls = $topDpd >= 360 ? 'LOSS' : ($topDpd >= 180 ? 'DOUBTFUL' : ($topDpd >= 90 ? 'SUBSTANDARD' : ($topDpd > 0 ? 'WATCHLIST' : 'PERFORMING')));
            $accounts[] = [
                'institution'         => $topInstitution,
                'account_number'      => $topAccount,
                'account_type'        => 'Loan',
                'date_opened'         => null,
                'expiry_date'         => null,
                'last_payment_date'   => null,
                'account_status'      => $topDpd > 0 ? 'Open' : 'Open',
                'classification'      => $topCls,
                'outstanding_balance' => 0,
                'credit_limit'        => 0,
                'overdue_amount'      => 0,
                'amount_availed'      => 0,
                'min_payment'         => 0,
                'last_payment'        => 0,
                'dpd'                 => $topDpd,
                'status'              => $this->resolveStatus('Open', $topCls),
                'section'             => 'non_performing',
                'payment_history'     => [],
            ];
        }

        return $accounts;
    }

    private function resolveStatus(string $accountStatus, string $classification): string
    {
        $cls = strtoupper($classification);
        $st  = strtolower($accountStatus);
        if (str_contains($st, 'closed') || $cls === 'CLOSED') return 'closed';
        if (in_array($cls, ['LOSS', 'LOST', 'DOUBTFUL', 'SUBSTANDARD'])) return 'non_performing';
        if ($cls === 'WATCHLIST') return 'non_performing';
        return 'performing';
    }

    private function parsePaymentHistory(string $block): array
    {
        $history = [];
        $lines   = array_values(array_filter(array_map('trim', explode("\n", $block))));

        for ($i = 0; $i < count($lines) - 1; $i++) {
            $line = $lines[$i];
            $next = $lines[$i + 1];

            // Detect a line of 12 concatenated month labels: "2024 APR2024 MAY..."
            if (!preg_match('/^(\d{4}\s+[A-Z]{3}){4,}/', $line)) continue;

            // Extract month tokens: e.g. "2025 APR"
            preg_match_all('/(\d{4}\s+[A-Z]{3})/', $line, $months);
            $monthList = $months[1];

            // Next line has status codes: "NDNDND..." or "OK OK ND..."
            preg_match_all('/(ND|OK|\d{1,2})/', $next, $statuses);
            $statusList = $statuses[1];

            foreach ($monthList as $j => $month) {
                $history[] = [
                    'month'  => trim($month),
                    'status' => $statusList[$j] ?? 'ND',
                ];
            }
        }

        // Sort oldest to newest
        usort($history, fn($a, $b) => strcmp($a['month'], $b['month']));

        return $history;
    }

    // ─── Inquiries ────────────────────────────────────────────────────────────

    private function parseInquiries(string $text): array
    {
        $inquiries = [];

        // From the top "Enquiry Input Details" section — the subscriber who pulled the report
        // "LINKS MICROFINANCE BANK LTDSubscriber Name"
        // "Application for credit by a borrower.Enquiry Reason"
        // "10/03/2026 03:01 PMEnquiry Date"
        $enquirerName = null; $enquiryReason = null; $enquiryDate = null;
        // Search for Subscriber Name specifically within the Enquiry Input Details section
        // to avoid matching "LCreditSubscriber Name" from the Credit Agreements Summary table.
        $enquiryInputSection = '';
        if (preg_match('/Enquiry Input Details\s*\n(.*?)(?=Summary of Performance|Credit Agreements|Personal Information|Identification History|\z)/si', $text, $sec)) {
            $enquiryInputSection = $sec[1];
        }
        // Pattern: institution names in FC are ALL-CAPS and contain no digits.
        // Removing the `i` flag and digits from the char class prevents matching
        // garbage like "Month 20220209192523618Account Number LCredit".
        $subscriberSearch = $enquiryInputSection ?: $text;
        if (preg_match('/([A-Z][A-Z\s&.,\'-]{5,60})Subscriber Name/', $subscriberSearch, $m)) {
            $enquirerName = trim($m[1]);
        }
        if (preg_match('/(.{5,80})Enquiry Reason/i', $text, $m)) {
            $enquiryReason = trim($m[1]);
        }
        if (preg_match('/([\d\/]{10}[\s\d:APM]+)Enquiry Date/i', $text, $m)) {
            $enquiryDate = trim($m[1]);
        }

        if ($enquirerName) {
            $inquiries[] = [
                'date'        => $enquiryDate ?? '',
                'reason'      => $enquiryReason ?? 'Credit application',
                'institution' => $enquirerName,
                'phone'       => '',
            ];
        }

        // Also parse "Enquiry History in Last 12 Months" section for older inquiries
        if (preg_match('/Enquiry History in Last 12 Months\s*\n.*?\n(.*?)(?:Identification History|\z)/si', $text, $sec)) {
            $lines = array_filter(array_map('trim', explode("\n", $sec[1])));
            // Skip header line: "Reason for EnquiryEnquirer Phone NumberName of EnquirerEnquiry Date"
            foreach ($lines as $line) {
                if (stripos($line, 'Reason for Enquiry') !== false) continue;
                if (strlen($line) < 5) continue;
                // Format: "Reason<>InstitutionName<>" — institution appears before "<>"
                if (preg_match('/(.+?)<>([A-Z][A-Z0-9\s&.,\'-]{3,60})<>/i', $line, $m)) {
                    $inst = trim($m[2]);
                    if ($inst && $inst !== $enquirerName) { // avoid duplicate of top entry
                        $inquiries[] = [
                            'date'        => '',
                            'reason'      => trim($m[1]),
                            'institution' => $inst,
                            'phone'       => '',
                        ];
                    }
                }
            }
        }

        return array_values($inquiries);
    }
}
