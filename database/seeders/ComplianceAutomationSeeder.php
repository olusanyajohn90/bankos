<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ComplianceAutomationSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->value('id');
        if (!$tenantId) {
            $this->command->warn('No tenant found, skipping ComplianceAutomationSeeder.');
            return;
        }

        // Clean up any previous seeded data for this tenant
        DB::table('compliance_trust_reports')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_audit_trail')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_evidence')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_monitors')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_controls')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_frameworks')->where('tenant_id', $tenantId)->delete();

        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id');
        $now = Carbon::now();

        // ── FRAMEWORKS ───────────────────────────────────────────────────────

        $frameworks = [
            [
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'name' => 'CBN MFB Guidelines', 'code' => 'cbn_mfb',
                'description' => 'Central Bank of Nigeria Microfinance Bank regulations covering capital adequacy, governance, and operational standards.',
                'total_controls' => 15, 'compliant_controls' => 9, 'non_compliant_controls' => 2,
                'not_assessed_controls' => 1, 'compliance_score' => 73.3,
                'last_assessed_at' => $now->copy()->subDays(2), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'name' => 'NDIC Regulations', 'code' => 'ndic',
                'description' => 'Nigeria Deposit Insurance Corporation regulations for deposit protection and insured institution compliance.',
                'total_controls' => 8, 'compliant_controls' => 6, 'non_compliant_controls' => 1,
                'not_assessed_controls' => 0, 'compliance_score' => 81.3,
                'last_assessed_at' => $now->copy()->subDays(3), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'name' => 'NFIU AML/CFT', 'code' => 'nfiu_aml',
                'description' => 'Nigerian Financial Intelligence Unit Anti-Money Laundering and Counter-Financing of Terrorism requirements.',
                'total_controls' => 10, 'compliant_controls' => 7, 'non_compliant_controls' => 1,
                'not_assessed_controls' => 1, 'compliance_score' => 75.0,
                'last_assessed_at' => $now->copy()->subDays(1), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'name' => 'NDPR Data Protection', 'code' => 'ndpr',
                'description' => 'Nigeria Data Protection Regulation ensuring data privacy, consent management, and breach notification.',
                'total_controls' => 6, 'compliant_controls' => 4, 'non_compliant_controls' => 0,
                'not_assessed_controls' => 1, 'compliance_score' => 75.0,
                'last_assessed_at' => $now->copy()->subDays(5), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'name' => 'BOFIA Requirements', 'code' => 'bofia',
                'description' => 'Banks and Other Financial Institutions Act provisions for institutional governance and risk management.',
                'total_controls' => 5, 'compliant_controls' => 3, 'non_compliant_controls' => 0,
                'not_assessed_controls' => 1, 'compliance_score' => 70.0,
                'last_assessed_at' => $now->copy()->subDays(7), 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        DB::table('compliance_frameworks')->insert($frameworks);

        $fwMap = [];
        foreach ($frameworks as $fw) {
            $fwMap[$fw['code']] = $fw['id'];
        }

        // ── CONTROLS ─────────────────────────────────────────────────────────

        // Priority mapping: 1=critical, 2=high, 3=medium, 4=low
        $controlDefs = [
            // CBN MFB - 15 controls
            // [framework_code, ref, title, description, category, status, priority_int]
            ['cbn_mfb', 'CBN-001', 'Capital Adequacy Ratio', 'Maintain minimum capital adequacy ratio as prescribed by CBN guidelines.', 'Capital', 'compliant', 1],
            ['cbn_mfb', 'CBN-002', 'Liquidity Ratio Compliance', 'Maintain minimum liquidity ratio for deposit safety.', 'Liquidity', 'compliant', 1],
            ['cbn_mfb', 'CBN-003', 'Single Obligor Limit', 'Ensure no single borrower exceeds the prescribed exposure limit.', 'Credit', 'partial', 2],
            ['cbn_mfb', 'CBN-004', 'KYC Completion Rate', 'All customers must have completed KYC verification.', 'KYC', 'compliant', 2],
            ['cbn_mfb', 'CBN-005', 'Loan Classification', 'Proper classification of loans per CBN prudential guidelines.', 'Credit', 'compliant', 2],
            ['cbn_mfb', 'CBN-006', 'Provisioning Adequacy', 'Maintain adequate provisioning for classified loans.', 'Credit', 'compliant', 2],
            ['cbn_mfb', 'CBN-007', 'Regulatory Reporting Timeliness', 'Submit all required returns to CBN within prescribed timelines.', 'Reporting', 'partial', 3],
            ['cbn_mfb', 'CBN-008', 'Board Composition', 'Ensure board composition meets CBN corporate governance guidelines.', 'Governance', 'compliant', 3],
            ['cbn_mfb', 'CBN-009', 'Insider Lending Limits', 'Lending to insiders must not exceed aggregate limits.', 'Credit', 'compliant', 2],
            ['cbn_mfb', 'CBN-010', 'Branch Licensing', 'All branches must have valid CBN branch licenses.', 'Operations', 'compliant', 3],
            ['cbn_mfb', 'CBN-011', 'IT Security Standards', 'Comply with CBN IT standards for financial institutions.', 'Technology', 'partial', 2],
            ['cbn_mfb', 'CBN-012', 'BVN Compliance', 'All accounts linked to verified Bank Verification Numbers.', 'KYC', 'compliant', 2],
            ['cbn_mfb', 'CBN-013', 'Customer Complaints Resolution', 'Resolve customer complaints within CBN-prescribed timelines.', 'Customer Service', 'non_compliant', 3],
            ['cbn_mfb', 'CBN-014', 'Interest Rate Disclosure', 'Transparent disclosure of all interest rates and fees.', 'Consumer Protection', 'compliant', 3],
            ['cbn_mfb', 'CBN-015', 'Dormancy Management', 'Proper management and reporting of dormant accounts.', 'Operations', 'not_assessed', 4],

            // NDIC - 8 controls
            ['ndic', 'NDIC-001', 'Premium Payment', 'Timely payment of deposit insurance premiums.', 'Insurance', 'compliant', 1],
            ['ndic', 'NDIC-002', 'Depositor Information Accuracy', 'Maintain accurate records of all depositors.', 'Data', 'compliant', 2],
            ['ndic', 'NDIC-003', 'Insured Deposit Computation', 'Correct computation of insured deposits per depositor.', 'Insurance', 'compliant', 2],
            ['ndic', 'NDIC-004', 'Reporting Accuracy', 'Accurate and timely reporting to NDIC.', 'Reporting', 'partial', 3],
            ['ndic', 'NDIC-005', 'DIS Compliance', 'Compliance with Deposit Insurance Scheme requirements.', 'Insurance', 'compliant', 2],
            ['ndic', 'NDIC-006', 'Pass-Through Coverage', 'Proper handling of pass-through deposit insurance.', 'Insurance', 'compliant', 3],
            ['ndic', 'NDIC-007', 'Failure Resolution Readiness', 'Maintain adequate records for potential resolution scenarios.', 'Risk', 'non_compliant', 2],
            ['ndic', 'NDIC-008', 'Data Submission', 'Regular data submissions to NDIC as required.', 'Reporting', 'compliant', 3],

            // NFIU AML - 10 controls
            ['nfiu_aml', 'AML-001', 'CTR Filing', 'File Currency Transaction Reports for transactions exceeding threshold.', 'Reporting', 'compliant', 1],
            ['nfiu_aml', 'AML-002', 'STR Filing', 'File Suspicious Transaction Reports within prescribed timelines.', 'Reporting', 'compliant', 1],
            ['nfiu_aml', 'AML-003', 'PEP Screening', 'Screen all customers against Politically Exposed Persons lists.', 'Screening', 'compliant', 2],
            ['nfiu_aml', 'AML-004', 'Sanctions Screening', 'Screen transactions and customers against global sanctions lists.', 'Screening', 'compliant', 2],
            ['nfiu_aml', 'AML-005', 'Transaction Monitoring', 'Automated monitoring of transactions for suspicious patterns.', 'Monitoring', 'partial', 2],
            ['nfiu_aml', 'AML-006', 'Customer Due Diligence', 'Perform adequate CDD on all customers at onboarding.', 'KYC', 'compliant', 2],
            ['nfiu_aml', 'AML-007', 'Enhanced Due Diligence', 'Perform EDD on high-risk customers and PEPs.', 'KYC', 'compliant', 2],
            ['nfiu_aml', 'AML-008', 'Record Keeping', 'Maintain transaction records for minimum prescribed period.', 'Data', 'compliant', 3],
            ['nfiu_aml', 'AML-009', 'Staff Training', 'Regular AML/CFT training for all relevant staff.', 'Training', 'non_compliant', 3],
            ['nfiu_aml', 'AML-010', 'Compliance Officer Designation', 'Appoint a designated Chief Compliance Officer.', 'Governance', 'not_assessed', 1],

            // NDPR - 6 controls
            ['ndpr', 'NDPR-001', 'Data Protection Officer', 'Appoint a qualified Data Protection Officer.', 'Governance', 'compliant', 2],
            ['ndpr', 'NDPR-002', 'Privacy Policy', 'Maintain and publish a comprehensive privacy policy.', 'Documentation', 'compliant', 3],
            ['ndpr', 'NDPR-003', 'Consent Management', 'Obtain and manage explicit consent for data processing.', 'Operations', 'partial', 2],
            ['ndpr', 'NDPR-004', 'Data Breach Notification', 'Establish and test data breach notification procedures.', 'Incident Response', 'compliant', 1],
            ['ndpr', 'NDPR-005', 'Data Retention Policy', 'Implement compliant data retention and destruction policies.', 'Documentation', 'compliant', 3],
            ['ndpr', 'NDPR-006', 'Third-Party Data Processing', 'Ensure third-party processors comply with NDPR requirements.', 'Vendor Management', 'not_assessed', 3],

            // BOFIA - 5 controls
            ['bofia', 'BOF-001', 'Minimum Capital Requirement', 'Maintain minimum capital as prescribed under BOFIA.', 'Capital', 'compliant', 1],
            ['bofia', 'BOF-002', 'Corporate Governance', 'Board and management structure compliant with BOFIA requirements.', 'Governance', 'compliant', 2],
            ['bofia', 'BOF-003', 'Related Party Transactions', 'Proper disclosure and limits on related party transactions.', 'Governance', 'partial', 2],
            ['bofia', 'BOF-004', 'Risk Management Framework', 'Comprehensive risk management framework in place.', 'Risk', 'compliant', 2],
            ['bofia', 'BOF-005', 'Internal Audit Function', 'Maintain an independent and effective internal audit function.', 'Governance', 'not_assessed', 3],
        ];

        $controlIds = [];
        $compliantControlIds = [];

        foreach ($controlDefs as $cd) {
            $id = Str::uuid()->toString();
            $controlIds[] = $id;
            if ($cd[5] === 'compliant') {
                $compliantControlIds[] = $id;
            }

            DB::table('compliance_controls')->insert([
                'id'               => $id,
                'framework_id'     => $fwMap[$cd[0]],
                'tenant_id'        => $tenantId,
                'control_ref'      => $cd[1],
                'title'            => $cd[2],
                'description'      => $cd[3],
                'category'         => $cd[4],
                'status'           => $cd[5],
                'priority'         => $cd[6],
                'evidence_notes'   => $cd[5] === 'compliant' ? 'Evidence collected and verified.' : null,
                'evidence_files'   => json_encode([]),
                'auto_check_config' => json_encode([]),
                'last_checked_at'  => $cd[5] !== 'not_assessed' ? $now->copy()->subDays(rand(0, 7)) : null,
                'assigned_to'      => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── MONITORS ─────────────────────────────────────────────────────────

        $monitorDefs = [
            ['Capital Adequacy Ratio', 'Monitors capital adequacy against CBN minimum requirement.', 'capital_adequacy', 15.2, 10.0, 'passing', 'daily', ['direction' => 'higher_is_better']],
            ['Liquidity Ratio', 'Tracks liquid assets to total deposits ratio.', 'liquidity_ratio', 45.3, 30.0, 'passing', 'daily', ['direction' => 'higher_is_better']],
            ['Single Obligor Limit', 'Maximum single borrower exposure as % of equity.', 'single_obligor', 18.5, 20.0, 'warning', 'daily', ['direction' => 'lower_is_better']],
            ['KYC Completion Rate', 'Percentage of customers with completed KYC.', 'kyc_completion', 92.4, 90.0, 'passing', 'daily', ['direction' => 'higher_is_better']],
            ['NPL Ratio', 'Non-performing loans as percentage of total loan portfolio.', 'npl_ratio', 3.2, 5.0, 'passing', 'daily', ['direction' => 'lower_is_better']],
            ['CTR Filing Rate', 'Currency Transaction Reports filed vs required.', 'ctr_filing', 100.0, 100.0, 'passing', 'daily', ['direction' => 'higher_is_better']],
            ['STR Response Time', 'Average time to file Suspicious Transaction Reports.', 'str_response', 18.0, 24.0, 'passing', 'daily', ['direction' => 'lower_is_better']],
            ['Dormant Account Ratio', 'Dormant accounts as percentage of total accounts.', 'dormancy_check', 8.2, 10.0, 'passing', 'weekly', ['direction' => 'lower_is_better']],
            ['BVN Verification Rate', 'Percentage of accounts with verified BVN.', 'bvn_verification', 97.1, 95.0, 'passing', 'daily', ['direction' => 'higher_is_better']],
            ['Data Breach Response', 'Average data breach notification time in hours.', 'data_breach', 4.0, 72.0, 'passing', 'monthly', ['direction' => 'lower_is_better']],
        ];

        foreach ($monitorDefs as $md) {
            DB::table('compliance_monitors')->insert([
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $tenantId,
                'name'            => $md[0],
                'description'     => $md[1],
                'check_type'      => $md[2],
                'config'          => json_encode($md[7]),
                'frequency'       => $md[6],
                'current_value'   => $md[3],
                'threshold_value' => $md[4],
                'status'          => $md[5],
                'last_checked_at' => $now->copy()->subHours(rand(1, 24)),
                'is_active'       => true,
                'control_id'      => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ── EVIDENCE ─────────────────────────────────────────────────────────

        $evidenceTypes = ['document', 'screenshot', 'query_result', 'api_response', 'manual_note', 'system_log'];
        $evidenceTitles = [
            'Capital adequacy computation report',
            'Board meeting minutes - governance review',
            'KYC verification batch report',
            'Liquidity position statement',
            'CBN return submission confirmation',
            'AML training completion certificates',
            'Transaction monitoring system screenshot',
            'Risk assessment report Q1',
            'Data protection impact assessment',
            'Sanctions screening log export',
            'BVN verification batch results',
            'Deposit insurance premium receipt',
            'IT security audit report',
            'Customer complaints resolution log',
            'Loan classification review report',
            'Internal audit report - Q4',
            'PEP screening results export',
            'Privacy policy publication proof',
            'Compliance officer appointment letter',
            'Branch license renewal documents',
        ];

        $usedControls = array_slice($compliantControlIds, 0, min(20, count($compliantControlIds)));
        for ($i = 0; $i < 20; $i++) {
            $ctrlId = $usedControls[$i % count($usedControls)];

            DB::table('compliance_evidence')->insert([
                'id'               => Str::uuid()->toString(),
                'control_id'       => $ctrlId,
                'tenant_id'        => $tenantId,
                'type'             => $evidenceTypes[array_rand($evidenceTypes)],
                'title'            => $evidenceTitles[$i],
                'description'      => 'Automatically collected compliance evidence.',
                'file_path'        => null,
                'data'             => json_encode(['source' => 'system', 'verified' => true]),
                'is_auto_collected' => $i % 3 === 0,
                'collected_by'     => $userId,
                'collected_at'     => $now->copy()->subDays(rand(0, 30)),
                'expires_at'       => $now->copy()->addDays(rand(30, 365)),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── AUDIT TRAIL ──────────────────────────────────────────────────────

        $eventTypes = [
            'breach', 'warning', 'check_passed', 'check_passed', 'check_passed',
            'evidence_added', 'evidence_added', 'status_changed', 'framework_scored',
            'checks_run',
        ];

        $descriptions = [
            'breach' => [
                'Single obligor limit exceeded: 21.3% vs threshold 20%',
                'Customer complaints SLA breach detected',
                'AML staff training overdue by 30 days',
            ],
            'warning' => [
                'Single obligor approaching limit: 18.5% vs 20%',
                'KYC completion rate trending down: 92.4%',
                'Dormant account ratio increasing: 8.2%',
            ],
            'check_passed' => [
                'Capital adequacy check passed: 15.2% above 10% threshold',
                'Liquidity ratio check passed: 45.3% above 30% threshold',
                'BVN verification rate check passed: 97.1%',
                'CTR filing rate at 100%',
                'NPL ratio healthy at 3.2%',
                'Data breach response time within limits: 4hrs',
            ],
            'evidence_added' => [
                'Evidence uploaded: Capital adequacy computation report',
                'Evidence uploaded: KYC batch verification results',
                'Evidence collected: Liquidity position statement',
                'Auto-collected: Transaction monitoring system log',
            ],
            'status_changed' => [
                'Control CBN-003 status changed from non_compliant to partial',
                'Control AML-005 status changed from not_assessed to partial',
                'Control NDPR-003 updated to partial compliance',
            ],
            'framework_scored' => [
                'CBN MFB Guidelines scored at 73.3%',
                'NDIC Regulations scored at 81.3%',
                'NFIU AML/CFT scored at 75.0%',
                'NDPR Data Protection scored at 75.0%',
                'BOFIA Requirements scored at 70.0%',
            ],
            'checks_run' => [
                'Automated checks completed: 10 run, 8 passing, 1 warning, 1 failing',
                'Scheduled compliance check cycle completed',
            ],
        ];

        for ($i = 0; $i < 30; $i++) {
            $eventType = $eventTypes[array_rand($eventTypes)];
            $descList = $descriptions[$eventType];
            $desc = $descList[array_rand($descList)];

            DB::table('compliance_audit_trail')->insert([
                'tenant_id'   => $tenantId,
                'event_type'  => $eventType,
                'entity_type' => match ($eventType) {
                    'breach', 'warning', 'check_passed' => 'monitor',
                    'evidence_added', 'status_changed' => 'control',
                    'framework_scored' => 'framework',
                    'checks_run' => 'system',
                    default => 'system',
                },
                'entity_id'   => null,
                'description' => $desc,
                'metadata'    => json_encode(['seeded' => true]),
                'user_id'     => $userId,
                'created_at'  => $now->copy()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                'updated_at'  => $now,
            ]);
        }

        // ── TRUST REPORT ─────────────────────────────────────────────────────

        DB::table('compliance_trust_reports')->insert([
            'id'                  => Str::uuid()->toString(),
            'tenant_id'           => $tenantId,
            'public_url_token'    => Str::random(32),
            'is_published'        => true,
            'visible_frameworks'  => json_encode(array_values($fwMap)),
            'custom_sections'     => json_encode([]),
            'logo_path'           => null,
            'intro_text'          => 'We are committed to maintaining the highest standards of regulatory compliance across all areas of our operations. This trust report provides transparency into our compliance posture across major Nigerian financial regulatory frameworks.',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        $this->command->info('ComplianceAutomationSeeder: seeded 5 frameworks, 44 controls, 10 monitors, 20 evidence records, 30 audit trail entries, 1 trust report.');
    }
}
