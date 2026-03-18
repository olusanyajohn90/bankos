<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemDataSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';
    private int $adminUserId = 3;   // Bank Admin
    private int $managerUserId = 7; // Adewale Balogun - HO manager

    public function run(): void
    {
        $this->command->info('Starting SystemDataSeeder...');

        $this->seedPayComponents();
        $this->seedStaffPayConfigs();
        $this->seedPayrollRuns();
        $this->seedTrainingPrograms();
        $this->seedReviewCycles();
        $this->seedPerformanceReviews();
        $this->seedLoanApplications();
        $this->seedKycUpgradeRequests();

        $this->command->info('SystemDataSeeder completed!');
    }

    // -------------------------------------------------------------------------
    // 1. PAY COMPONENTS
    // -------------------------------------------------------------------------
    private function seedPayComponents(): void
    {
        try {
            $this->command->info('Seeding pay components...');

            $components = [
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Basic Salary',
                    'code'             => 'BASIC',
                    'type'             => 'earning',
                    'is_statutory'     => false,
                    'is_taxable'       => true,
                    'computation_type' => 'fixed',
                    'value'            => null,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Housing Allowance',
                    'code'             => 'HOUSING',
                    'type'             => 'earning',
                    'is_statutory'     => false,
                    'is_taxable'       => true,
                    'computation_type' => 'percentage_of_basic',
                    'value'            => 25.0000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Transport Allowance',
                    'code'             => 'TRANSPORT',
                    'type'             => 'earning',
                    'is_statutory'     => false,
                    'is_taxable'       => true,
                    'computation_type' => 'percentage_of_basic',
                    'value'            => 15.0000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Meal Allowance',
                    'code'             => 'MEAL',
                    'type'             => 'earning',
                    'is_statutory'     => false,
                    'is_taxable'       => false,
                    'computation_type' => 'fixed',
                    'value'            => 10000.0000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Utility Allowance',
                    'code'             => 'UTILITY',
                    'type'             => 'earning',
                    'is_statutory'     => false,
                    'is_taxable'       => true,
                    'computation_type' => 'percentage_of_basic',
                    'value'            => 10.0000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                // Deductions
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'Employee Pension (8%)',
                    'code'             => 'PEN_EMP',
                    'type'             => 'deduction',
                    'is_statutory'     => true,
                    'is_taxable'       => false,
                    'computation_type' => 'percentage_of_basic',
                    'value'            => 8.0000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'PAYE Tax',
                    'code'             => 'PAYE',
                    'type'             => 'deduction',
                    'is_statutory'     => true,
                    'is_taxable'       => false,
                    'computation_type' => 'formula',
                    'value'            => null,
                    'formula_key'      => 'paye_ng',
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
                [
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'name'             => 'NHF (2.5%)',
                    'code'             => 'NHF',
                    'type'             => 'deduction',
                    'is_statutory'     => true,
                    'is_taxable'       => false,
                    'computation_type' => 'percentage_of_basic',
                    'value'            => 2.5000,
                    'formula_key'      => null,
                    'is_active'        => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ],
            ];

            foreach ($components as $component) {
                $exists = DB::table('pay_components')
                    ->where('tenant_id', $this->tenantId)
                    ->where('code', $component['code'])
                    ->exists();

                if (!$exists) {
                    DB::table('pay_components')->insert($component);
                }
            }

            $count = DB::table('pay_components')->where('tenant_id', $this->tenantId)->count();
            $this->command->info("  Pay components: {$count} total");

        } catch (\Throwable $e) {
            $this->command->error('Pay components failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // 2. STAFF PAY CONFIGS
    // -------------------------------------------------------------------------
    private function seedStaffPayConfigs(): void
    {
        try {
            $this->command->info('Seeding staff pay configs...');

            $staffProfiles = DB::table('staff_profiles')
                ->where('tenant_id', $this->tenantId)
                ->get(['id', 'user_id', 'job_title']);

            $payGrades = DB::table('pay_grades')
                ->where('tenant_id', $this->tenantId)
                ->orderBy('level')
                ->get(['id', 'code', 'level', 'basic_min', 'basic_max'])
                ->keyBy('level');

            // Map job titles to GL levels
            $titleToLevel = [
                'General Manager'        => 10,
                'Compliance Manager'     => 9,
                'Credit Analyst'         => 7,
                'HR Officer'             => 6,
                'Finance Officer'        => 7,
                'Operations Officer'     => 6,
                'Customer Service Officer' => 5,
                'IT Officer'             => 7,
                'Credit Officer'         => 6,
                'Teller'                 => 4,
            ];

            $pfaNames = [
                'ARM Pension Managers',
                'Stanbic IBTC Pensions',
                'Leadway Pensure',
                'AIICO Pension',
                'AXA Mansard Pensions',
                'Crusader Sterling Pensions',
            ];

            foreach ($staffProfiles as $staff) {
                $exists = DB::table('staff_pay_configs')
                    ->where('staff_profile_id', $staff->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $level    = $titleToLevel[$staff->job_title] ?? 5;
                $grade    = $payGrades->get($level) ?? $payGrades->first();
                $basic    = round(($grade->basic_min + $grade->basic_max) / 2, 2);
                $housing  = round($basic * 0.25, 2);
                $transport = round($basic * 0.15, 2);
                $meal     = 10000.00;

                DB::table('staff_pay_configs')->insert([
                    'id'                      => (string) Str::uuid(),
                    'tenant_id'               => $this->tenantId,
                    'staff_profile_id'        => $staff->id,
                    'pay_grade_id'            => $grade->id,
                    'basic_salary'            => $basic,
                    'housing_allowance'       => $housing,
                    'transport_allowance'     => $transport,
                    'meal_allowance'          => $meal,
                    'other_allowances'        => null,
                    'pension_fund_administrator' => $pfaNames[array_rand($pfaNames)],
                    'pension_account_number'  => 'PFA' . rand(1000000000, 9999999999),
                    'tax_id'                  => 'NG' . rand(10000000000, 99999999999),
                    'nhf_number'              => 'NHF' . rand(100000000, 999999999),
                    'effective_date'          => '2025-01-01',
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
            }

            $count = DB::table('staff_pay_configs')->where('tenant_id', $this->tenantId)->count();
            $this->command->info("  Staff pay configs: {$count} total");

        } catch (\Throwable $e) {
            $this->command->error('Staff pay configs failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // 3. PAYROLL RUNS + ITEMS
    // -------------------------------------------------------------------------
    private function seedPayrollRuns(): void
    {
        try {
            $this->command->info('Seeding payroll runs and items...');

            $staffProfiles = DB::table('staff_profiles')
                ->where('tenant_id', $this->tenantId)
                ->get(['id']);

            $payConfigs = DB::table('staff_pay_configs')
                ->where('tenant_id', $this->tenantId)
                ->get()
                ->keyBy('staff_profile_id');

            // 3 completed months + 1 draft
            $periods = [
                ['month' => 1, 'year' => 2026, 'status' => 'paid'],
                ['month' => 2, 'year' => 2026, 'status' => 'paid'],
                ['month' => 3, 'year' => 2026, 'status' => 'approved'],
                ['month' => 4, 'year' => 2026, 'status' => 'draft'],
            ];

            foreach ($periods as $period) {
                $exists = DB::table('payroll_runs')
                    ->where('tenant_id', $this->tenantId)
                    ->where('period_month', $period['month'])
                    ->where('period_year', $period['year'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $runId      = (string) Str::uuid();
                $totalGross = 0;
                $totalDed   = 0;
                $totalNet   = 0;
                $totalPaye  = 0;
                $totalPen   = 0;
                $totalNhf   = 0;

                $itemsToInsert = [];

                foreach ($staffProfiles as $staff) {
                    $cfg = $payConfigs->get($staff->id);
                    if (!$cfg) {
                        continue;
                    }

                    $basic    = (float) $cfg->basic_salary;
                    $housing  = (float) $cfg->housing_allowance;
                    $transport = (float) $cfg->transport_allowance;
                    $meal     = (float) $cfg->meal_allowance;
                    $gross    = $basic + $housing + $transport + $meal;

                    $empPension = round($basic * 0.08, 2);
                    $empPenEmployer = round($basic * 0.10, 2);
                    $nhf        = round($basic * 0.025, 2);
                    $taxable    = $gross - $empPension - $nhf;
                    // Simple PAYE estimation (Nigeria CIT graduated rates simplified)
                    $paye       = $this->calcPaye($taxable);
                    $totalDedItem = $empPension + $paye + $nhf;
                    $net        = $gross - $totalDedItem;

                    $itemId = (string) Str::uuid();
                    $paidAt = ($period['status'] === 'paid')
                        ? now()->setDate($period['year'], $period['month'], 28)->format('Y-m-d H:i:s')
                        : null;

                    $itemsToInsert[] = [
                        'id'                  => $itemId,
                        'payroll_run_id'      => $runId,
                        'staff_profile_id'    => $staff->id,
                        'gross_salary'        => $gross,
                        'taxable_income'      => $taxable,
                        'total_deductions'    => $totalDedItem,
                        'paye'                => $paye,
                        'employee_pension'    => $empPension,
                        'employer_pension'    => $empPenEmployer,
                        'nhf'                 => $nhf,
                        'nsitf'               => 0,
                        'net_salary'          => $net,
                        'bank_detail_id'      => null,
                        'payment_status'      => ($period['status'] === 'paid') ? 'paid' : 'pending',
                        'payment_date'        => $paidAt,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];

                    $totalGross += $gross;
                    $totalDed   += $totalDedItem;
                    $totalNet   += $net;
                    $totalPaye  += $paye;
                    $totalPen   += $empPension;
                    $totalNhf   += $nhf;
                }

                $runAt    = now()->setDate($period['year'], $period['month'], 25)->format('Y-m-d H:i:s');
                $approvedAt = ($period['status'] !== 'draft')
                    ? now()->setDate($period['year'], $period['month'], 26)->format('Y-m-d H:i:s')
                    : null;
                $paidAt   = ($period['status'] === 'paid')
                    ? now()->setDate($period['year'], $period['month'], 28)->format('Y-m-d H:i:s')
                    : null;

                DB::table('payroll_runs')->insert([
                    'id'                       => $runId,
                    'tenant_id'                => $this->tenantId,
                    'period_month'             => $period['month'],
                    'period_year'              => $period['year'],
                    'status'                   => $period['status'],
                    'total_gross'              => $totalGross,
                    'total_deductions'         => $totalDed,
                    'total_net'                => $totalNet,
                    'total_paye'               => $totalPaye,
                    'total_pension_employee'   => $totalPen,
                    'total_pension_employer'   => round($totalPen * 1.25, 2),
                    'total_nhf'                => $totalNhf,
                    'total_nsitf'              => 0,
                    'staff_count'              => count($itemsToInsert),
                    'run_by'                   => $this->adminUserId,
                    'approved_by'              => ($period['status'] !== 'draft') ? $this->managerUserId : null,
                    'approved_at'              => $approvedAt,
                    'paid_at'                  => $paidAt,
                    'notes'                    => 'Monthly payroll for ' . date('F Y', mktime(0, 0, 0, $period['month'], 1, $period['year'])),
                    'created_at'               => $runAt,
                    'updated_at'               => now(),
                ]);

                if (!empty($itemsToInsert)) {
                    foreach (array_chunk($itemsToInsert, 50) as $chunk) {
                        DB::table('payroll_items')->insert($chunk);
                    }
                }

                $this->command->info("  Payroll run {$period['month']}/{$period['year']} ({$period['status']}) — " . count($itemsToInsert) . ' items');
            }

        } catch (\Throwable $e) {
            $this->command->error('Payroll runs failed: ' . $e->getMessage());
        }
    }

    private function calcPaye(float $annualTaxable): float
    {
        // Nigeria PAYE 2024 graduated rates (annualised then / 12)
        $annual = $annualTaxable * 12;
        $tax    = 0;
        $bands  = [
            [300000,  0.07],
            [300000,  0.11],
            [500000,  0.15],
            [500000,  0.19],
            [1600000, 0.21],
            [PHP_INT_MAX, 0.24],
        ];
        $remaining = $annual;
        foreach ($bands as [$band, $rate]) {
            if ($remaining <= 0) {
                break;
            }
            $taxable = min($remaining, $band);
            $tax    += $taxable * $rate;
            $remaining -= $taxable;
        }
        return round($tax / 12, 2);
    }

    // -------------------------------------------------------------------------
    // 4. TRAINING PROGRAMS + ATTENDANCES
    // -------------------------------------------------------------------------
    private function seedTrainingPrograms(): void
    {
        try {
            $this->command->info('Seeding training programs...');

            $staffProfiles = DB::table('staff_profiles')
                ->where('tenant_id', $this->tenantId)
                ->get(['id']);

            $programs = [
                [
                    'title'          => 'Anti-Money Laundering & CFT Refresher 2026',
                    'category'       => 'compliance',
                    'provider'       => 'CAMS Nigeria',
                    'duration_hours' => 8.0,
                    'is_mandatory'   => true,
                    'description'    => 'Annual mandatory AML/CFT training covering typologies, red flags, and reporting obligations under NFIU guidelines.',
                    'status'         => 'active',
                ],
                [
                    'title'          => 'Credit Risk Analysis & Loan Appraisal',
                    'category'       => 'technical',
                    'provider'       => 'CIBN Training',
                    'duration_hours' => 16.0,
                    'is_mandatory'   => false,
                    'description'    => 'Deep-dive into credit risk frameworks, financial statement analysis, and CBN prudential guidelines for MFBs.',
                    'status'         => 'active',
                ],
                [
                    'title'          => 'Customer Experience & Service Excellence',
                    'category'       => 'soft_skills',
                    'provider'       => 'Internal HR',
                    'duration_hours' => 4.0,
                    'is_mandatory'   => false,
                    'description'    => 'Enhancing frontline staff ability to deliver outstanding customer service and handle complaints effectively.',
                    'status'         => 'active',
                ],
                [
                    'title'          => 'CBN Regulatory Updates 2025/2026',
                    'category'       => 'regulatory',
                    'provider'       => 'CBN Learning Hub',
                    'duration_hours' => 6.0,
                    'is_mandatory'   => true,
                    'description'    => 'Overview of key CBN circulars and policy updates affecting microfinance banking operations.',
                    'status'         => 'active',
                ],
                [
                    'title'          => 'Leadership & People Management',
                    'category'       => 'leadership',
                    'provider'       => 'MCB Business School',
                    'duration_hours' => 24.0,
                    'is_mandatory'   => false,
                    'description'    => 'Management training for team leads and branch managers on coaching, performance management, and delegation.',
                    'status'         => 'active',
                ],
            ];

            $programIds = [];

            foreach ($programs as $program) {
                $exists = DB::table('training_programs')
                    ->where('tenant_id', $this->tenantId)
                    ->where('title', $program['title'])
                    ->first(['id']);

                if ($exists) {
                    $programIds[] = $exists->id;
                    continue;
                }

                $id = (string) Str::uuid();
                DB::table('training_programs')->insert([
                    'id'             => $id,
                    'tenant_id'      => $this->tenantId,
                    'title'          => $program['title'],
                    'category'       => $program['category'],
                    'provider'       => $program['provider'],
                    'duration_hours' => $program['duration_hours'],
                    'is_mandatory'   => $program['is_mandatory'],
                    'description'    => $program['description'],
                    'status'         => $program['status'],
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $programIds[] = $id;
                $this->command->info("  Created training program: {$program['title']}");
            }

            // Seed attendances
            $staffList   = $staffProfiles->values()->all();
            $staffCount  = count($staffList);
            $statusPool  = ['enrolled', 'attended', 'completed', 'completed', 'completed'];
            $inserted    = 0;

            foreach ($programIds as $idx => $programId) {
                // Enroll roughly 60-100% of staff
                $enrollCount = rand((int) ceil($staffCount * 0.6), $staffCount);
                $shuffled    = $staffList;
                shuffle($shuffled);
                $toEnroll    = array_slice($shuffled, 0, $enrollCount);

                foreach ($toEnroll as $staff) {
                    $exists = DB::table('training_attendances')
                        ->where('program_id', $programId)
                        ->where('staff_profile_id', $staff->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $status      = $statusPool[array_rand($statusPool)];
                    $score       = ($status === 'completed') ? rand(60, 100) + (rand(0, 9) / 10) : null;
                    $completedAt = ($status === 'completed')
                        ? now()->subDays(rand(5, 60))->format('Y-m-d H:i:s')
                        : null;

                    DB::table('training_attendances')->insert([
                        'id'               => (string) Str::uuid(),
                        'tenant_id'        => $this->tenantId,
                        'program_id'       => $programId,
                        'staff_profile_id' => $staff->id,
                        'enrolled_at'      => now()->subDays(rand(30, 90))->format('Y-m-d H:i:s'),
                        'status'           => $status,
                        'score'            => $score,
                        'certificate_issued' => ($status === 'completed' && $score >= 70),
                        'completed_at'     => $completedAt,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $inserted++;
                }
            }

            $this->command->info("  Training attendances inserted: {$inserted}");

        } catch (\Throwable $e) {
            $this->command->error('Training programs failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // 5. REVIEW CYCLES + PERFORMANCE REVIEWS
    // -------------------------------------------------------------------------
    private function seedReviewCycles(): void
    {
        try {
            $this->command->info('Seeding review cycles...');

            $cycles = [
                [
                    'name'        => '2025 Annual Review',
                    'period_type' => 'annual',
                    'start_date'  => '2025-01-01',
                    'end_date'    => '2025-12-31',
                    'status'      => 'closed',
                ],
                [
                    'name'        => '2026 H1 Semi-Annual Review',
                    'period_type' => 'semi_annual',
                    'start_date'  => '2026-01-01',
                    'end_date'    => '2026-06-30',
                    'status'      => 'active',
                ],
            ];

            foreach ($cycles as $cycle) {
                $exists = DB::table('review_cycles')
                    ->where('tenant_id', $this->tenantId)
                    ->where('name', $cycle['name'])
                    ->exists();

                if (!$exists) {
                    DB::table('review_cycles')->insert([
                        'id'          => (string) Str::uuid(),
                        'tenant_id'   => $this->tenantId,
                        'name'        => $cycle['name'],
                        'period_type' => $cycle['period_type'],
                        'start_date'  => $cycle['start_date'],
                        'end_date'    => $cycle['end_date'],
                        'status'      => $cycle['status'],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $this->command->info("  Created review cycle: {$cycle['name']}");
                }
            }

        } catch (\Throwable $e) {
            $this->command->error('Review cycles failed: ' . $e->getMessage());
        }
    }

    private function seedPerformanceReviews(): void
    {
        try {
            $this->command->info('Seeding performance reviews...');

            $staffProfiles = DB::table('staff_profiles')
                ->where('tenant_id', $this->tenantId)
                ->get(['id', 'user_id', 'manager_id']);

            $cycles = DB::table('review_cycles')
                ->where('tenant_id', $this->tenantId)
                ->get(['id', 'status']);

            $ratings = ['exceptional', 'exceeds_expectations', 'meets_expectations', 'meets_expectations', 'below_expectations'];
            $reviewStatuses = ['pending', 'self_assessed', 'manager_reviewed', 'hr_approved'];
            $inserted = 0;

            foreach ($cycles as $cycle) {
                foreach ($staffProfiles as $staff) {
                    $exists = DB::table('performance_reviews')
                        ->where('review_cycle_id', $cycle->id)
                        ->where('staff_profile_id', $staff->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    // Closed cycle = mostly completed; active = mixed
                    if ($cycle->status === 'closed') {
                        $status      = $reviewStatuses[array_rand(['hr_approved', 'manager_reviewed', 'hr_approved'])];
                        $score       = round(rand(55, 98) + (rand(0, 9) / 10), 2);
                        $rating      = $ratings[array_rand($ratings)];
                        $submittedAt = now()->subMonths(3)->format('Y-m-d H:i:s');
                        $reviewedAt  = now()->subMonths(2)->format('Y-m-d H:i:s');
                    } else {
                        $statusIdx   = rand(0, count($reviewStatuses) - 1);
                        $status      = $reviewStatuses[$statusIdx];
                        $score       = ($statusIdx >= 2) ? round(rand(55, 98) + (rand(0, 9) / 10), 2) : null;
                        $rating      = ($statusIdx >= 2) ? $ratings[array_rand($ratings)] : null;
                        $submittedAt = ($statusIdx >= 1) ? now()->subDays(rand(10, 40))->format('Y-m-d H:i:s') : null;
                        $reviewedAt  = ($statusIdx >= 2) ? now()->subDays(rand(5, 20))->format('Y-m-d H:i:s') : null;
                    }

                    $reviewerId = $staff->manager_id ?? $this->managerUserId;

                    DB::table('performance_reviews')->insert([
                        'id'               => (string) Str::uuid(),
                        'tenant_id'        => $this->tenantId,
                        'review_cycle_id'  => $cycle->id,
                        'staff_profile_id' => $staff->id,
                        'reviewer_id'      => $reviewerId,
                        'status'           => $status,
                        'overall_score'    => $score,
                        'rating'           => $rating,
                        'staff_comments'   => $submittedAt ? 'I met all my set targets this period and contributed positively to the team.' : null,
                        'manager_comments' => $reviewedAt ? 'Staff shows good initiative and consistently delivers on KPIs. Recommend continued development.' : null,
                        'submitted_at'     => $submittedAt,
                        'reviewed_at'      => $reviewedAt,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $inserted++;
                }
            }

            $this->command->info("  Performance reviews inserted: {$inserted}");

        } catch (\Throwable $e) {
            $this->command->error('Performance reviews failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // 6. LOAN APPLICATIONS
    // -------------------------------------------------------------------------
    private function seedLoanApplications(): void
    {
        try {
            $this->command->info('Seeding loan applications...');

            $customers = DB::table('customers')
                ->where('tenant_id', $this->tenantId)
                ->inRandomOrder()
                ->limit(20)
                ->get(['id', 'first_name', 'last_name']);

            $loanProducts = DB::table('loan_products')
                ->where('tenant_id', $this->tenantId)
                ->get(['id', 'name']);

            if ($customers->isEmpty() || $loanProducts->isEmpty()) {
                $this->command->warn('  No customers or loan products found — skipping loan applications.');
                return;
            }

            $purposes = [
                'Business expansion and working capital',
                'Purchase of commercial equipment',
                'Agricultural inputs and farm development',
                'School fees payment for children',
                'Medical expenses and health emergency',
                'Home improvement and renovation',
                'Purchase of motorcycle for transport business',
                'Stock/inventory purchase for retail store',
                'Payment of rent arrears',
                'Small business startup capital',
            ];

            $statuses        = ['submitted', 'submitted', 'under_review', 'under_review', 'approved'];
            $employmentTypes = ['employed', 'self_employed', 'business_owner', 'farmer'];
            $inserted        = 0;

            foreach ($customers->take(15) as $i => $customer) {
                $product   = $loanProducts->random();
                $amount    = rand(50, 500) * 1000;
                $tenor     = [3, 6, 9, 12, 18, 24][array_rand([3, 6, 9, 12, 18, 24])];
                $status    = $statuses[array_rand($statuses)];
                $ref       = 'LA' . strtoupper(substr($this->tenantId, 0, 4)) . date('Y') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

                $refExists = DB::table('loan_applications')
                    ->where('reference', $ref)
                    ->exists();

                if ($refExists) {
                    continue;
                }

                DB::table('loan_applications')->insert([
                    'id'                      => (string) Str::uuid(),
                    'customer_id'             => $customer->id,
                    'tenant_id'               => $this->tenantId,
                    'account_id'              => null,
                    'reference'               => $ref,
                    'loan_type'               => 'personal',
                    'requested_amount'        => $amount,
                    'requested_tenor_months'  => $tenor,
                    'monthly_income'          => rand(80, 500) * 1000,
                    'employment_status'       => $employmentTypes[array_rand($employmentTypes)],
                    'employer_name'           => null,
                    'purpose'                 => $purposes[array_rand($purposes)],
                    'collateral_description'  => null,
                    'collateral_value'        => null,
                    'status'                  => $status,
                    'officer_notes'           => null,
                    'reviewed_by'             => null,
                    'reviewed_at'             => null,
                    'resulting_loan_id'       => null,
                    'created_at'              => now()->subDays(rand(1, 45)),
                    'updated_at'              => now(),
                ]);
                $inserted++;
            }

            $this->command->info("  Loan applications inserted: {$inserted}");

        } catch (\Throwable $e) {
            $this->command->error('Loan applications failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // 7. KYC UPGRADE REQUESTS
    // -------------------------------------------------------------------------
    private function seedKycUpgradeRequests(): void
    {
        try {
            $this->command->info('Seeding KYC upgrade requests...');

            $customers = DB::table('customers')
                ->where('tenant_id', $this->tenantId)
                ->inRandomOrder()
                ->limit(12)
                ->get(['id', 'kyc_tier']);

            if ($customers->isEmpty()) {
                $this->command->warn('  No customers found — skipping KYC upgrade requests.');
                return;
            }

            $idTypes  = ['national_id', 'drivers_license', 'voters_card', 'international_passport', 'nin_slip'];
            $statuses = ['submitted', 'submitted', 'under_review', 'under_review', 'approved', 'rejected'];
            $inserted = 0;

            foreach ($customers->take(10) as $customer) {
                // Avoid duplicate pending requests for same customer
                $exists = DB::table('kyc_upgrade_requests')
                    ->where('customer_id', $customer->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $currentTier = $customer->kyc_tier ?? 'level_1';
                $targetTier  = ($currentTier === 'level_1') ? 'level_2' : 'level_3';
                $status      = $statuses[array_rand($statuses)];

                DB::table('kyc_upgrade_requests')->insert([
                    'id'                  => (string) Str::uuid(),
                    'customer_id'         => $customer->id,
                    'tenant_id'           => $this->tenantId,
                    'current_tier'        => $currentTier,
                    'target_tier'         => $targetTier,
                    'bvn'                 => (string) rand(10000000000, 99999999999),
                    'nin'                 => (string) rand(10000000000, 99999999999),
                    'id_type'             => $idTypes[array_rand($idTypes)],
                    'id_number'           => strtoupper(Str::random(2)) . rand(100000000, 999999999),
                    'id_document_path'    => 'kyc/documents/' . Str::uuid() . '.jpg',
                    'selfie_path'         => 'kyc/selfies/' . Str::uuid() . '.jpg',
                    'address_proof_path'  => 'kyc/address/' . Str::uuid() . '.pdf',
                    'status'              => $status,
                    'reviewer_notes'      => ($status === 'rejected') ? 'Document quality insufficient. Please resubmit clearer copies.' : null,
                    'reviewed_by'         => in_array($status, ['approved', 'rejected']) ? (string) $this->adminUserId : null,
                    'reviewed_at'         => in_array($status, ['approved', 'rejected']) ? now()->subDays(rand(1, 10))->format('Y-m-d H:i:s') : null,
                    'created_at'          => now()->subDays(rand(3, 30)),
                    'updated_at'          => now(),
                ]);
                $inserted++;
            }

            $this->command->info("  KYC upgrade requests inserted: {$inserted}");

        } catch (\Throwable $e) {
            $this->command->error('KYC upgrade requests failed: ' . $e->getMessage());
        }
    }
}
