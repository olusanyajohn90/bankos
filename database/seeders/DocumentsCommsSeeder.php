<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentsCommsSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        // Fetch real user IDs
        $users = DB::table('users')
            ->where('tenant_id', $this->tenantId)
            ->pluck('name', 'id')
            ->toArray();

        $userIds = array_keys($users);

        // Fetch real customer IDs
        $customers = DB::table('customers')
            ->where('tenant_id', $this->tenantId)
            ->limit(10)
            ->pluck('id')
            ->toArray();

        if (empty($userIds) || empty($customers)) {
            $this->command->warn('No users or customers found for tenant. Aborting.');
            return;
        }

        $now = Carbon::now();

        // ─── 1. DOCUMENT FOLDERS ────────────────────────────────────────
        $folders = [
            ['id' => Str::uuid()->toString(), 'name' => 'KYC Documents', 'icon' => 'id-card', 'description' => 'Know Your Customer identity and verification documents', 'is_system' => true, 'sort_order' => 1],
            ['id' => Str::uuid()->toString(), 'name' => 'Loan Files', 'icon' => 'file-invoice-dollar', 'description' => 'Loan application and disbursement files', 'is_system' => true, 'sort_order' => 2],
            ['id' => Str::uuid()->toString(), 'name' => 'Legal & Compliance', 'icon' => 'balance-scale', 'description' => 'Regulatory filings, legal opinions, and compliance records', 'is_system' => true, 'sort_order' => 3],
            ['id' => Str::uuid()->toString(), 'name' => 'HR & Staff Records', 'icon' => 'users', 'description' => 'Employee contracts, appraisals, and personnel files', 'is_system' => false, 'sort_order' => 4],
            ['id' => Str::uuid()->toString(), 'name' => 'Board & Minutes', 'icon' => 'gavel', 'description' => 'Board meeting minutes and resolutions', 'is_system' => false, 'sort_order' => 5],
            ['id' => Str::uuid()->toString(), 'name' => 'Collateral Files', 'icon' => 'home', 'description' => 'Property deeds, vehicle titles, and collateral valuations', 'is_system' => false, 'sort_order' => 6],
        ];

        foreach ($folders as &$f) {
            $f['tenant_id'] = $this->tenantId;
            $f['parent_id'] = null;
            $f['created_at'] = $now;
            $f['updated_at'] = $now;
        }
        unset($f);
        DB::table('document_folders')->insert($folders);

        $folderMap = array_column($folders, 'id', 'name');

        // ─── 2. DOCUMENTS ───────────────────────────────────────────────
        $documentDefs = [
            // KYC documents linked to customers
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[0], 'document_type' => 'national_id', 'document_category' => 'identity', 'title' => 'NIN Slip - Yetunde Nwosu', 'file_name' => 'nin_yetunde_nwosu.pdf', 'status' => 'approved', 'folder' => 'KYC Documents', 'is_required' => true, 'source' => 'portal', 'direction' => 'inbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[0], 'document_type' => 'utility_bill', 'document_category' => 'identity', 'title' => 'PHCN Bill - Yetunde Nwosu', 'file_name' => 'utility_yetunde_nwosu.pdf', 'status' => 'approved', 'folder' => 'KYC Documents', 'is_required' => true, 'source' => 'portal', 'direction' => 'inbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[1], 'document_type' => 'passport', 'document_category' => 'identity', 'title' => 'International Passport - Emeka Adeleke', 'file_name' => 'passport_emeka_adeleke.pdf', 'status' => 'approved', 'folder' => 'KYC Documents', 'is_required' => true, 'source' => 'internal', 'direction' => 'inbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[2], 'document_type' => 'drivers_license', 'document_category' => 'identity', 'title' => 'Driver\'s License - Obiageli Nwachukwu', 'file_name' => 'dl_obiageli.pdf', 'status' => 'pending', 'folder' => 'KYC Documents', 'is_required' => true, 'source' => 'portal', 'direction' => 'inbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[3], 'document_type' => 'bank_reference', 'document_category' => 'financial', 'title' => 'Bank Reference Letter - Suleiman Afolabi', 'file_name' => 'bank_ref_suleiman.pdf', 'status' => 'approved', 'folder' => 'KYC Documents', 'is_required' => false, 'source' => 'external', 'direction' => 'inbound'],

            // Loan documents
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[0], 'document_type' => 'loan_application', 'document_category' => 'financial', 'title' => 'SME Loan Application - Yetunde Nwosu', 'file_name' => 'loan_app_yetunde.pdf', 'status' => 'approved', 'folder' => 'Loan Files', 'is_required' => true, 'source' => 'internal', 'direction' => 'internal'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[1], 'document_type' => 'loan_agreement', 'document_category' => 'legal', 'title' => 'Personal Loan Agreement - Emeka Adeleke', 'file_name' => 'loan_agreement_emeka.pdf', 'status' => 'approved', 'folder' => 'Loan Files', 'is_required' => true, 'source' => 'internal', 'direction' => 'outbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[4], 'document_type' => 'collateral_valuation', 'document_category' => 'collateral', 'title' => 'Property Valuation Report - Taiwo Fasanya', 'file_name' => 'valuation_taiwo.pdf', 'status' => 'pending', 'folder' => 'Collateral Files', 'is_required' => true, 'source' => 'external', 'direction' => 'inbound'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[5], 'document_type' => 'guarantor_form', 'document_category' => 'financial', 'title' => 'Guarantor Form - Kayode Lawal', 'file_name' => 'guarantor_kayode.pdf', 'status' => 'approved', 'folder' => 'Loan Files', 'is_required' => true, 'source' => 'internal', 'direction' => 'inbound'],

            // Legal & Compliance
            ['documentable_type' => 'App\\Models\\Tenant', 'documentable_id' => $this->tenantId, 'document_type' => 'cbn_license', 'document_category' => 'compliance', 'title' => 'CBN Microfinance Banking License', 'file_name' => 'cbn_license_2025.pdf', 'status' => 'approved', 'folder' => 'Legal & Compliance', 'is_required' => true, 'source' => 'external', 'direction' => 'inbound', 'expiry_date' => '2027-12-31'],
            ['documentable_type' => 'App\\Models\\Tenant', 'documentable_id' => $this->tenantId, 'document_type' => 'aml_policy', 'document_category' => 'compliance', 'title' => 'Anti-Money Laundering Policy v3.1', 'file_name' => 'aml_policy_v3.1.pdf', 'status' => 'approved', 'folder' => 'Legal & Compliance', 'is_required' => true, 'source' => 'internal', 'direction' => 'internal'],
            ['documentable_type' => 'App\\Models\\Tenant', 'documentable_id' => $this->tenantId, 'document_type' => 'board_resolution', 'document_category' => 'legal', 'title' => 'Board Resolution - Q1 2026 Dividend', 'file_name' => 'board_res_q1_2026.pdf', 'status' => 'approved', 'folder' => 'Board & Minutes', 'is_required' => false, 'source' => 'internal', 'direction' => 'internal'],

            // HR documents
            ['documentable_type' => 'App\\Models\\User', 'documentable_id' => (string) $userIds[4], 'document_type' => 'employment_letter', 'document_category' => 'hr', 'title' => 'Employment Offer Letter - Musa Ibrahim', 'file_name' => 'offer_musa_ibrahim.pdf', 'status' => 'approved', 'folder' => 'HR & Staff Records', 'is_required' => false, 'source' => 'internal', 'direction' => 'outbound'],
            ['documentable_type' => 'App\\Models\\User', 'documentable_id' => (string) $userIds[5], 'document_type' => 'staff_id', 'document_category' => 'hr', 'title' => 'Staff ID Card - Hauwa Suleiman', 'file_name' => 'staff_id_hauwa.pdf', 'status' => 'approved', 'folder' => 'HR & Staff Records', 'is_required' => false, 'source' => 'internal', 'direction' => 'internal'],

            // Rejected / expired for realism
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[6], 'document_type' => 'utility_bill', 'document_category' => 'identity', 'title' => 'Utility Bill (Expired) - Blessing Eze', 'file_name' => 'utility_blessing_old.pdf', 'status' => 'expired', 'folder' => 'KYC Documents', 'is_required' => true, 'source' => 'portal', 'direction' => 'inbound', 'expiry_date' => '2025-06-30'],
            ['documentable_type' => 'App\\Models\\Customer', 'documentable_id' => $customers[7], 'document_type' => 'bank_statement', 'document_category' => 'financial', 'title' => 'Bank Statement (Rejected) - Chinyere Olawale', 'file_name' => 'stmt_chinyere_rejected.pdf', 'status' => 'rejected', 'folder' => 'Loan Files', 'is_required' => true, 'source' => 'portal', 'direction' => 'inbound'],
        ];

        $documentIds = [];
        foreach ($documentDefs as $d) {
            $docId = Str::uuid()->toString();
            $documentIds[] = $docId;

            $reviewerId = in_array($d['status'], ['approved', 'rejected']) ? $userIds[array_rand(array_slice($userIds, 0, 5))] : null;
            $reviewedAt = $reviewerId ? $now->copy()->subDays(rand(1, 30)) : null;

            DB::table('documents')->insert([
                'id' => $docId,
                'tenant_id' => $this->tenantId,
                'folder_id' => $folderMap[$d['folder']] ?? null,
                'documentable_type' => $d['documentable_type'],
                'documentable_id' => $d['documentable_id'],
                'document_type' => $d['document_type'],
                'document_category' => $d['document_category'],
                'title' => $d['title'],
                'description' => null,
                'file_path' => 'documents/' . $this->tenantId . '/' . $d['file_name'],
                'file_name' => $d['file_name'],
                'mime_type' => 'application/pdf',
                'file_size_kb' => rand(120, 4500),
                'version' => 1,
                'is_current_version' => true,
                'parent_id' => null,
                'status' => $d['status'],
                'expiry_date' => $d['expiry_date'] ?? null,
                'alert_days_before' => 30,
                'is_required' => $d['is_required'],
                'is_confidential' => in_array($d['document_category'], ['hr', 'compliance']),
                'source' => $d['source'],
                'ref_number' => 'DOC-' . strtoupper(Str::random(6)),
                'direction' => $d['direction'],
                'reviewed_by' => $reviewerId,
                'review_notes' => $reviewerId ? ($d['status'] === 'rejected' ? 'Document is illegible or incomplete. Please resubmit.' : 'Verified and compliant.') : null,
                'reviewed_at' => $reviewedAt,
                'uploaded_by' => $userIds[array_rand($userIds)],
                'created_at' => $now->copy()->subDays(rand(5, 60)),
                'updated_at' => $now,
            ]);
        }

        // ─── 3. DOCUMENT ACCESS LOGS ────────────────────────────────────
        $accessActions = ['viewed', 'downloaded', 'printed'];
        $accessLogs = [];
        foreach (array_slice($documentIds, 0, 8) as $docId) {
            for ($j = 0; $j < rand(2, 5); $j++) {
                $accessLogs[] = [
                    'id' => Str::uuid()->toString(),
                    'tenant_id' => $this->tenantId,
                    'document_id' => $docId,
                    'accessed_by' => $userIds[array_rand($userIds)],
                    'action' => $accessActions[array_rand($accessActions)],
                    'ip_address' => '192.168.1.' . rand(10, 250),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'accessed_at' => $now->copy()->subHours(rand(1, 720)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('document_access_logs')->insert($accessLogs);

        // ─── 4. CBN DOCUMENT CHECKLISTS ─────────────────────────────────
        $checklists = [
            ['entity_type' => 'customer', 'document_type' => 'national_id', 'document_label' => 'National Identity Number (NIN) Slip', 'is_required' => true, 'applies_to' => 'individual', 'sort_order' => 1],
            ['entity_type' => 'customer', 'document_type' => 'passport_photo', 'document_label' => 'Recent Passport Photograph', 'is_required' => true, 'applies_to' => 'individual', 'sort_order' => 2],
            ['entity_type' => 'customer', 'document_type' => 'utility_bill', 'document_label' => 'Utility Bill (not older than 3 months)', 'is_required' => true, 'applies_to' => 'individual', 'sort_order' => 3],
            ['entity_type' => 'customer', 'document_type' => 'bvn_printout', 'document_label' => 'BVN Verification Printout', 'is_required' => true, 'applies_to' => null, 'sort_order' => 4],
            ['entity_type' => 'customer', 'document_type' => 'cac_certificate', 'document_label' => 'CAC Certificate of Incorporation', 'is_required' => true, 'applies_to' => 'corporate', 'sort_order' => 5],
            ['entity_type' => 'customer', 'document_type' => 'memart', 'document_label' => 'Memorandum & Articles of Association', 'is_required' => true, 'applies_to' => 'corporate', 'sort_order' => 6],
            ['entity_type' => 'customer', 'document_type' => 'board_resolution', 'document_label' => 'Board Resolution for Account Opening', 'is_required' => true, 'applies_to' => 'corporate', 'sort_order' => 7],
            ['entity_type' => 'customer', 'document_type' => 'reference_letter', 'document_label' => 'Reference Letter from Existing Bank', 'is_required' => false, 'applies_to' => null, 'sort_order' => 8],

            ['entity_type' => 'loan', 'document_type' => 'loan_application', 'document_label' => 'Completed Loan Application Form', 'is_required' => true, 'applies_to' => null, 'sort_order' => 1],
            ['entity_type' => 'loan', 'document_type' => 'bank_statement', 'document_label' => 'Bank Statement (6 months)', 'is_required' => true, 'applies_to' => null, 'sort_order' => 2],
            ['entity_type' => 'loan', 'document_type' => 'collateral_docs', 'document_label' => 'Collateral Documentation', 'is_required' => true, 'applies_to' => 'secured', 'sort_order' => 3],
            ['entity_type' => 'loan', 'document_type' => 'guarantor_form', 'document_label' => 'Guarantor Form (signed and witnessed)', 'is_required' => true, 'applies_to' => null, 'sort_order' => 4],
            ['entity_type' => 'loan', 'document_type' => 'salary_confirmation', 'document_label' => 'Salary Confirmation / Employment Letter', 'is_required' => true, 'applies_to' => 'salary', 'sort_order' => 5],

            ['entity_type' => 'staff_profile', 'document_type' => 'offer_letter', 'document_label' => 'Signed Offer Letter', 'is_required' => true, 'applies_to' => null, 'sort_order' => 1],
            ['entity_type' => 'staff_profile', 'document_type' => 'guarantor_form', 'document_label' => 'Staff Guarantor Form', 'is_required' => true, 'applies_to' => null, 'sort_order' => 2],
            ['entity_type' => 'staff_profile', 'document_type' => 'educational_cert', 'document_label' => 'Educational Certificates', 'is_required' => true, 'applies_to' => null, 'sort_order' => 3],
            ['entity_type' => 'staff_profile', 'document_type' => 'police_report', 'document_label' => 'Police Clearance Report', 'is_required' => false, 'applies_to' => null, 'sort_order' => 4],
        ];

        foreach ($checklists as &$c) {
            $c['id'] = Str::uuid()->toString();
            $c['tenant_id'] = $this->tenantId;
            $c['is_active'] = true;
            $c['created_at'] = $now;
            $c['updated_at'] = $now;
        }
        unset($c);
        DB::table('cbn_document_checklists')->insert($checklists);

        // ─── 5. DOCUMENT WORKFLOWS ──────────────────────────────────────
        $workflows = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'KYC Document Approval',
                'description' => 'Standard approval flow for customer identity documents',
                'trigger_category' => 'identity',
                'is_active' => true,
                'requires_all_signatures' => false,
                'steps' => [
                    ['step_order' => 1, 'name' => 'Operations Officer Review', 'action_type' => 'review', 'assignee_type' => 'role', 'assignee_role' => 'operations_officer'],
                    ['step_order' => 2, 'name' => 'Compliance Check', 'action_type' => 'approve', 'assignee_type' => 'user', 'assignee_user_id' => $userIds[0]],
                    ['step_order' => 3, 'name' => 'Branch Manager Sign-off', 'action_type' => 'approve', 'assignee_type' => 'role', 'assignee_role' => 'branch_manager'],
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Loan Document Workflow',
                'description' => 'Multi-step approval for loan origination documents',
                'trigger_category' => 'financial',
                'is_active' => true,
                'requires_all_signatures' => true,
                'steps' => [
                    ['step_order' => 1, 'name' => 'Credit Analyst Review', 'action_type' => 'review', 'assignee_type' => 'role', 'assignee_role' => 'credit_analyst'],
                    ['step_order' => 2, 'name' => 'Risk Assessment', 'action_type' => 'review', 'assignee_type' => 'role', 'assignee_role' => 'risk_officer'],
                    ['step_order' => 3, 'name' => 'Credit Committee Approval', 'action_type' => 'approve', 'assignee_type' => 'role', 'assignee_role' => 'credit_committee'],
                    ['step_order' => 4, 'name' => 'MD Final Approval', 'action_type' => 'sign', 'assignee_type' => 'user', 'assignee_user_id' => $userIds[2]],
                ],
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Legal Document Review',
                'description' => 'Review and sign-off for legal and regulatory documents',
                'trigger_category' => 'legal',
                'is_active' => true,
                'requires_all_signatures' => true,
                'steps' => [
                    ['step_order' => 1, 'name' => 'Legal Officer Review', 'action_type' => 'review', 'assignee_type' => 'role', 'assignee_role' => 'legal_officer'],
                    ['step_order' => 2, 'name' => 'Compliance Sign-off', 'action_type' => 'sign', 'assignee_type' => 'user', 'assignee_user_id' => $userIds[0]],
                ],
            ],
        ];

        $stepIds = [];
        foreach ($workflows as $wf) {
            DB::table('document_workflows')->insert([
                'id' => $wf['id'],
                'tenant_id' => $this->tenantId,
                'name' => $wf['name'],
                'description' => $wf['description'],
                'trigger_category' => $wf['trigger_category'],
                'is_active' => $wf['is_active'],
                'requires_all_signatures' => $wf['requires_all_signatures'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($wf['steps'] as $step) {
                $stepId = Str::uuid()->toString();
                $stepIds[$wf['id']][] = $stepId;
                DB::table('document_workflow_steps')->insert([
                    'id' => $stepId,
                    'workflow_id' => $wf['id'],
                    'step_order' => $step['step_order'],
                    'name' => $step['name'],
                    'action_type' => $step['action_type'],
                    'assignee_type' => $step['assignee_type'],
                    'assignee_user_id' => $step['assignee_user_id'] ?? null,
                    'assignee_role' => $step['assignee_role'] ?? null,
                    'deadline_hours' => rand(24, 72),
                    'is_optional' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ─── 6. WORKFLOW INSTANCES ──────────────────────────────────────
        // Instance 1: Completed KYC workflow
        $wfKycId = $workflows[0]['id'];
        $inst1Id = Str::uuid()->toString();
        DB::table('document_workflow_instances')->insert([
            'id' => $inst1Id,
            'document_id' => $documentIds[0], // NIN slip approved
            'workflow_id' => $wfKycId,
            'initiated_by' => $userIds[3],
            'status' => 'completed',
            'current_step_order' => 3,
            'notes' => 'All verification steps completed successfully.',
            'started_at' => $now->copy()->subDays(15),
            'completed_at' => $now->copy()->subDays(12),
            'created_at' => $now->copy()->subDays(15),
            'updated_at' => $now->copy()->subDays(12),
        ]);

        foreach ($stepIds[$wfKycId] as $i => $sId) {
            DB::table('document_workflow_actions')->insert([
                'id' => Str::uuid()->toString(),
                'instance_id' => $inst1Id,
                'step_id' => $sId,
                'assignee_id' => $userIds[min($i, count($userIds) - 1)],
                'actor_id' => $userIds[min($i, count($userIds) - 1)],
                'status' => 'approved',
                'notes' => 'Document verified and accepted.',
                'deadline_at' => $now->copy()->subDays(14 - $i),
                'acted_at' => $now->copy()->subDays(14 - $i)->addHours(rand(1, 8)),
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(14 - $i),
            ]);
        }

        // Instance 2: In-progress loan workflow (step 2 of 4)
        $wfLoanId = $workflows[1]['id'];
        $inst2Id = Str::uuid()->toString();
        DB::table('document_workflow_instances')->insert([
            'id' => $inst2Id,
            'document_id' => $documentIds[5], // loan application
            'workflow_id' => $wfLoanId,
            'initiated_by' => $userIds[1],
            'status' => 'in_progress',
            'current_step_order' => 2,
            'notes' => null,
            'started_at' => $now->copy()->subDays(5),
            'completed_at' => null,
            'created_at' => $now->copy()->subDays(5),
            'updated_at' => $now->copy()->subDays(2),
        ]);

        // Step 1 done, step 2 pending
        DB::table('document_workflow_actions')->insert([
            'id' => Str::uuid()->toString(),
            'instance_id' => $inst2Id,
            'step_id' => $stepIds[$wfLoanId][0],
            'assignee_id' => $userIds[3],
            'actor_id' => $userIds[3],
            'status' => 'approved',
            'notes' => 'Credit analysis complete. Debt-to-income ratio within limits.',
            'deadline_at' => $now->copy()->subDays(3),
            'acted_at' => $now->copy()->subDays(3)->addHours(4),
            'created_at' => $now->copy()->subDays(5),
            'updated_at' => $now->copy()->subDays(3),
        ]);
        DB::table('document_workflow_actions')->insert([
            'id' => Str::uuid()->toString(),
            'instance_id' => $inst2Id,
            'step_id' => $stepIds[$wfLoanId][1],
            'assignee_id' => $userIds[0],
            'actor_id' => null,
            'status' => 'pending',
            'notes' => null,
            'deadline_at' => $now->copy()->addDays(1),
            'acted_at' => null,
            'created_at' => $now->copy()->subDays(3),
            'updated_at' => $now->copy()->subDays(3),
        ]);

        // Instance 3: Rejected legal document
        $wfLegalId = $workflows[2]['id'];
        $inst3Id = Str::uuid()->toString();
        DB::table('document_workflow_instances')->insert([
            'id' => $inst3Id,
            'document_id' => $documentIds[11], // board resolution
            'workflow_id' => $wfLegalId,
            'initiated_by' => $userIds[2],
            'status' => 'rejected',
            'current_step_order' => 1,
            'notes' => 'Returned for revision due to missing signatory.',
            'started_at' => $now->copy()->subDays(10),
            'completed_at' => $now->copy()->subDays(8),
            'created_at' => $now->copy()->subDays(10),
            'updated_at' => $now->copy()->subDays(8),
        ]);
        DB::table('document_workflow_actions')->insert([
            'id' => Str::uuid()->toString(),
            'instance_id' => $inst3Id,
            'step_id' => $stepIds[$wfLegalId][0],
            'assignee_id' => $userIds[0],
            'actor_id' => $userIds[0],
            'status' => 'rejected',
            'notes' => 'Board resolution is missing the signature of the company secretary. Please resubmit.',
            'deadline_at' => $now->copy()->subDays(8),
            'acted_at' => $now->copy()->subDays(8)->addHours(2),
            'created_at' => $now->copy()->subDays(10),
            'updated_at' => $now->copy()->subDays(8),
        ]);

        // ─── 7. DOCUMENT NOTES ──────────────────────────────────────────
        $notes = [
            ['document_id' => $documentIds[0], 'author_id' => $userIds[0], 'body' => 'NIN verified against NIMC database. All details match.', 'is_internal' => true],
            ['document_id' => $documentIds[3], 'author_id' => $userIds[1], 'body' => 'Driver\'s license photo is slightly blurry. Requesting a clearer scan from the customer.', 'is_internal' => true],
            ['document_id' => $documentIds[5], 'author_id' => $userIds[3], 'body' => 'Loan application is complete. Forwarding to credit committee.', 'is_internal' => false],
            ['document_id' => $documentIds[7], 'author_id' => $userIds[2], 'body' => 'Valuation report received from Lagos Property Associates. Awaiting review.', 'is_internal' => true],
            ['document_id' => $documentIds[15], 'author_id' => $userIds[0], 'body' => 'Bank statement does not cover the required 6-month period. Rejected and customer notified.', 'is_internal' => false],
        ];

        foreach ($notes as $n) {
            DB::table('document_notes')->insert([
                'id' => Str::uuid()->toString(),
                'document_id' => $n['document_id'],
                'author_id' => $n['author_id'],
                'body' => $n['body'],
                'is_internal' => $n['is_internal'],
                'parent_id' => null,
                'created_at' => $now->copy()->subDays(rand(1, 20)),
                'updated_at' => $now,
            ]);
        }

        // ─── 8. CHAT CONVERSATIONS ─────────────────────────────────────
        $conversations = [
            ['id' => Str::uuid()->toString(), 'type' => 'group', 'name' => 'General', 'description' => 'Company-wide announcements and general discussion'],
            ['id' => Str::uuid()->toString(), 'type' => 'group', 'name' => 'Operations Team', 'description' => 'Daily operations coordination and updates'],
            ['id' => Str::uuid()->toString(), 'type' => 'group', 'name' => 'Loan Committee', 'description' => 'Loan application discussions and approvals'],
            ['id' => Str::uuid()->toString(), 'type' => 'group', 'name' => 'Compliance & Risk', 'description' => 'Regulatory updates and compliance matters'],
            ['id' => Str::uuid()->toString(), 'type' => 'direct', 'name' => null, 'description' => null],
            ['id' => Str::uuid()->toString(), 'type' => 'direct', 'name' => null, 'description' => null],
        ];

        foreach ($conversations as &$conv) {
            $conv['tenant_id'] = $this->tenantId;
            $conv['created_by'] = $userIds[2]; // Bank Admin
            $conv['last_message_at'] = $now->copy()->subMinutes(rand(5, 300));
            $conv['last_message_preview'] = null;
            $conv['is_archived'] = false;
            $conv['created_at'] = $now->copy()->subDays(60);
            $conv['updated_at'] = $now;
        }
        unset($conv);
        DB::table('chat_conversations')->insert($conversations);

        // ─── 9. CHAT PARTICIPANTS ───────────────────────────────────────
        $participantData = [];
        // Group chats: add multiple users
        // General: all users
        foreach ($userIds as $uid) {
            $participantData[] = [
                'conversation_id' => $conversations[0]['id'],
                'user_id' => $uid,
                'role' => $uid === $userIds[2] ? 'admin' : 'member',
                'joined_at' => $now->copy()->subDays(60),
                'last_read_at' => $now->copy()->subMinutes(rand(0, 120)),
                'left_at' => null,
                'created_at' => $now->copy()->subDays(60),
                'updated_at' => $now,
            ];
        }
        // Operations: first 8 users
        foreach (array_slice($userIds, 0, 8) as $uid) {
            $participantData[] = [
                'conversation_id' => $conversations[1]['id'],
                'user_id' => $uid,
                'role' => $uid === $userIds[2] ? 'admin' : 'member',
                'joined_at' => $now->copy()->subDays(55),
                'last_read_at' => $now->copy()->subMinutes(rand(0, 60)),
                'left_at' => null,
                'created_at' => $now->copy()->subDays(55),
                'updated_at' => $now,
            ];
        }
        // Loan Committee: select users
        $loanCommitteeUsers = [$userIds[0], $userIds[1], $userIds[2], $userIds[3], $userIds[8]];
        foreach ($loanCommitteeUsers as $uid) {
            $participantData[] = [
                'conversation_id' => $conversations[2]['id'],
                'user_id' => $uid,
                'role' => $uid === $userIds[2] ? 'admin' : 'member',
                'joined_at' => $now->copy()->subDays(50),
                'last_read_at' => $now->copy()->subMinutes(rand(0, 180)),
                'left_at' => null,
                'created_at' => $now->copy()->subDays(50),
                'updated_at' => $now,
            ];
        }
        // Compliance
        $complianceUsers = [$userIds[0], $userIds[1], $userIds[2]];
        foreach ($complianceUsers as $uid) {
            $participantData[] = [
                'conversation_id' => $conversations[3]['id'],
                'user_id' => $uid,
                'role' => 'admin',
                'joined_at' => $now->copy()->subDays(50),
                'last_read_at' => $now->copy()->subMinutes(rand(0, 60)),
                'left_at' => null,
                'created_at' => $now->copy()->subDays(50),
                'updated_at' => $now,
            ];
        }
        // Direct message 1: two users
        $participantData[] = ['conversation_id' => $conversations[4]['id'], 'user_id' => $userIds[3], 'role' => 'member', 'joined_at' => $now->copy()->subDays(10), 'last_read_at' => $now->copy()->subMinutes(15), 'left_at' => null, 'created_at' => $now->copy()->subDays(10), 'updated_at' => $now];
        $participantData[] = ['conversation_id' => $conversations[4]['id'], 'user_id' => $userIds[4], 'role' => 'member', 'joined_at' => $now->copy()->subDays(10), 'last_read_at' => $now->copy()->subMinutes(45), 'left_at' => null, 'created_at' => $now->copy()->subDays(10), 'updated_at' => $now];
        // Direct message 2: two users
        $participantData[] = ['conversation_id' => $conversations[5]['id'], 'user_id' => $userIds[2], 'role' => 'member', 'joined_at' => $now->copy()->subDays(3), 'last_read_at' => $now->copy()->subMinutes(5), 'left_at' => null, 'created_at' => $now->copy()->subDays(3), 'updated_at' => $now];
        $participantData[] = ['conversation_id' => $conversations[5]['id'], 'user_id' => $userIds[0], 'role' => 'member', 'joined_at' => $now->copy()->subDays(3), 'last_read_at' => $now->copy()->subHours(2), 'left_at' => null, 'created_at' => $now->copy()->subDays(3), 'updated_at' => $now];

        DB::table('chat_participants')->insert($participantData);

        // ─── 10. CHAT MESSAGES ──────────────────────────────────────────
        $chatMessageDefs = [
            // General channel
            ['conv' => 0, 'sender' => 2, 'body' => 'Good morning everyone. Reminder that our quarterly CBN returns are due by end of this week. All heads of department please ensure your reports are submitted to Compliance.', 'mins_ago' => 180],
            ['conv' => 0, 'sender' => 0, 'body' => 'Thank you. The compliance report template has been shared in the Documents section. Please use version 3.1.', 'mins_ago' => 170],
            ['conv' => 0, 'sender' => 5, 'body' => 'Noted. Operations report will be ready by Wednesday.', 'mins_ago' => 155],
            ['conv' => 0, 'sender' => 2, 'body' => 'Also, we will be conducting our fire drill on Friday at 2:00 PM. Please ensure all staff are briefed.', 'mins_ago' => 90],
            ['conv' => 0, 'sender' => 7, 'body' => 'Will the Lekki branch participate remotely?', 'mins_ago' => 85],
            ['conv' => 0, 'sender' => 2, 'body' => 'Yes, Lekki branch should conduct their own drill simultaneously. Tonye will coordinate.', 'mins_ago' => 80],

            // Operations Team
            ['conv' => 1, 'sender' => 3, 'body' => 'We have 12 pending account opening requests from yesterday. Can the KYC team prioritize these today?', 'mins_ago' => 240],
            ['conv' => 1, 'sender' => 4, 'body' => 'I will handle the first batch. How many have complete documentation?', 'mins_ago' => 230],
            ['conv' => 1, 'sender' => 3, 'body' => '8 out of 12 have complete docs. The remaining 4 are waiting for BVN verification.', 'mins_ago' => 225],
            ['conv' => 1, 'sender' => 5, 'body' => 'The NIBSS BVN portal was down earlier. It is back up now. I will run the verifications.', 'mins_ago' => 200],
            ['conv' => 1, 'sender' => 0, 'body' => 'Please flag any accounts that fail the BVN check. We need to report those to compliance.', 'mins_ago' => 190],
            ['conv' => 1, 'sender' => 6, 'body' => 'Cash vault balance reconciled for yesterday. All clear, no discrepancies.', 'mins_ago' => 60],

            // Loan Committee
            ['conv' => 2, 'sender' => 3, 'body' => 'Presenting Yetunde Nwosu\'s SME loan application for N5,000,000. Business: textile trading at Balogun Market. 3 years trading history, collateral is shop at Balogun.', 'mins_ago' => 360],
            ['conv' => 2, 'sender' => 8, 'body' => 'What is the debt-to-income ratio?', 'mins_ago' => 350],
            ['conv' => 2, 'sender' => 3, 'body' => 'DTI is 28%. Well within our 40% threshold. Monthly turnover averages N2.8M based on 6-month bank statement.', 'mins_ago' => 340],
            ['conv' => 2, 'sender' => 0, 'body' => 'KYC is fully verified. No adverse findings from our AML screening.', 'mins_ago' => 330],
            ['conv' => 2, 'sender' => 2, 'body' => 'Approved. Please prepare the offer letter and ensure the collateral perfection documents are filed.', 'mins_ago' => 310],
            ['conv' => 2, 'sender' => 1, 'body' => 'We also need to review Kayode Lawal\'s request for a top-up. His current facility of N2M has been performing well for 8 months.', 'mins_ago' => 120],

            // Compliance & Risk
            ['conv' => 3, 'sender' => 0, 'body' => 'New CBN circular on anti-money laundering: effective April 1st, all MFBs must implement enhanced due diligence for transactions above N5M.', 'mins_ago' => 500],
            ['conv' => 3, 'sender' => 2, 'body' => 'We should schedule a training session for frontline staff. Can you prepare the materials?', 'mins_ago' => 480],
            ['conv' => 3, 'sender' => 0, 'body' => 'Already working on it. I will have the training deck ready by next Monday. Also updating our internal AML policy document.', 'mins_ago' => 470],
            ['conv' => 3, 'sender' => 1, 'body' => 'I flagged a suspicious transaction pattern on account 0012345678. Three deposits of N4.9M each within 48 hours. Looks like structuring.', 'mins_ago' => 200],
            ['conv' => 3, 'sender' => 0, 'body' => 'Good catch. Please file a Suspicious Transaction Report immediately. I will review and submit to NFIU.', 'mins_ago' => 190],

            // Direct message 1: Loan Officer to Musa
            ['conv' => 4, 'sender' => 3, 'body' => 'Musa, can you follow up with Taiwo Fasanya on the property valuation report? It has been pending for a week now.', 'mins_ago' => 60],
            ['conv' => 4, 'sender' => 4, 'body' => 'I spoke with the valuer yesterday. They promised to deliver by tomorrow. I will send a reminder email today.', 'mins_ago' => 45],
            ['conv' => 4, 'sender' => 3, 'body' => 'Thank you. Once we have that, we can move the loan file to committee.', 'mins_ago' => 40],

            // Direct message 2: Bank Admin to Compliance
            ['conv' => 5, 'sender' => 2, 'body' => 'Have you completed the review of the updated KYC documents for Suleiman Afolabi?', 'mins_ago' => 30],
            ['conv' => 5, 'sender' => 0, 'body' => 'Yes, all documents are in order. I have approved them in the system. The reference letter from First Bank was the last piece.', 'mins_ago' => 20],
            ['conv' => 5, 'sender' => 2, 'body' => 'Perfect. Please also look at the new corporate account opening request from Adekunle & Sons Ltd.', 'mins_ago' => 10],
        ];

        $chatMessageIds = [];
        foreach ($chatMessageDefs as $m) {
            $msgId = Str::uuid()->toString();
            $chatMessageIds[] = $msgId;
            DB::table('chat_messages')->insert([
                'id' => $msgId,
                'tenant_id' => $this->tenantId,
                'conversation_id' => $conversations[$m['conv']]['id'],
                'sender_id' => $userIds[$m['sender']],
                'reply_to_id' => null,
                'body' => $m['body'],
                'type' => 'text',
                'is_edited' => false,
                'edited_at' => null,
                'is_deleted' => false,
                'deleted_at' => null,
                'created_at' => $now->copy()->subMinutes($m['mins_ago']),
                'updated_at' => $now->copy()->subMinutes($m['mins_ago']),
            ]);
        }

        // Update last_message_preview on conversations
        $convLastMessages = [
            $conversations[0]['id'] => 'Yes, Lekki branch should conduct their own drill simultaneously.',
            $conversations[1]['id'] => 'Cash vault balance reconciled for yesterday. All clear.',
            $conversations[2]['id'] => 'We also need to review Kayode Lawal\'s request for a top-up.',
            $conversations[3]['id'] => 'Good catch. Please file a Suspicious Transaction Report.',
            $conversations[4]['id'] => 'Thank you. Once we have that, we can move the loan file.',
            $conversations[5]['id'] => 'Please also look at the new corporate account opening request.',
        ];
        foreach ($convLastMessages as $cid => $preview) {
            DB::table('chat_conversations')->where('id', $cid)->update(['last_message_preview' => $preview]);
        }

        // ─── 11. COMMS MESSAGES ─────────────────────────────────────────
        $commsMessages = [
            [
                'id' => Str::uuid()->toString(),
                'type' => 'announcement',
                'subject' => 'Q1 2026 Performance Review Schedule',
                'body' => "<p>Dear Team,</p><p>The Q1 2026 performance review cycle will commence on <strong>April 5th, 2026</strong>. All line managers are expected to complete their team assessments by April 20th.</p><p>Key dates:</p><ul><li>Self-assessment deadline: April 10th</li><li>Manager review: April 10th - 18th</li><li>Calibration meeting: April 19th</li><li>Final ratings due: April 20th</li></ul><p>Please contact HR for any questions.</p><p>Regards,<br>Management</p>",
                'priority' => 'normal',
                'requires_ack' => false,
                'scope_type' => 'all_staff',
                'scope_id' => null,
                'status' => 'published',
                'published_at' => $now->copy()->subDays(5),
                'sender_idx' => 2,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'circular',
                'subject' => 'Updated Cash Handling Procedures - Effective Immediately',
                'body' => "<p>All Staff,</p><p>Following the recent CBN directive on cash management in microfinance banks, the following updated procedures are <strong>effective immediately</strong>:</p><ol><li>All cash transactions above N500,000 must be dual-verified by two tellers</li><li>End-of-day cash count must be witnessed by the Operations Manager or designated alternate</li><li>Cash vault access requires biometric authentication and physical key (dual control)</li><li>Any cash discrepancy above N1,000 must be reported within 1 hour to the Branch Manager and Internal Audit</li></ol><p>Non-compliance will attract disciplinary action as per our HR policy section 12.3.</p><p>Compliance Officer</p>",
                'priority' => 'urgent',
                'requires_ack' => true,
                'ack_deadline' => $now->copy()->addDays(3)->toDateString(),
                'scope_type' => 'all_staff',
                'scope_id' => null,
                'status' => 'published',
                'published_at' => $now->copy()->subDays(2),
                'sender_idx' => 0,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'memo',
                'subject' => 'Loan Portfolio Quality Report - February 2026',
                'body' => "<p>Dear Credit Team,</p><p>Please find the summary of our February 2026 loan portfolio performance:</p><ul><li>Total loan portfolio: N1.2 Billion</li><li>PAR > 30 days: 3.8% (target: below 5%)</li><li>PAR > 90 days: 1.2%</li><li>Write-offs this month: N2.4M</li><li>Recovery rate on written-off loans: 18%</li></ul><p>We are within acceptable limits but need to closely monitor the agricultural portfolio which showed a slight uptick in delinquency. Please schedule follow-up visits for all agric loans above N1M with repayments overdue by more than 15 days.</p><p>Best regards,<br>Loan Officer</p>",
                'priority' => 'normal',
                'requires_ack' => false,
                'scope_type' => 'role',
                'scope_id' => 'credit_team',
                'status' => 'published',
                'published_at' => $now->copy()->subDays(8),
                'sender_idx' => 1,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'circular',
                'subject' => 'System Maintenance Notice - March 25, 2026',
                'body' => "<p>Dear Colleagues,</p><p>Please be informed that the core banking system will undergo scheduled maintenance on <strong>Wednesday, March 25th, 2026 from 10:00 PM to 2:00 AM</strong> (Thursday).</p><p>During this window:</p><ul><li>All online banking services will be temporarily unavailable</li><li>Mobile app transactions will be queued and processed after maintenance</li><li>ATM services will remain operational for balance inquiry only</li></ul><p>Please ensure all critical transactions are completed before 9:30 PM. Customers should be informed proactively.</p><p>IT Department</p>",
                'priority' => 'urgent',
                'requires_ack' => true,
                'ack_deadline' => $now->copy()->addDays(2)->toDateString(),
                'scope_type' => 'all_staff',
                'scope_id' => null,
                'status' => 'published',
                'published_at' => $now->copy()->subDays(1),
                'sender_idx' => 2,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'memo',
                'subject' => 'New Staff Onboarding - Week of March 23',
                'body' => "<p>HR Team,</p><p>We have two new hires joining us next week:</p><ol><li><strong>Adebayo Ogundimu</strong> - Teller, Head Office (reports to Operations Manager)</li><li><strong>Folake Aderibigbe</strong> - Customer Service Officer, Lekki Branch</li></ol><p>Please ensure the following are ready before their start date:</p><ul><li>Staff ID cards</li><li>System access credentials</li><li>Workstation setup</li><li>Welcome pack and policy handbook</li></ul><p>Orientation schedule will be shared separately.</p>",
                'priority' => 'normal',
                'requires_ack' => false,
                'scope_type' => 'department',
                'scope_id' => 'hr',
                'status' => 'published',
                'published_at' => $now->copy()->subDays(1),
                'sender_idx' => 2,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'announcement',
                'subject' => 'NDIC Premium Payment Confirmation',
                'body' => "<p>For the records,</p><p>This is to confirm that the Nigeria Deposit Insurance Corporation (NDIC) annual premium payment of N3,450,000 has been remitted for the 2025/2026 financial year. Payment reference: NDIC-MFB-2026-0847.</p><p>Receipt has been filed in the Legal & Compliance documents folder.</p><p>Compliance Department</p>",
                'priority' => 'normal',
                'requires_ack' => false,
                'scope_type' => 'role',
                'scope_id' => 'management',
                'status' => 'published',
                'published_at' => $now->copy()->subDays(12),
                'sender_idx' => 0,
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'memo',
                'subject' => 'Draft: Customer Appreciation Day Planning',
                'body' => "<p>Team,</p><p>Draft memo for the upcoming Customer Appreciation Day event scheduled for April 15th. This is still in planning phase - please review and add your suggestions.</p><p>Proposed activities: refreshments, loyalty awards, savings raffle draw, financial literacy talk.</p>",
                'priority' => 'normal',
                'requires_ack' => false,
                'scope_type' => 'all_staff',
                'scope_id' => null,
                'status' => 'draft',
                'published_at' => null,
                'sender_idx' => 2,
            ],
        ];

        foreach ($commsMessages as $cm) {
            DB::table('comms_messages')->insert([
                'id' => $cm['id'],
                'tenant_id' => $this->tenantId,
                'type' => $cm['type'],
                'subject' => $cm['subject'],
                'body' => $cm['body'],
                'priority' => $cm['priority'],
                'requires_ack' => $cm['requires_ack'],
                'ack_deadline' => $cm['ack_deadline'] ?? null,
                'sender_id' => $userIds[$cm['sender_idx']],
                'scope_type' => $cm['scope_type'],
                'scope_id' => $cm['scope_id'],
                'status' => $cm['status'],
                'published_at' => $cm['published_at'],
                'archived_at' => null,
                'created_at' => $cm['published_at'] ?? $now,
                'updated_at' => $now,
            ]);
        }

        // ─── 12. COMMS RECIPIENTS ───────────────────────────────────────
        $recipientData = [];

        // For all_staff published messages, add all users as recipients
        $allStaffMsgIds = [$commsMessages[0]['id'], $commsMessages[1]['id'], $commsMessages[3]['id']];
        foreach ($allStaffMsgIds as $msgId) {
            foreach ($userIds as $idx => $uid) {
                $isRead = rand(0, 100) > 25; // 75% read rate
                $recipientData[] = [
                    'tenant_id' => $this->tenantId,
                    'message_id' => $msgId,
                    'user_id' => $uid,
                    'read_at' => $isRead ? $now->copy()->subHours(rand(1, 48)) : null,
                    'ack_at' => ($msgId === $commsMessages[1]['id'] && $isRead && rand(0, 100) > 40)
                        ? $now->copy()->subHours(rand(1, 24))
                        : null,
                    'ack_note' => null,
                    'created_at' => $now->copy()->subDays(5),
                    'updated_at' => $now,
                ];
            }
        }

        // Loan portfolio memo: credit team members
        $creditTeamUsers = [$userIds[1], $userIds[3], $userIds[8]];
        foreach ($creditTeamUsers as $uid) {
            $recipientData[] = [
                'tenant_id' => $this->tenantId,
                'message_id' => $commsMessages[2]['id'],
                'user_id' => $uid,
                'read_at' => $now->copy()->subHours(rand(2, 72)),
                'ack_at' => null,
                'ack_note' => null,
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now,
            ];
        }

        // HR memo
        $hrUsers = [$userIds[2], $userIds[5], $userIds[7]];
        foreach ($hrUsers as $uid) {
            $recipientData[] = [
                'tenant_id' => $this->tenantId,
                'message_id' => $commsMessages[4]['id'],
                'user_id' => $uid,
                'read_at' => $uid === $userIds[2] ? $now->copy()->subHours(3) : null,
                'ack_at' => null,
                'ack_note' => null,
                'created_at' => $now->copy()->subDays(1),
                'updated_at' => $now,
            ];
        }

        // NDIC memo: management
        $mgmtUsers = [$userIds[0], $userIds[1], $userIds[2]];
        foreach ($mgmtUsers as $uid) {
            $recipientData[] = [
                'tenant_id' => $this->tenantId,
                'message_id' => $commsMessages[5]['id'],
                'user_id' => $uid,
                'read_at' => $now->copy()->subDays(rand(1, 10)),
                'ack_at' => null,
                'ack_note' => null,
                'created_at' => $now->copy()->subDays(12),
                'updated_at' => $now,
            ];
        }

        DB::table('comms_recipients')->insert($recipientData);

        $this->command->info('DocumentsCommsSeeder completed successfully.');
        $this->command->info('  - ' . count($folders) . ' document folders');
        $this->command->info('  - ' . count($documentDefs) . ' documents');
        $this->command->info('  - ' . count($accessLogs) . ' access logs');
        $this->command->info('  - ' . count($checklists) . ' CBN checklist items');
        $this->command->info('  - ' . count($workflows) . ' workflows with steps & instances');
        $this->command->info('  - ' . count($conversations) . ' chat conversations');
        $this->command->info('  - ' . count($chatMessageDefs) . ' chat messages');
        $this->command->info('  - ' . count($commsMessages) . ' comms messages');
        $this->command->info('  - ' . count($recipientData) . ' comms recipients');
    }
}
