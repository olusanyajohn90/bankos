<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdvancedComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = DB::table('tenants')->value('id');
        if (!$tenantId) {
            $this->command->warn('No tenant found, skipping AdvancedComplianceSeeder.');
            return;
        }

        $userId = DB::table('users')->where('tenant_id', $tenantId)->value('id') ?? 1;
        $customers = DB::table('customers')->where('tenant_id', $tenantId)->limit(20)->get(['id', 'first_name', 'last_name', 'customer_number']);

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found, skipping AdvancedComplianceSeeder.');
            return;
        }

        $now = Carbon::now();

        // Clean existing data
        DB::table('regulatory_simulations')->where('tenant_id', $tenantId)->delete();
        DB::table('cross_border_rules')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_agent_tasks')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_chat_sessions')->where('tenant_id', $tenantId)->delete();
        DB::table('compliance_scenarios')->where('tenant_id', $tenantId)->delete();
        DB::table('regulatory_changes')->where('tenant_id', $tenantId)->delete();
        DB::table('predictive_compliance_alerts')->where('tenant_id', $tenantId)->delete();
        DB::table('adverse_media_results')->where('tenant_id', $tenantId)->delete();
        DB::table('beneficial_owners')->where('tenant_id', $tenantId)->delete();
        DB::table('entity_relationships')->where('tenant_id', $tenantId)->delete();
        DB::table('customer_behavior_profiles')->where('tenant_id', $tenantId)->delete();
        DB::table('perpetual_kyc_events')->where('tenant_id', $tenantId)->delete();
        DB::table('suspicious_activity_reports')->where('tenant_id', $tenantId)->delete();
        DB::table('transaction_screenings')->where('tenant_id', $tenantId)->delete();
        DB::table('customer_risk_scores')->where('tenant_id', $tenantId)->delete();

        // ═════════════════════════════════════════════════════════════
        // PHASE 1: Risk Scores, Screenings, SARs, KYC Events
        // ═════════════════════════════════════════════════════════════

        $this->command->info('Seeding customer risk scores...');
        $riskLevels = ['low', 'low', 'low', 'low', 'low', 'low', 'low', 'low', 'medium', 'medium', 'medium', 'medium', 'medium', 'medium', 'high', 'high', 'high', 'high', 'critical', 'critical'];
        $riskScores = [12, 15, 18, 8, 22, 10, 19, 14, 38, 42, 45, 36, 48, 40, 65, 72, 68, 75, 85, 92];

        foreach ($customers->take(20) as $i => $customer) {
            $level = $riskLevels[$i] ?? 'low';
            $score = $riskScores[$i] ?? 20;

            $breakdown = [
                'transaction' => rand(0, 25),
                'kyc'         => rand(0, 20),
                'pep'         => $level === 'critical' ? rand(10, 20) : 0,
                'geography'   => rand(0, 15),
                'product'     => rand(0, 10),
                'behavior'    => rand(0, 10),
            ];

            $factors = [];
            if ($breakdown['transaction'] > 10) $factors[] = ['factor' => 'high_value_txns', 'weight' => 25, 'score' => $breakdown['transaction'], 'description' => 'Multiple high-value transactions detected'];
            if ($breakdown['kyc'] > 10) $factors[] = ['factor' => 'kyc_incomplete', 'weight' => 20, 'score' => $breakdown['kyc'], 'description' => 'KYC documentation incomplete or pending'];
            if ($breakdown['pep'] > 0) $factors[] = ['factor' => 'pep_associated', 'weight' => 20, 'score' => $breakdown['pep'], 'description' => 'Associated with Politically Exposed Person'];
            if ($breakdown['behavior'] > 5) $factors[] = ['factor' => 'behavioral_anomalies', 'weight' => 10, 'score' => $breakdown['behavior'], 'description' => 'Unusual transaction patterns detected'];

            DB::table('customer_risk_scores')->insert([
                'id'               => Str::uuid(),
                'tenant_id'        => $tenantId,
                'customer_id'      => $customer->id,
                'overall_score'    => $score,
                'risk_level'       => $level,
                'score_breakdown'  => json_encode($breakdown),
                'risk_factors'     => json_encode($factors),
                'last_assessed_at' => $now->copy()->subDays(rand(0, 14)),
                'assessed_by'      => 'system',
                'ai_narrative'     => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        $this->command->info('Seeding transaction screenings...');
        $screeningResults = array_merge(
            array_fill(0, 25, 'clear'),
            array_fill(0, 3, 'potential_match'),
            array_fill(0, 2, 'match')
        );
        shuffle($screeningResults);

        $screeningTypes = ['sanctions', 'pep', 'adverse_media', 'threshold', 'pattern', 'velocity'];

        foreach ($screeningResults as $i => $result) {
            $cust = $customers->random();
            $conf = match($result) { 'clear' => rand(95, 100), 'potential_match' => rand(60, 85), 'match' => rand(85, 99) };
            $disp = match($result) {
                'clear' => 'false_positive',
                'match' => (rand(0, 1) ? 'true_positive' : 'escalated'),
                default => 'pending',
            };

            DB::table('transaction_screenings')->insert([
                'tenant_id'      => $tenantId,
                'transaction_id' => null,
                'customer_id'    => $cust->id,
                'screening_type' => $screeningTypes[array_rand($screeningTypes)],
                'result'         => $result,
                'confidence'     => $conf,
                'match_details'  => $result !== 'clear' ? json_encode(['matched_name' => 'Name Variant', 'list_source' => 'OFAC SDN', 'match_score' => $conf]) : null,
                'reason_codes'   => $result !== 'clear' ? json_encode(['SANCTIONS_MATCH', 'NAME_SIMILARITY']) : null,
                'disposition'    => $disp,
                'reviewed_by'    => $disp !== 'pending' ? $userId : null,
                'reviewed_at'    => $disp !== 'pending' ? $now->copy()->subDays(rand(0, 5)) : null,
                'review_notes'   => $disp === 'false_positive' ? 'False positive - common name match' : null,
                'created_at'     => $now->copy()->subDays(rand(0, 30)),
                'updated_at'     => $now,
            ]);
        }

        $this->command->info('Seeding SAR/STR reports...');
        $sarStatuses = ['filed', 'pending_review', 'draft'];
        foreach ($sarStatuses as $idx => $status) {
            $cust = $customers->slice($idx)->first() ?? $customers->first();
            $txnAmount = rand(5000000, 25000000);
            DB::table('suspicious_activity_reports')->insert([
                'id'                     => Str::uuid(),
                'tenant_id'              => $tenantId,
                'report_type'            => $idx === 0 ? 'SAR' : 'STR',
                'reference'              => 'STR-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_id'            => $cust->id,
                'narrative'              => "SUSPICIOUS ACTIVITY REPORT\n\nSubject: {$cust->first_name} {$cust->last_name} (Customer No: {$cust->customer_number})\n\nSUMMARY:\nDuring the review period, the subject conducted multiple transactions totalling NGN " . number_format($txnAmount, 2) . " that exhibited patterns inconsistent with the customer's known profile.\n\nThe transactions were flagged due to:\n- Unusual transaction frequency\n- Round-number structuring patterns\n- Amounts inconsistent with declared income\n\nRECOMMENDED ACTION:\nThis report is filed with NFIU in compliance with the Money Laundering (Prohibition) Act 2011.",
                'transactions_involved'  => json_encode([Str::uuid(), Str::uuid(), Str::uuid()]),
                'total_amount'           => $txnAmount,
                'suspicion_category'     => ['structuring', 'unusual_pattern', 'layering'][$idx],
                'status'                 => $status,
                'filing_reference'       => $status === 'filed' ? 'NFIU-' . strtoupper(Str::random(8)) : null,
                'prepared_by'            => $userId,
                'approved_by'            => $status === 'filed' ? $userId : null,
                'filed_at'               => $status === 'filed' ? $now->copy()->subDays(3) : null,
                'created_at'             => $now->copy()->subDays(10 - $idx * 3),
                'updated_at'             => $now,
            ]);
        }

        $this->command->info('Seeding perpetual KYC events...');
        $kycEventTypes = ['document_expiry', 'address_change', 'transaction_pattern_shift', 'sanctions_hit', 'risk_score_change', 'pep_status_change', 'occupation_change'];
        $kycActions = ['review', 'enhanced_due_diligence', 'account_restriction', 'none', 'sar_filing'];

        for ($i = 0; $i < 15; $i++) {
            $cust = $customers->random();
            $isResolved = $i < 10;
            DB::table('perpetual_kyc_events')->insert([
                'tenant_id'       => $tenantId,
                'customer_id'     => $cust->id,
                'event_type'      => $kycEventTypes[array_rand($kycEventTypes)],
                'description'     => match($i % 5) {
                    0 => "KYC documents approaching 12-month review deadline for {$cust->first_name} {$cust->last_name}",
                    1 => "Customer address changed - requires re-verification",
                    2 => "Unusual transaction pattern shift detected - monthly volume increased 200%",
                    3 => "Risk score elevated from medium to high",
                    4 => "Occupation change reported - requires updated documentation",
                },
                'old_data'        => $i % 3 === 0 ? json_encode(['previous_address' => '12 Marina Road, Lagos']) : null,
                'new_data'        => $i % 3 === 0 ? json_encode(['new_address' => '45 Adeola Odeku, VI']) : null,
                'action_required' => $kycActions[array_rand($kycActions)],
                'status'          => $isResolved ? 'resolved' : (['open', 'open', 'in_review', 'open', 'escalated'][$i % 5]),
                'resolved_by'     => $isResolved ? $userId : null,
                'resolved_at'     => $isResolved ? $now->copy()->subDays(rand(1, 20)) : null,
                'created_at'      => $now->copy()->subDays(rand(1, 45)),
                'updated_at'      => $now,
            ]);
        }

        // ═════════════════════════════════════════════════════════════
        // PHASE 2: Behavior Profiles, Relationships, Owners, Media
        // ═════════════════════════════════════════════════════════════

        $this->command->info('Seeding behavior profiles...');
        foreach ($customers->take(10) as $i => $customer) {
            $avgMonthly = rand(500000, 15000000);
            $avgTxn = rand(50000, 2000000);
            $anomalies = $i < 3 ? rand(2, 8) : 0;

            DB::table('customer_behavior_profiles')->insert([
                'id'                   => Str::uuid(),
                'tenant_id'            => $tenantId,
                'customer_id'          => $customer->id,
                'transaction_patterns' => json_encode([
                    'avg_monthly_volume' => $avgMonthly,
                    'avg_txn_size'       => $avgTxn,
                    'total_txn_count'    => rand(20, 200),
                    'max_txn_amount'     => $avgTxn * 5,
                    'min_txn_amount'     => rand(1000, 10000),
                ]),
                'baseline_metrics' => json_encode([
                    'avg_balance'     => rand(100000, 5000000),
                    'income_estimate' => $avgMonthly * 0.6,
                    'expense_ratio'   => round(rand(50, 85) / 100, 2),
                    'savings_rate'    => round(rand(10, 40) / 100, 2),
                ]),
                'anomaly_thresholds' => json_encode([
                    'volume_3sigma'  => $avgTxn * 4,
                    'size_3sigma'    => $avgTxn * 3.5,
                    'frequency_max'  => 50,
                ]),
                'anomaly_count_30d'    => $anomalies,
                'behavior_risk_score'  => min(100, $anomalies * 8),
                'profile_computed_at'  => $now->copy()->subDays(rand(0, 7)),
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        $this->command->info('Seeding entity relationships...');
        $relTypes = ['transacts_with', 'guarantor_for', 'director_of', 'related_to', 'shares_address', 'shares_phone', 'shares_employer'];
        for ($i = 0; $i < 20; $i++) {
            $a = $customers->random();
            $b = $customers->where('id', '!=', $a->id)->random();
            $isSuspicious = $i < 3;

            DB::table('entity_relationships')->insert([
                'tenant_id'         => $tenantId,
                'entity_a_id'       => $a->id,
                'entity_a_type'     => 'customer',
                'entity_b_id'       => $b->id,
                'entity_b_type'     => 'customer',
                'relationship_type' => $relTypes[array_rand($relTypes)],
                'strength'          => rand(10, 95),
                'transaction_count' => rand(1, 150),
                'total_volume'      => rand(100000, 50000000),
                'is_suspicious'     => $isSuspicious,
                'notes'             => $isSuspicious ? 'High-volume circular transactions detected between entities' : null,
                'created_at'        => $now->copy()->subDays(rand(0, 60)),
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('Seeding beneficial owners...');
        $ownerNames = [
            ['name' => 'Chief Adebayo Ogundimu', 'nationality' => 'Nigerian', 'pep' => true, 'sanctioned' => false, 'pct' => 35],
            ['name' => 'Mrs. Folake Adeyemi', 'nationality' => 'Nigerian', 'pep' => false, 'sanctioned' => false, 'pct' => 25],
            ['name' => 'Mr. Zhang Wei', 'nationality' => 'Chinese', 'pep' => false, 'sanctioned' => false, 'pct' => 20],
            ['name' => 'Alhaji Musa Ibrahim', 'nationality' => 'Nigerian', 'pep' => true, 'sanctioned' => false, 'pct' => 15],
            ['name' => 'Mr. Viktor Petrov', 'nationality' => 'Russian', 'pep' => false, 'sanctioned' => true, 'pct' => 10],
        ];

        foreach ($ownerNames as $idx => $owner) {
            $cust = $customers->slice($idx)->first() ?? $customers->first();
            DB::table('beneficial_owners')->insert([
                'id'                   => Str::uuid(),
                'tenant_id'            => $tenantId,
                'customer_id'          => $cust->id,
                'owner_name'           => $owner['name'],
                'nationality'          => $owner['nationality'],
                'id_type'              => 'NIN',
                'id_number'            => 'NIN' . rand(10000000000, 99999999999),
                'ownership_percentage' => $owner['pct'],
                'is_pep'               => $owner['pep'],
                'is_sanctioned'        => $owner['sanctioned'],
                'verification_status'  => $owner['sanctioned'] ? 'failed' : ($idx < 3 ? 'verified' : 'pending'),
                'date_of_birth'        => Carbon::now()->subYears(rand(35, 70))->format('Y-m-d'),
                'address'              => "Lagos, Nigeria",
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        $this->command->info('Seeding adverse media results...');
        $mediaItems = [
            ['source' => 'Premium Times', 'headline' => 'EFCC investigates N2.5B fraud linked to Lagos businessmen', 'category' => 'fraud', 'severity' => 'high'],
            ['source' => 'Channels TV', 'headline' => 'CBN blacklists three companies for forex violations', 'category' => 'sanctions', 'severity' => 'critical'],
            ['source' => 'The Guardian Nigeria', 'headline' => 'Money laundering ring busted in Abuja', 'category' => 'money_laundering', 'severity' => 'high'],
            ['source' => 'Punch News', 'headline' => 'Former PEP linked to property fraud scheme', 'category' => 'corruption', 'severity' => 'medium'],
            ['source' => 'Vanguard News', 'headline' => 'Company director faces tax evasion charges', 'category' => 'fraud', 'severity' => 'medium'],
            ['source' => 'Daily Trust', 'headline' => 'NFIU flags suspicious transactions from northern states', 'category' => 'money_laundering', 'severity' => 'low'],
            ['source' => 'BusinessDay', 'headline' => 'SEC sanctions brokerage firm for insider trading', 'category' => 'sanctions', 'severity' => 'medium'],
            ['source' => 'ThisDay', 'headline' => 'Cryptocurrency scam victims demand refunds from fintech', 'category' => 'fraud', 'severity' => 'low'],
        ];

        foreach ($mediaItems as $idx => $item) {
            $cust = $customers->random();
            DB::table('adverse_media_results')->insert([
                'tenant_id'      => $tenantId,
                'customer_id'    => $cust->id,
                'source'         => $item['source'],
                'headline'       => $item['headline'],
                'summary'        => 'Automated screening detected a potential adverse media match. Manual review recommended.',
                'url'            => 'https://example.com/news/' . Str::slug($item['headline']),
                'published_date' => $now->copy()->subDays(rand(1, 90))->format('Y-m-d'),
                'category'       => $item['category'],
                'severity'       => $item['severity'],
                'disposition'    => ['pending', 'relevant', 'irrelevant', 'pending', 'escalated', 'irrelevant', 'pending', 'pending'][$idx],
                'created_at'     => $now->copy()->subDays(rand(0, 30)),
                'updated_at'     => $now,
            ]);
        }

        // ═════════════════════════════════════════════════════════════
        // PHASE 3: Predictive Alerts, Reg Changes, Scenarios, Chat
        // ═════════════════════════════════════════════════════════════

        $this->command->info('Seeding predictive alerts...');
        $alerts = [
            ['type' => 'trend_warning', 'title' => 'NPL Ratio Trending Above CBN Threshold', 'severity' => 'critical', 'desc' => 'Current NPL ratio at 4.2%. CBN prudential guideline threshold is 5%. At current trajectory, breach predicted within 60 days.', 'metric' => 'npl_ratio', 'current' => 4.2, 'predicted' => 5.8, 'date' => 60, 'conf' => 78, 'action' => 'Review loan portfolio quality and increase provisioning for sub-standard loans.'],
            ['type' => 'predicted_breach', 'title' => 'KYC Expiry Wave: 45 Customers Need Re-verification', 'severity' => 'warning', 'desc' => '45 customer KYC records are approaching 12-month review deadline. Failure to renew will breach CBN KYC requirements.', 'metric' => 'kyc_expiry_count', 'current' => 45, 'predicted' => 45, 'date' => 30, 'conf' => 95, 'action' => 'Initiate batch KYC re-verification. Prioritize high-risk customers first.'],
            ['type' => 'anomaly_cluster', 'title' => 'Unusual Transaction Volume Spike in Corporate Accounts', 'severity' => 'warning', 'desc' => 'Corporate account transaction volume has increased 180% month-over-month. Pattern analysis suggests potential structuring activity.', 'metric' => 'corporate_txn_volume', 'current' => 280, 'predicted' => 350, 'date' => 15, 'conf' => 65, 'action' => 'Enhanced monitoring on top 10 corporate accounts. Review CTR filing completeness.'],
            ['type' => 'regulatory_risk', 'title' => 'Liquidity Coverage Ratio Approaching Minimum', 'severity' => 'info', 'desc' => 'LCR at 105%, approaching the 100% CBN minimum. Seasonal deposit withdrawals may push below threshold.', 'metric' => 'lcr', 'current' => 105, 'predicted' => 98, 'date' => 45, 'conf' => 60, 'action' => 'Monitor deposit outflows closely. Prepare liquidity contingency plan.'],
            ['type' => 'trend_warning', 'title' => 'Capital Adequacy Buffer Narrowing', 'severity' => 'warning', 'desc' => 'CAR at 11.5% vs 10% minimum. Rapid loan growth is eroding the capital buffer.', 'metric' => 'car', 'current' => 11.5, 'predicted' => 10.2, 'date' => 90, 'conf' => 70, 'action' => 'Consider slowing loan growth or raising additional capital through retained earnings.'],
        ];

        foreach ($alerts as $a) {
            DB::table('predictive_compliance_alerts')->insert([
                'id'                 => Str::uuid(),
                'tenant_id'          => $tenantId,
                'alert_type'         => $a['type'],
                'title'              => $a['title'],
                'description'        => $a['desc'],
                'prediction_data'    => json_encode([
                    'metric'          => $a['metric'],
                    'current_value'   => $a['current'],
                    'predicted_value' => $a['predicted'],
                    'predicted_date'  => $now->copy()->addDays($a['date'])->toDateString(),
                    'confidence'      => $a['conf'],
                ]),
                'severity'           => $a['severity'],
                'status'             => $a === $alerts[3] ? 'acknowledged' : 'active',
                'recommended_action' => $a['action'],
                'ai_analysis'        => null,
                'created_at'         => $now->copy()->subDays(rand(0, 7)),
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('Seeding regulatory changes...');
        $regChanges = [
            [
                'regulator' => 'CBN', 'title' => 'Revised Guidelines on Minimum Capital Requirement for Commercial Banks',
                'summary' => 'CBN has announced a phased increase in minimum capital requirements for commercial banks from N25B to N500B, effective from March 2026.',
                'ref' => 'CBN/DIR/GEN/CIR/07/077', 'impact' => 'critical', 'status' => 'under_review',
                'areas' => ['capital', 'lending', 'risk_management'],
                'plan' => "Phase 1: Impact assessment (Q2 2026)\nPhase 2: Capital raising strategy (Q3 2026)\nPhase 3: Implementation and compliance reporting (Q4 2026)",
            ],
            [
                'regulator' => 'NFIU', 'title' => 'Updated STR Filing Guidelines and Enhanced Due Diligence Requirements',
                'summary' => 'NFIU requires financial institutions to file STRs within 24 hours of detection and implement enhanced due diligence for all PEP-related transactions.',
                'ref' => 'NFIU/2026/CIRCULAR/003', 'impact' => 'high', 'status' => 'impact_assessed',
                'areas' => ['aml', 'kyc', 'reporting'],
                'plan' => "Update STR filing workflows to meet 24-hour deadline. Train compliance staff on new EDD requirements.",
            ],
            [
                'regulator' => 'NDIC', 'title' => 'Revised Premium Assessment and Risk-Based Supervision Framework',
                'summary' => 'NDIC introduces risk-based deposit insurance premium assessments. Banks with higher risk profiles will pay increased premiums.',
                'ref' => 'NDIC/BSD/2026/015', 'impact' => 'medium', 'status' => 'new',
                'areas' => ['risk_management', 'reporting', 'capital'],
                'plan' => null,
            ],
            [
                'regulator' => 'FCCPC', 'title' => 'Nigeria Data Protection Regulation (NDPR) Amendment - Customer Consent',
                'summary' => 'Amendment requires explicit customer consent for all data processing activities, including enhanced rights for data deletion and portability.',
                'ref' => 'NDPR/2026/AMENDMENT/002', 'impact' => 'medium', 'status' => 'implemented',
                'areas' => ['data_protection', 'kyc', 'technology'],
                'plan' => "Updated consent forms deployed. Customer data portal launched. Staff training completed.",
            ],
        ];

        foreach ($regChanges as $rc) {
            DB::table('regulatory_changes')->insert([
                'id'                  => Str::uuid(),
                'tenant_id'           => $tenantId,
                'regulator'           => $rc['regulator'],
                'title'               => $rc['title'],
                'summary'             => $rc['summary'],
                'full_text'           => null,
                'reference_number'    => $rc['ref'],
                'effective_date'      => $now->copy()->addMonths(rand(1, 6))->format('Y-m-d'),
                'published_date'      => $now->copy()->subDays(rand(5, 60))->format('Y-m-d'),
                'impact_level'        => $rc['impact'],
                'affected_areas'      => json_encode($rc['areas']),
                'status'              => $rc['status'],
                'implementation_plan' => $rc['plan'],
                'affected_controls'   => null,
                'assigned_to'         => $userId,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        $this->command->info('Seeding compliance scenarios...');
        $scenarios = [
            ['name' => 'CTR Threshold Detection', 'category' => 'aml', 'config' => ['type' => 'transaction_simulation', 'params' => ['amount' => 10000000]], 'result' => 'passed', 'outcome' => ['simulated_amount' => 10000000, 'ctr_triggered' => true, 'aml_flagged' => true]],
            ['name' => 'Sanctions Screening Validation', 'category' => 'sanctions', 'config' => ['type' => 'sanctions_check', 'params' => ['entity' => 'Test SDN Entity']], 'result' => 'passed', 'outcome' => ['sanctions_db_active' => true, 'screening_functional' => true]],
            ['name' => 'KYC Completion Rate Check', 'category' => 'kyc', 'config' => ['type' => 'kyc_validation', 'params' => []], 'result' => 'failed', 'outcome' => ['kyc_completion_rate' => 87.5, 'threshold' => 95]],
            ['name' => 'Structuring Detection (N4.9M splits)', 'category' => 'aml', 'config' => ['type' => 'transaction_simulation', 'params' => ['amount' => 4900000]], 'result' => 'not_run', 'outcome' => null],
        ];

        foreach ($scenarios as $s) {
            DB::table('compliance_scenarios')->insert([
                'id'               => Str::uuid(),
                'tenant_id'        => $tenantId,
                'name'             => $s['name'],
                'description'      => "Automated compliance scenario test for {$s['category']} rules",
                'category'         => $s['category'],
                'test_config'      => json_encode($s['config']),
                'expected_outcome' => json_encode(['should_detect' => true]),
                'actual_outcome'   => $s['outcome'] ? json_encode($s['outcome']) : null,
                'result'           => $s['result'],
                'last_run_at'      => $s['result'] !== 'not_run' ? $now->copy()->subDays(rand(1, 7)) : null,
                'created_by'       => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ═════════════════════════════════════════════════════════════
        // PHASE 4: Agent Tasks, Cross-border Rules, Simulations
        // ═════════════════════════════════════════════════════════════

        $this->command->info('Seeding agent tasks...');
        $agentTasks = [
            ['type' => 'kyc_refresher', 'desc' => 'Scan all customers for expired KYC documents', 'status' => 'completed', 'items' => 156, 'issues' => 12, 'result' => ['message' => 'Scanned 156 customers, 12 need KYC refresh']],
            ['type' => 'sanctions_scanner', 'desc' => 'Screen all active customers against sanctions lists', 'status' => 'completed', 'items' => 342, 'issues' => 0, 'result' => ['message' => 'Screened 342 active customers. No new matches.']],
            ['type' => 'risk_scorer', 'desc' => 'Recalculate risk scores for all customers', 'status' => 'completed', 'items' => 342, 'issues' => 18, 'result' => ['message' => 'Scored 342 customers. 18 flagged as high/critical risk.']],
        ];

        foreach ($agentTasks as $at) {
            DB::table('compliance_agent_tasks')->insert([
                'id'              => Str::uuid(),
                'tenant_id'       => $tenantId,
                'agent_type'      => $at['type'],
                'description'     => $at['desc'],
                'config'          => null,
                'status'          => $at['status'],
                'result'          => json_encode($at['result']),
                'items_processed' => $at['items'],
                'issues_found'    => $at['issues'],
                'started_at'      => $now->copy()->subHours(2),
                'completed_at'    => $now->copy()->subHours(1),
                'error'           => null,
                'created_at'      => $now->copy()->subDays(rand(0, 5)),
                'updated_at'      => $now,
            ]);
        }

        $this->command->info('Seeding cross-border rules...');
        $countries = [
            ['code' => 'GH', 'name' => 'Ghana', 'risk' => 'low', 'req' => ['reporting_threshold' => 'GHS 20,000', 'required_documents' => ['BVN', 'Passport']], 'res' => ['max_transfer' => 'USD 10,000/day']],
            ['code' => 'US', 'name' => 'United States', 'risk' => 'low', 'req' => ['reporting_threshold' => 'USD 10,000', 'required_documents' => ['BVN', 'SSN verification']], 'res' => ['max_transfer' => 'USD 50,000/day']],
            ['code' => 'GB', 'name' => 'United Kingdom', 'risk' => 'low', 'req' => ['reporting_threshold' => 'GBP 10,000', 'required_documents' => ['BVN', 'ID']], 'res' => ['max_transfer' => 'GBP 50,000/day']],
            ['code' => 'CN', 'name' => 'China', 'risk' => 'medium', 'req' => ['reporting_threshold' => 'CNY 50,000', 'required_documents' => ['BVN', 'Trade documents']], 'res' => ['max_transfer' => 'USD 20,000/day']],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'risk' => 'medium', 'req' => ['reporting_threshold' => 'AED 55,000', 'required_documents' => ['BVN', 'EDD required']], 'res' => ['max_transfer' => 'USD 25,000/day']],
            ['code' => 'ZA', 'name' => 'South Africa', 'risk' => 'low', 'req' => ['reporting_threshold' => 'ZAR 25,000', 'required_documents' => ['BVN', 'ID']], 'res' => ['max_transfer' => 'USD 15,000/day']],
            ['code' => 'IN', 'name' => 'India', 'risk' => 'medium', 'req' => ['reporting_threshold' => 'INR 1,000,000', 'required_documents' => ['BVN', 'PAN']], 'res' => ['max_transfer' => 'USD 10,000/day']],
            ['code' => 'IR', 'name' => 'Iran', 'risk' => 'prohibited', 'req' => ['reporting_threshold' => 'N/A'], 'res' => ['max_transfer' => 'PROHIBITED', 'sanctions_programs' => 'OFAC, EU, UN']],
            ['code' => 'KP', 'name' => 'North Korea', 'risk' => 'prohibited', 'req' => ['reporting_threshold' => 'N/A'], 'res' => ['max_transfer' => 'PROHIBITED', 'sanctions_programs' => 'OFAC, EU, UN']],
            ['code' => 'SY', 'name' => 'Syria', 'risk' => 'prohibited', 'req' => ['reporting_threshold' => 'N/A'], 'res' => ['max_transfer' => 'PROHIBITED', 'sanctions_programs' => 'OFAC, EU']],
        ];

        foreach ($countries as $c) {
            DB::table('cross_border_rules')->insert([
                'id'            => Str::uuid(),
                'tenant_id'     => $tenantId,
                'country_code'  => $c['code'],
                'country_name'  => $c['name'],
                'requirements'  => json_encode($c['req']),
                'restrictions'  => json_encode($c['res']),
                'risk_category' => $c['risk'],
                'is_active'     => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        $this->command->info('Seeding regulatory simulations...');
        $sims = [
            [
                'name'   => 'CAR Increase to 15% Impact Analysis',
                'desc'   => 'Simulate the impact of CBN increasing minimum Capital Adequacy Ratio from 10% to 15% for commercial banks.',
                'params' => ['regulation_change' => 'increase_car', 'new_threshold' => 15],
                'status' => 'completed',
                'baseline' => ['capital_adequacy_ratio' => 12.5, 'total_loan_portfolio' => 15000000000, 'shareholder_equity' => 2500000000, 'total_assets' => 20000000000, 'customer_count' => 15000],
                'simulated' => ['required_car' => 15, 'capital_gap' => 500000000, 'capital_adequacy_ratio' => 12.5, 'total_loan_portfolio' => 15000000000],
                'impact' => ['affected_products' => ['lending', 'investment'], 'capital_gap' => 500000000, 'timeline' => '6-12 months', 'cost' => 550000000, 'lending_reduction' => '3.3%'],
                'rec' => 'The institution should prepare a 12-month capital raising plan. Estimated compliance cost: NGN 550,000,000. Consider retained earnings, rights issue, or subordinated debt. Recommend engaging CBN early for phased implementation.',
            ],
            [
                'name'   => 'Enhanced KYC/CDD Requirements Impact',
                'desc'   => 'Simulate the operational impact of implementing NFIU enhanced due diligence requirements for all customer segments.',
                'params' => ['regulation_change' => 'stricter_kyc_cdd'],
                'status' => 'draft',
                'baseline' => null, 'simulated' => null, 'impact' => null, 'rec' => null,
            ],
        ];

        foreach ($sims as $sim) {
            DB::table('regulatory_simulations')->insert([
                'id'                => Str::uuid(),
                'tenant_id'         => $tenantId,
                'name'              => $sim['name'],
                'description'       => $sim['desc'],
                'scenario_params'   => json_encode($sim['params']),
                'baseline_metrics'  => $sim['baseline'] ? json_encode($sim['baseline']) : null,
                'simulated_metrics' => $sim['simulated'] ? json_encode($sim['simulated']) : null,
                'impact_analysis'   => $sim['impact'] ? json_encode($sim['impact']) : null,
                'ai_recommendation' => $sim['rec'],
                'status'            => $sim['status'],
                'created_by'        => $userId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('AdvancedComplianceSeeder completed successfully!');
    }
}
