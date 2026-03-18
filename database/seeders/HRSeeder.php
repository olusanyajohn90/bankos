<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds all HR & Payroll tables:
 * regions, divisions, departments, pay_grades, staff_profiles,
 * staff_pay_configs, staff_bank_details, leave_types, leave_balances,
 * leave_requests, public_holidays, asset_categories, assets,
 * performance_reviews, salary_advances, staff_id_cards
 */
class HRSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    // All tenant users: id => [name, email, branch_key]
    private array $users = [
        2  => ['name' => 'Compliance Officer',    'email' => 'compliance@demomfb.com',    'branch' => 'head_office'],
        3  => ['name' => 'Bank Admin',             'email' => 'admin@demomfb.com',         'branch' => 'head_office'],
        4  => ['name' => 'Loan Officer',           'email' => 'loans@demomfb.com',         'branch' => 'head_office'],
        5  => ['name' => 'Adewale Balogun',        'email' => 'manager.ho@demomfb.com',    'branch' => 'head_office'],
        6  => ['name' => 'Chidinma Okonkwo',       'email' => 'manager.ikeja@demomfb.com', 'branch' => 'ikeja'],
        7  => ['name' => 'Musa Aliyu Ibrahim',     'email' => 'manager.abuja@demomfb.com', 'branch' => 'abuja'],
        8  => ['name' => 'Hauwa Suleiman Musa',    'email' => 'manager.kano@demomfb.com',  'branch' => 'kano'],
        9  => ['name' => 'Tonye Briggs-Williams',  'email' => 'manager.phc@demomfb.com',   'branch' => 'phc'],
        10 => ['name' => 'Obinna Ezeh',            'email' => 'manager.enugu@demomfb.com', 'branch' => 'enugu'],
        11 => ['name' => 'Olusegun Adeyemi',       'email' => 'manager.ibadan@demomfb.com','branch' => 'ibadan'],
        12 => ['name' => 'Blessing Eze',           'email' => 'lo.ikeja@demomfb.com',      'branch' => 'ikeja'],
        13 => ['name' => 'Segun Adeoye',           'email' => 'teller.ikeja@demomfb.com',  'branch' => 'ikeja'],
        14 => ['name' => 'Amina Garba',            'email' => 'lo.abuja@demomfb.com',      'branch' => 'abuja'],
        15 => ['name' => 'Emeka Nwachukwu',        'email' => 'teller.abuja@demomfb.com',  'branch' => 'abuja'],
        16 => ['name' => 'Bashir Usman',           'email' => 'lo.kano@demomfb.com',       'branch' => 'kano'],
        17 => ['name' => 'Aisha Mohammed',         'email' => 'teller.kano@demomfb.com',   'branch' => 'kano'],
        18 => ['name' => 'Chukwuemeka Obi',        'email' => 'lo.phc@demomfb.com',        'branch' => 'phc'],
        19 => ['name' => 'Ifunanya Amadi',         'email' => 'teller.phc@demomfb.com',    'branch' => 'phc'],
        20 => ['name' => 'Ikenna Okafor',          'email' => 'lo.enugu@demomfb.com',      'branch' => 'enugu'],
        21 => ['name' => 'Ngozi Ugwu',             'email' => 'teller.enugu@demomfb.com',  'branch' => 'enugu'],
        22 => ['name' => 'Adebimpe Ogunleye',      'email' => 'lo.ibadan@demomfb.com',     'branch' => 'ibadan'],
        23 => ['name' => 'Taiwo Salami',           'email' => 'teller.ibadan@demomfb.com', 'branch' => 'ibadan'],
    ];

    public function run(): void
    {
        $this->cleanup();

        $branches  = $this->branchMap();
        $regionIds = $this->seedRegions();
        $divIds    = $this->seedDivisions();
        $deptIds   = $this->seedDepartments($divIds);
        $gradeIds  = $this->seedPayGrades();
        $profileIds= $this->seedStaffProfiles($branches, $deptIds, $gradeIds);
        $this->seedDepartmentHeads($deptIds, $profileIds);
        $this->seedPayConfigs($profileIds, $gradeIds);
        $this->seedBankDetails($profileIds);
        $leaveTypeIds = $this->seedLeaveTypes();
        $this->seedLeaveBalances($profileIds, $leaveTypeIds);
        $this->seedLeaveRequests($profileIds, $leaveTypeIds);
        $this->seedPublicHolidays();
        $catIds = $this->seedAssetCategories();
        $this->seedAssets($catIds, $branches, $profileIds);
        $this->seedPerformanceReviews($profileIds);
        $this->seedSalaryAdvances($profileIds);

        $this->command->info('HR data seeded successfully.');
    }

    // ─── Cleanup ──────────────────────────────────────────────────────────────
    private function cleanup(): void
    {
        DB::table('staff_id_cards')->where('tenant_id', $this->tenantId)->delete();
        DB::table('salary_advances')->where('tenant_id', $this->tenantId)->delete();
        DB::table('performance_review_items')->whereIn('review_id', function($q) {
            $q->select('id')->from('performance_reviews')->where('tenant_id', $this->tenantId);
        })->delete();
        DB::table('performance_reviews')->where('tenant_id', $this->tenantId)->delete();
        DB::table('attendance_records')->where('tenant_id', $this->tenantId)->delete();
        DB::table('leave_requests')->where('tenant_id', $this->tenantId)->delete();
        DB::table('leave_balances')->where('tenant_id', $this->tenantId)->delete();
        DB::table('leave_types')->where('tenant_id', $this->tenantId)->delete();
        DB::table('assets')->where('tenant_id', $this->tenantId)->delete();
        DB::table('asset_categories')->where('tenant_id', $this->tenantId)->delete();
        DB::table('public_holidays')->where('tenant_id', $this->tenantId)->delete();
        DB::table('staff_bank_details')->where('tenant_id', $this->tenantId)->delete();
        DB::table('staff_pay_configs')->where('tenant_id', $this->tenantId)->delete();
        DB::table('staff_profiles')->where('tenant_id', $this->tenantId)->delete();
        DB::table('pay_grades')->where('tenant_id', $this->tenantId)->delete();
        DB::table('departments')->where('tenant_id', $this->tenantId)->delete();
        DB::table('divisions')->where('tenant_id', $this->tenantId)->delete();
        DB::table('regions')->where('tenant_id', $this->tenantId)->delete();
    }

    private function branchMap(): array
    {
        $all = DB::table('branches')->where('tenant_id', $this->tenantId)->get(['id', 'name']);
        $map = [];
        foreach ($all as $b) {
            $n = strtolower($b->name);
            if (str_contains($n, 'head office') && str_contains($n, 'lagos')) $map['head_office'] = $b->id;
            elseif (str_contains($n, 'head office') && !isset($map['head_office'])) $map['head_office'] = $b->id;
            elseif (str_contains($n, 'ikeja') && !isset($map['ikeja'])) $map['ikeja'] = $b->id;
            elseif (str_contains($n, 'abuja') && !str_contains($n, 'wuse') && !isset($map['abuja'])) $map['abuja'] = $b->id;
            elseif (str_contains($n, 'kano') && !isset($map['kano'])) $map['kano'] = $b->id;
            elseif (str_contains($n, 'port harcourt') && !isset($map['phc'])) $map['phc'] = $b->id;
            elseif (str_contains($n, 'enugu') && !isset($map['enugu'])) $map['enugu'] = $b->id;
            elseif (str_contains($n, 'ibadan') && !isset($map['ibadan'])) $map['ibadan'] = $b->id;
        }
        // Fallback to first branch for any unmapped
        $first = $all->first()->id ?? null;
        foreach (['head_office','ikeja','abuja','kano','phc','enugu','ibadan'] as $k) {
            if (!isset($map[$k])) $map[$k] = $first;
        }
        return $map;
    }

    // ─── Regions ──────────────────────────────────────────────────────────────
    private function seedRegions(): array
    {
        $rows = [
            ['name' => 'South West',  'code' => 'SW',  'status' => 'active'],
            ['name' => 'North West',  'code' => 'NW',  'status' => 'active'],
            ['name' => 'South South', 'code' => 'SS',  'status' => 'active'],
            ['name' => 'North Central','code'=> 'NC',  'status' => 'active'],
            ['name' => 'South East',  'code' => 'SE',  'status' => 'active'],
        ];
        $ids = [];
        foreach ($rows as $r) {
            $id = (string) Str::uuid();
            DB::table('regions')->insert([
                'id' => $id, 'tenant_id' => $this->tenantId,
                'name' => $r['name'], 'code' => $r['code'],
                'manager_id' => null, 'status' => $r['status'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[$r['code']] = $id;
        }
        $this->command->line('  Regions: ' . \count($rows));
        return $ids;
    }

    // ─── Divisions ────────────────────────────────────────────────────────────
    private function seedDivisions(): array
    {
        $rows = [
            ['name' => 'Business & Operations',    'code' => 'BOP', 'description' => 'Core banking operations and branch network'],
            ['name' => 'Credit & Risk',             'code' => 'CRK', 'description' => 'Loan origination, credit analysis, and risk management'],
            ['name' => 'Finance & Treasury',        'code' => 'FIN', 'description' => 'Financial reporting, treasury, and accounts'],
            ['name' => 'Technology & Innovation',   'code' => 'TEC', 'description' => 'IT infrastructure, digital products, and innovation'],
            ['name' => 'Compliance & Legal',        'code' => 'CMP', 'description' => 'Regulatory compliance, AML/CFT, and legal'],
            ['name' => 'Human Resources',           'code' => 'HRM', 'description' => 'Talent management, payroll, and employee services'],
        ];
        $ids = [];
        foreach ($rows as $r) {
            $id = (string) Str::uuid();
            DB::table('divisions')->insert([
                'id' => $id, 'tenant_id' => $this->tenantId,
                'name' => $r['name'], 'code' => $r['code'],
                'description' => $r['description'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[$r['code']] = $id;
        }
        $this->command->line('  Divisions: ' . \count($rows));
        return $ids;
    }

    // ─── Departments ──────────────────────────────────────────────────────────
    private function seedDepartments(array $divIds): array
    {
        $rows = [
            ['name' => 'Retail Banking',      'code' => 'RBK', 'div' => 'BOP', 'cost_centre' => 'CC001'],
            ['name' => 'Branch Operations',   'code' => 'BRC', 'div' => 'BOP', 'cost_centre' => 'CC002'],
            ['name' => 'Customer Service',    'code' => 'CUS', 'div' => 'BOP', 'cost_centre' => 'CC003'],
            ['name' => 'Loans & Credit',      'code' => 'LNS', 'div' => 'CRK', 'cost_centre' => 'CC004'],
            ['name' => 'Risk Management',     'code' => 'RSK', 'div' => 'CRK', 'cost_centre' => 'CC005'],
            ['name' => 'Finance & Accounts',  'code' => 'FIN', 'div' => 'FIN', 'cost_centre' => 'CC006'],
            ['name' => 'Treasury',            'code' => 'TRY', 'div' => 'FIN', 'cost_centre' => 'CC007'],
            ['name' => 'Information Technology','code'=>'ITD', 'div' => 'TEC', 'cost_centre' => 'CC008'],
            ['name' => 'Digital Products',    'code' => 'DPD', 'div' => 'TEC', 'cost_centre' => 'CC009'],
            ['name' => 'Compliance & AML',    'code' => 'CMP', 'div' => 'CMP', 'cost_centre' => 'CC010'],
            ['name' => 'Legal & Secretariat', 'code' => 'LGL', 'div' => 'CMP', 'cost_centre' => 'CC011'],
            ['name' => 'Human Resources',     'code' => 'HRM', 'div' => 'HRM', 'cost_centre' => 'CC012'],
            ['name' => 'Administration',      'code' => 'ADM', 'div' => 'HRM', 'cost_centre' => 'CC013'],
        ];
        $ids = [];
        foreach ($rows as $r) {
            $id = (string) Str::uuid();
            DB::table('departments')->insert([
                'id' => $id, 'tenant_id' => $this->tenantId,
                'division_id' => $divIds[$r['div']] ?? null,
                'name' => $r['name'], 'code' => $r['code'],
                'head_id' => null, // set after staff profiles
                'cost_centre_code' => $r['cost_centre'],
                'status' => 'active',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[$r['code']] = $id;
        }
        $this->command->line('  Departments: ' . \count($rows));
        return $ids;
    }

    // ─── Pay Grades ───────────────────────────────────────────────────────────
    private function seedPayGrades(): array
    {
        $grades = [
            ['code' => 'GL01', 'name' => 'Grade Level 1 — Intern',           'level' => 1,  'grade' => 1,  'basic_min' => 100_000, 'basic_max' => 150_000,  'title' => 'Intern / Trainee'],
            ['code' => 'GL02', 'name' => 'Grade Level 2 — Junior Officer',   'level' => 2,  'grade' => 2,  'basic_min' => 180_000, 'basic_max' => 250_000,  'title' => 'Junior Banking Officer'],
            ['code' => 'GL03', 'name' => 'Grade Level 3 — Officer I',        'level' => 3,  'grade' => 3,  'basic_min' => 280_000, 'basic_max' => 380_000,  'title' => 'Banking Officer I'],
            ['code' => 'GL04', 'name' => 'Grade Level 4 — Officer II',       'level' => 4,  'grade' => 4,  'basic_min' => 400_000, 'basic_max' => 550_000,  'title' => 'Banking Officer II'],
            ['code' => 'GL05', 'name' => 'Grade Level 5 — Senior Officer',   'level' => 5,  'grade' => 5,  'basic_min' => 580_000, 'basic_max' => 750_000,  'title' => 'Senior Banking Officer'],
            ['code' => 'GL06', 'name' => 'Grade Level 6 — Assistant Manager','level' => 6,  'grade' => 6,  'basic_min' => 800_000, 'basic_max' => 1_050_000,'title' => 'Assistant Branch Manager'],
            ['code' => 'GL07', 'name' => 'Grade Level 7 — Manager',          'level' => 7,  'grade' => 7,  'basic_min' => 1_100_000,'basic_max' => 1_500_000,'title' => 'Branch Manager / Dept Head'],
            ['code' => 'GL08', 'name' => 'Grade Level 8 — Senior Manager',   'level' => 8,  'grade' => 8,  'basic_min' => 1_600_000,'basic_max' => 2_200_000,'title' => 'Senior Manager'],
            ['code' => 'GL09', 'name' => 'Grade Level 9 — Deputy Director',  'level' => 9,  'grade' => 9,  'basic_min' => 2_400_000,'basic_max' => 3_500_000,'title' => 'Deputy Director / AGM'],
            ['code' => 'GL10', 'name' => 'Grade Level 10 — Director / C-Suite','level' => 10,'grade'=> 10, 'basic_min' => 3_600_000,'basic_max' => 6_000_000,'title' => 'Director / MD / CEO'],
        ];
        $ids = [];
        foreach ($grades as $g) {
            $id = (string) Str::uuid();
            DB::table('pay_grades')->insert([
                'id' => $id, 'tenant_id' => $this->tenantId,
                'code' => $g['code'], 'name' => $g['name'],
                'level' => $g['level'], 'grade' => $g['grade'],
                'basic_min' => $g['basic_min'], 'basic_max' => $g['basic_max'],
                'typical_title' => $g['title'],
                'annual_increment_pct' => 5.0,
                'leave_allowance_pct' => 10.0,
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[$g['code']] = $id;
        }
        $this->command->line('  Pay grades: ' . \count($grades));
        return $ids;
    }

    // ─── Staff Profiles ───────────────────────────────────────────────────────
    private function seedStaffProfiles(array $branches, array $deptIds, array $gradeIds): array
    {
        // user_id => [dept_code, grade_code, title, emp_type, joined_months_ago]
        $config = [
            2  => ['dept'=>'CMP', 'grade'=>'GL06', 'title'=>'Head of Compliance',    'type'=>'full_time', 'joined'=>36],
            3  => ['dept'=>'ADM', 'grade'=>'GL07', 'title'=>'Head of Administration', 'type'=>'full_time', 'joined'=>48],
            4  => ['dept'=>'LNS', 'grade'=>'GL05', 'title'=>'Senior Loan Officer',    'type'=>'full_time', 'joined'=>24],
            5  => ['dept'=>'BRC', 'grade'=>'GL08', 'title'=>'General Manager',        'type'=>'full_time', 'joined'=>60],
            6  => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>30],
            7  => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>28],
            8  => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>22],
            9  => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>18],
            10 => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>20],
            11 => ['dept'=>'RBK', 'grade'=>'GL07', 'title'=>'Branch Manager',         'type'=>'full_time', 'joined'=>16],
            12 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>15],
            13 => ['dept'=>'BRC', 'grade'=>'GL03', 'title'=>'Teller',                 'type'=>'full_time', 'joined'=>12],
            14 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>14],
            15 => ['dept'=>'BRC', 'grade'=>'GL03', 'title'=>'Teller',                 'type'=>'full_time', 'joined'=>10],
            16 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>9],
            17 => ['dept'=>'BRC', 'grade'=>'GL03', 'title'=>'Teller',                 'type'=>'full_time', 'joined'=>8],
            18 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>7],
            19 => ['dept'=>'BRC', 'grade'=>'GL03', 'title'=>'Teller',                 'type'=>'full_time', 'joined'=>6],
            20 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>6],
            21 => ['dept'=>'BRC', 'grade'=>'GL02', 'title'=>'Teller',                 'type'=>'full_time', 'joined'=>4],
            22 => ['dept'=>'LNS', 'grade'=>'GL04', 'title'=>'Loan Officer',           'type'=>'full_time', 'joined'=>5],
            23 => ['dept'=>'BRC', 'grade'=>'GL02', 'title'=>'Teller',                 'type'=>'contract',  'joined'=>3],
        ];

        $ids = [];
        $n   = 1;

        foreach ($config as $userId => $c) {
            $user        = $this->users[$userId];
            $branchKey   = $user['branch'];
            $branchId    = $branches[$branchKey] ?? null;
            $deptId      = $deptIds[$c['dept']] ?? null;
            $joined      = now()->subMonths($c['joined'])->startOfMonth();
            $confirmed   = $c['joined'] >= 6 ? $joined->copy()->addMonths(6)->toDateString() : null;
            $managerId   = $userId <= 5 ? null : 5; // managers report to GM (user 5)
            $profileId   = (string) Str::uuid();

            DB::table('staff_profiles')->insert([
                'id'                => $profileId,
                'tenant_id'         => $this->tenantId,
                'user_id'           => $userId,
                'branch_id'         => $branchId,
                'manager_id'        => $managerId,
                'team_id'           => null,
                'department'        => $c['dept'],
                'department_id'     => $deptId,
                'job_title'         => $c['title'],
                'grade_level'       => $c['grade'],
                'staff_code'        => 'EMP-' . str_pad($n, 4, '0', STR_PAD_LEFT),
                'employee_number'   => 'DMB' . str_pad($n, 5, '0', STR_PAD_LEFT),
                'referral_code'     => strtoupper(Str::random(8)),
                'joined_date'       => $joined->toDateString(),
                'confirmation_date' => $confirmed,
                'employment_type'   => $c['type'],
                'cost_centre_code'  => null,
                'region_id'         => null,
                'exit_date'         => null,
                'exit_reason'       => null,
                'status'            => 'active',
                'created_at'        => $joined,
                'updated_at'        => now(),
            ]);

            $ids[$userId] = $profileId;
            $n++;
        }

        $this->command->line('  Staff profiles: ' . \count($ids));
        return $ids;
    }

    // ─── Department Heads ─────────────────────────────────────────────────────
    private function seedDepartmentHeads(array $deptIds, array $profileIds): void
    {
        // user_id (int) => dept_code they head — head_id references users.id (bigint)
        $heads = [2 => 'CMP', 5 => 'BRC', 4 => 'LNS', 3 => 'ADM'];
        foreach ($heads as $userId => $deptCode) {
            if (isset($deptIds[$deptCode])) {
                DB::table('departments')
                    ->where('id', $deptIds[$deptCode])
                    ->update(['head_id' => $userId]);
            }
        }
    }

    // ─── Pay Configs ──────────────────────────────────────────────────────────
    private function seedPayConfigs(array $profileIds, array $gradeIds): void
    {
        // grade_code => [basic, housing, transport, meal]
        $salaries = [
            'GL02' => [200_000, 60_000,  40_000, 20_000],
            'GL03' => [320_000, 90_000,  60_000, 30_000],
            'GL04' => [480_000, 140_000, 80_000, 40_000],
            'GL05' => [650_000, 190_000, 100_000, 50_000],
            'GL06' => [900_000, 260_000, 130_000, 60_000],
            'GL07' => [1_200_000, 350_000, 160_000, 75_000],
            'GL08' => [1_800_000, 520_000, 200_000, 100_000],
            'GL09' => [2_800_000, 800_000, 300_000, 150_000],
            'GL10' => [4_500_000, 1_200_000, 400_000, 200_000],
        ];

        $gradeByUser = [
            2=>'GL06',3=>'GL07',4=>'GL05',5=>'GL08',6=>'GL07',7=>'GL07',8=>'GL07',9=>'GL07',10=>'GL07',11=>'GL07',
            12=>'GL04',13=>'GL03',14=>'GL04',15=>'GL03',16=>'GL04',17=>'GL03',18=>'GL04',19=>'GL03',20=>'GL04',21=>'GL02',22=>'GL04',23=>'GL02',
        ];

        $count = 0;
        foreach ($profileIds as $userId => $profileId) {
            $g = $gradeByUser[$userId] ?? 'GL03';
            [$basic, $housing, $transport, $meal] = $salaries[$g] ?? [200_000, 60_000, 40_000, 20_000];
            $gradeId = $gradeIds[$g] ?? null;

            DB::table('staff_pay_configs')->insert([
                'id'                       => (string) Str::uuid(),
                'tenant_id'                => $this->tenantId,
                'staff_profile_id'         => $profileId,
                'pay_grade_id'             => $gradeId,
                'basic_salary'             => $basic,
                'housing_allowance'        => $housing,
                'transport_allowance'      => $transport,
                'meal_allowance'           => $meal,
                'other_allowances'         => null,
                'pension_fund_administrator'=> 'ARM Pension Managers',
                'pension_account_number'   => 'PEN' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'tax_id'                   => 'TIN' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'nhf_number'               => 'NHF' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'effective_date'           => now()->startOfYear()->toDateString(),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
            $count++;
        }
        $this->command->line("  Pay configs: {$count}");
    }

    // ─── Bank Details ─────────────────────────────────────────────────────────
    private function seedBankDetails(array $profileIds): void
    {
        $banks = [
            ['code' => '011', 'name' => 'First Bank'],
            ['code' => '044', 'name' => 'Access Bank'],
            ['code' => '058', 'name' => 'GTBank'],
            ['code' => '033', 'name' => 'United Bank for Africa'],
            ['code' => '057', 'name' => 'Zenith Bank'],
        ];

        $count = 0;
        foreach ($profileIds as $userId => $profileId) {
            $bank = $banks[$userId % \count($banks)];
            DB::table('staff_bank_details')->insert([
                'id'               => (string) Str::uuid(),
                'tenant_id'        => $this->tenantId,
                'staff_profile_id' => $profileId,
                'bank_name'        => $bank['name'],
                'bank_code'        => $bank['code'],
                'account_number'   => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                'account_name'     => $this->users[$userId]['name'],
                'is_primary'       => true,
                'is_verified'      => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $count++;
        }
        $this->command->line("  Bank details: {$count}");
    }

    // ─── Leave Types ──────────────────────────────────────────────────────────
    private function seedLeaveTypes(): array
    {
        $types = [
            ['name' => 'Annual Leave',         'code' => 'AL',  'days' => 21, 'carry' => 5,  'gender' => 'all',    'paid' => true,  'approval' => true],
            ['name' => 'Sick Leave',            'code' => 'SL',  'days' => 15, 'carry' => 0,  'gender' => 'all',    'paid' => true,  'approval' => false],
            ['name' => 'Maternity Leave',       'code' => 'MAT', 'days' => 90, 'carry' => 0,  'gender' => 'female', 'paid' => true,  'approval' => true],
            ['name' => 'Paternity Leave',       'code' => 'PAT', 'days' => 5,  'carry' => 0,  'gender' => 'male',   'paid' => true,  'approval' => true],
            ['name' => 'Study / Exam Leave',    'code' => 'STD', 'days' => 10, 'carry' => 0,  'gender' => 'all',    'paid' => false, 'approval' => true],
            ['name' => 'Compassionate Leave',   'code' => 'COM', 'days' => 5,  'carry' => 0,  'gender' => 'all',    'paid' => true,  'approval' => true],
            ['name' => 'Public Holiday',        'code' => 'PH',  'days' => 0,  'carry' => 0,  'gender' => 'all',    'paid' => true,  'approval' => false],
        ];
        $ids = [];
        foreach ($types as $t) {
            $id = (string) Str::uuid();
            DB::table('leave_types')->insert([
                'id'               => $id,
                'tenant_id'        => $this->tenantId,
                'name'             => $t['name'],
                'code'             => $t['code'],
                'days_entitled'    => $t['days'],
                'carry_over_days'  => $t['carry'],
                'gender_restriction'=> $t['gender'],
                'requires_approval'=> $t['approval'],
                'is_paid'          => $t['paid'],
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            $ids[$t['code']] = $id;
        }
        $this->command->line('  Leave types: ' . \count($types));
        return $ids;
    }

    // ─── Leave Balances ───────────────────────────────────────────────────────
    private function seedLeaveBalances(array $profileIds, array $leaveTypeIds): void
    {
        $year  = now()->year;
        $count = 0;
        $mainTypes = ['AL', 'SL', 'STD', 'COM'];

        foreach ($profileIds as $profileId) {
            foreach ($mainTypes as $code) {
                $typeId   = $leaveTypeIds[$code] ?? null;
                if (!$typeId) continue;
                $entitled = ['AL' => 21, 'SL' => 15, 'STD' => 10, 'COM' => 5][$code];
                $used     = ['AL' => rand(0, 10), 'SL' => rand(0, 5), 'STD' => 0, 'COM' => rand(0, 2)][$code];
                $pending  = $used > 0 ? rand(0, 2) : 0;

                DB::table('leave_balances')->insert([
                    'id'               => (string) Str::uuid(),
                    'tenant_id'        => $this->tenantId,
                    'staff_profile_id' => $profileId,
                    'leave_type_id'    => $typeId,
                    'year'             => $year,
                    'entitled_days'    => $entitled,
                    'used_days'        => $used,
                    'pending_days'     => $pending,
                    'created_at'       => now()->startOfYear(),
                    'updated_at'       => now(),
                ]);
                $count++;
            }
        }
        $this->command->line("  Leave balances: {$count}");
    }

    // ─── Leave Requests ───────────────────────────────────────────────────────
    private function seedLeaveRequests(array $profileIds, array $leaveTypeIds): void
    {
        $statuses  = ['approved', 'approved', 'approved', 'pending', 'rejected'];
        $profileList = array_values($profileIds);
        $count     = 0;

        $requests = [
            ['profile_idx' => 0, 'type' => 'AL', 'start' => '-60 days', 'days' => 7,  'reason' => 'Annual vacation with family'],
            ['profile_idx' => 1, 'type' => 'SL', 'start' => '-30 days', 'days' => 3,  'reason' => 'Doctor confirmed malaria treatment'],
            ['profile_idx' => 2, 'type' => 'AL', 'start' => '-14 days', 'days' => 5,  'reason' => 'Attending family event in Abuja'],
            ['profile_idx' => 3, 'type' => 'AL', 'start' => '+7 days',  'days' => 10, 'reason' => 'Planned annual leave'],
            ['profile_idx' => 4, 'type' => 'SL', 'start' => '-7 days',  'days' => 2,  'reason' => 'Medical appointment'],
            ['profile_idx' => 5, 'type' => 'COM','start' => '-20 days', 'days' => 3,  'reason' => 'Family bereavement'],
            ['profile_idx' => 6, 'type' => 'AL', 'start' => '+14 days', 'days' => 5,  'reason' => 'Leave request'],
            ['profile_idx' => 7, 'type' => 'STD','start' => '-45 days', 'days' => 5,  'reason' => 'ICAN professional exam'],
            ['profile_idx' => 8, 'type' => 'AL', 'start' => '-90 days', 'days' => 14, 'reason' => 'Annual leave — end of year'],
            ['profile_idx' => 9, 'type' => 'SL', 'start' => '-10 days', 'days' => 1,  'reason' => 'Feeling unwell'],
            ['profile_idx' => 10,'type' => 'AL', 'start' => '+20 days', 'days' => 7,  'reason' => 'Family vacation'],
            ['profile_idx' => 11,'type' => 'AL', 'start' => '-5 days',  'days' => 5,  'reason' => 'Annual leave'],
        ];

        $approver = 5; // user_id of GM (approver_id references users.id bigint)

        foreach ($requests as $i => $req) {
            $profileId = $profileList[$req['profile_idx']] ?? ($profileList[0] ?? null);
            $typeId    = $leaveTypeIds[$req['type']] ?? null;
            if (!$profileId || !$typeId) continue;

            $status    = $statuses[$i % \count($statuses)];
            $startDate = now()->modify($req['start'])->startOfDay();
            $endDate   = $startDate->copy()->addDays($req['days'] - 1);
            $approved  = $status === 'approved' ? now()->subDays(rand(1, 7)) : null;

            DB::table('leave_requests')->insert([
                'id'               => (string) Str::uuid(),
                'tenant_id'        => $this->tenantId,
                'staff_profile_id' => $profileId,
                'leave_type_id'    => $typeId,
                'start_date'       => $startDate->toDateString(),
                'end_date'         => $endDate->toDateString(),
                'days_requested'   => $req['days'],
                'reason'           => $req['reason'],
                'status'           => $status,
                'approver_id'      => $status !== 'pending' ? $approver : null,
                'approved_at'      => $approved,
                'rejection_reason' => $status === 'rejected' ? 'Insufficient leave balance for this period.' : null,
                'relief_officer_id'=> null,
                'created_at'       => $startDate->copy()->subDays(rand(3, 14)),
                'updated_at'       => $approved ?? now(),
            ]);
            $count++;
        }
        $this->command->line("  Leave requests: {$count}");
    }

    // ─── Public Holidays (Nigeria 2026) ───────────────────────────────────────
    private function seedPublicHolidays(): void
    {
        $holidays = [
            ['name' => "New Year's Day",          'date' => '2026-01-01', 'type' => 'national'],
            ['name' => 'Id el-Fitri (Day 1)',      'date' => '2026-03-20', 'type' => 'national'],
            ['name' => 'Id el-Fitri (Day 2)',      'date' => '2026-03-21', 'type' => 'national'],
            ['name' => 'Good Friday',              'date' => '2026-04-03', 'type' => 'national'],
            ['name' => 'Easter Monday',            'date' => '2026-04-06', 'type' => 'national'],
            ['name' => "Workers' Day",             'date' => '2026-05-01', 'type' => 'national'],
            ['name' => "Children's Day",           'date' => '2026-05-27', 'type' => 'national'],
            ['name' => "Democracy Day",            'date' => '2026-06-12', 'type' => 'national'],
            ['name' => 'Id el-Kabir (Day 1)',      'date' => '2026-06-27', 'type' => 'national'],
            ['name' => 'Id el-Kabir (Day 2)',      'date' => '2026-06-28', 'type' => 'national'],
            ['name' => 'National Day (Ind.)',      'date' => '2026-10-01', 'type' => 'national'],
            ['name' => 'Id el-Maulud',             'date' => '2026-09-15', 'type' => 'national'],
            ['name' => 'Christmas Day',            'date' => '2026-12-25', 'type' => 'national'],
            ['name' => 'Boxing Day',               'date' => '2026-12-26', 'type' => 'national'],
        ];

        foreach ($holidays as $h) {
            DB::table('public_holidays')->insert([
                'id'           => (string) Str::uuid(),
                'tenant_id'    => $this->tenantId,
                'name'         => $h['name'],
                'date'         => $h['date'],
                'type'         => $h['type'],
                'is_recurring' => true,
                'is_active'    => true,
                'notes'        => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
        $this->command->line('  Public holidays: ' . \count($holidays));
    }

    // ─── Asset Categories ─────────────────────────────────────────────────────
    private function seedAssetCategories(): array
    {
        $cats = [
            ['name' => 'IT Equipment',    'code' => 'IT',  'desc' => 'Computers, servers, peripherals',       'years' => 3],
            ['name' => 'Furniture',       'code' => 'FRN', 'desc' => 'Office furniture and fittings',          'years' => 10],
            ['name' => 'Vehicles',        'code' => 'VEH', 'desc' => 'Cars and motorcycles',                   'years' => 5],
            ['name' => 'Office Equipment','code' => 'OEQ', 'desc' => 'Photocopiers, printers, safes',          'years' => 5],
            ['name' => 'Generators',      'code' => 'GEN', 'desc' => 'Power generators and UPS systems',       'years' => 7],
            ['name' => 'CCTV & Security', 'code' => 'SEC', 'desc' => 'Cameras, biometrics, access control',    'years' => 5],
        ];
        $ids = [];
        foreach ($cats as $c) {
            $id = (string) Str::uuid();
            DB::table('asset_categories')->insert([
                'id' => $id, 'tenant_id' => $this->tenantId,
                'name' => $c['name'], 'code' => $c['code'],
                'description' => $c['desc'],
                'depreciation_years' => $c['years'],
                'depreciation_method' => 'straight_line',
                'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $ids[$c['code']] = $id;
        }
        $this->command->line('  Asset categories: ' . \count($cats));
        return $ids;
    }

    // ─── Assets ───────────────────────────────────────────────────────────────
    private function seedAssets(array $catIds, array $branches, array $profileIds): void
    {
        $headOffice = $branches['head_office'] ?? array_values($branches)[0];
        $adminId    = 3; // user_id of Bank Admin (added_by references users.id bigint)
        $assets     = [
            // IT Equipment
            ['cat' => 'IT',  'name' => 'Dell OptiPlex Desktop',        'tag' => 'AST-IT-001', 'serial' => 'DL2024001', 'value' => 450_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'IT',  'name' => 'HP EliteBook Laptop',          'tag' => 'AST-IT-002', 'serial' => 'HP2024002', 'value' => 650_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'IT',  'name' => 'HP EliteBook Laptop',          'tag' => 'AST-IT-003', 'serial' => 'HP2024003', 'value' => 650_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'ikeja'],
            ['cat' => 'IT',  'name' => 'Dell Server PowerEdge T40',    'tag' => 'AST-IT-004', 'serial' => 'DLSV2024',  'value' => 2_800_000,'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'IT',  'name' => 'Cisco Network Switch 24-Port', 'tag' => 'AST-IT-005', 'serial' => 'CSCO2024',  'value' => 380_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'IT',  'name' => 'HP LaserJet Printer',          'tag' => 'AST-IT-006', 'serial' => 'HPPR2024',  'value' => 120_000,  'cond' => 'fair',      'status' => 'assigned',      'branch' => 'ikeja'],
            ['cat' => 'IT',  'name' => 'Lenovo ThinkPad Laptop',       'tag' => 'AST-IT-007', 'serial' => 'LV2024007', 'value' => 580_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'abuja'],
            ['cat' => 'IT',  'name' => 'Dell Desktop Computer',        'tag' => 'AST-IT-008', 'serial' => 'DL2024008', 'value' => 420_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'kano'],
            // Furniture
            ['cat' => 'FRN', 'name' => 'Executive Office Desk',        'tag' => 'AST-FRN-001','serial' => 'FRN2024001','value' => 85_000,   'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'FRN', 'name' => 'Ergonomic Office Chair (x5)',  'tag' => 'AST-FRN-002','serial' => 'FRN2024002','value' => 175_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'FRN', 'name' => 'Customer Waiting Sofa Set',    'tag' => 'AST-FRN-003','serial' => 'FRN2024003','value' => 120_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'ikeja'],
            ['cat' => 'FRN', 'name' => 'Filing Cabinet (4-drawer)',    'tag' => 'AST-FRN-004','serial' => 'FRN2024004','value' => 45_000,   'cond' => 'fair',      'status' => 'assigned',      'branch' => 'abuja'],
            // Vehicles
            ['cat' => 'VEH', 'name' => 'Toyota Corolla (2022)',        'tag' => 'AST-VEH-001','serial' => 'LAG-123-AA','value' => 8_500_000,'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'VEH', 'name' => 'Toyota HiAce Bus (2021)',      'tag' => 'AST-VEH-002','serial' => 'LAG-456-BB','value' => 12_000_000,'cond'=> 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'VEH', 'name' => 'Yamaha Dispatch Motorcycle',   'tag' => 'AST-VEH-003','serial' => 'MTC2024003','value' => 450_000,  'cond' => 'fair',      'status' => 'assigned',      'branch' => 'ikeja'],
            // Office Equipment
            ['cat' => 'OEQ', 'name' => 'Konica Minolta Photocopier',   'tag' => 'AST-OEQ-001','serial' => 'KM2024001', 'value' => 750_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'OEQ', 'name' => 'Main Vault Safe (2-tonne)',    'tag' => 'AST-OEQ-002','serial' => 'SFE2024001','value' => 2_500_000,'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'OEQ', 'name' => 'Currency Counting Machine',    'tag' => 'AST-OEQ-003','serial' => 'CCM2024001','value' => 180_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'ikeja'],
            // Generators
            ['cat' => 'GEN', 'name' => 'Mikano 100kVA Generator',      'tag' => 'AST-GEN-001','serial' => 'MKN2024001','value' => 6_500_000,'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'GEN', 'name' => 'Perkins 45kVA Generator',      'tag' => 'AST-GEN-002','serial' => 'PRK2024002','value' => 3_200_000,'cond' => 'good',      'status' => 'assigned',      'branch' => 'abuja'],
            ['cat' => 'GEN', 'name' => 'APC Smart-UPS 3kVA',           'tag' => 'AST-GEN-003','serial' => 'APC2024003','value' => 280_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'ikeja'],
            // CCTV
            ['cat' => 'SEC', 'name' => 'CCTV System (8-Channel)',       'tag' => 'AST-SEC-001','serial' => 'CTV2024001','value' => 650_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'SEC', 'name' => 'Biometric Door Access System', 'tag' => 'AST-SEC-002','serial' => 'BIO2024002','value' => 450_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'head_office'],
            ['cat' => 'SEC', 'name' => 'CCTV System (4-Channel)',       'tag' => 'AST-SEC-003','serial' => 'CTV2024003','value' => 380_000,  'cond' => 'fair',      'status' => 'assigned',      'branch' => 'ikeja'],
            ['cat' => 'IT',  'name' => 'HP Desktop Computer',          'tag' => 'AST-IT-009', 'serial' => 'HP2024009', 'value' => 420_000,  'cond' => 'poor',      'status' => 'under_maintenance', 'branch' => 'phc'],
            ['cat' => 'FRN', 'name' => 'Reception Counter Unit',       'tag' => 'AST-FRN-005','serial' => 'FRN2024005','value' => 200_000,  'cond' => 'good',      'status' => 'assigned',      'branch' => 'enugu'],
        ];

        $purchaseBase = now()->subMonths(18);
        $count = 0;
        foreach ($assets as $i => $a) {
            $catId       = $catIds[$a['cat']] ?? null;
            $branchId    = $branches[$a['branch']] ?? $headOffice;
            $purchased   = $purchaseBase->copy()->addDays(rand(0, 400))->toDateString();
            $currentVal  = round($a['value'] * 0.8, 2); // 20% depreciated

            DB::table('assets')->insert([
                'id'               => (string) Str::uuid(),
                'tenant_id'        => $this->tenantId,
                'category_id'      => $catId,
                'name'             => $a['name'],
                'asset_tag'        => $a['tag'],
                'serial_number'    => $a['serial'],
                'model'            => null,
                'manufacturer'     => null,
                'vendor'           => 'FCI Nigeria Ltd',
                'purchase_date'    => $purchased,
                'purchase_price'   => $a['value'],
                'current_value'    => $currentVal,
                'warranty_expiry'  => now()->addYear()->toDateString(),
                'condition'        => $a['cond'],
                'status'           => $a['status'],
                'location'         => 'Main Floor',
                'branch_id'        => $branchId,
                'notes'            => null,
                'invoice_number'   => 'INV-' . strtoupper(Str::random(6)),
                'photo_path'       => null,
                'added_by'         => $adminId,
                'created_at'       => $purchased,
                'updated_at'       => now(),
            ]);
            $count++;
        }
        $this->command->line("  Assets: {$count}");
    }

    // ─── Performance Reviews ──────────────────────────────────────────────────
    private function seedPerformanceReviews(array $profileIds): void
    {
        $cycle = DB::table('review_cycles')->where('tenant_id', $this->tenantId)->first();
        if (!$cycle) { $this->command->warn('  No review cycles found — skip performance reviews.'); return; }

        $reviewerProfileId = $profileIds[5] ?? array_values($profileIds)[0]; // profile UUID for staff_profile comparisons
        $reviewerUserId    = 5; // user_id integer for reviewer_id FK
        $ratings    = ['outstanding', 'exceeds_expectations', 'meets_expectations', 'needs_improvement'];
        $scores     = [92, 85, 75, 65, 90, 80, 78, 88, 70, 82];
        $statusList = ['self_assessed', 'manager_reviewed', 'manager_reviewed', 'hr_approved'];

        $count  = 0;
        $sample = array_slice($profileIds, 0, 10, true);

        foreach ($sample as $userId => $profileId) {
            if ($profileId === $reviewerProfileId) continue;
            $score  = $scores[$count % \count($scores)];
            $rating = $score >= 88 ? 'exceptional' : ($score >= 78 ? 'exceeds_expectations' : ($score >= 65 ? 'meets_expectations' : 'below_expectations'));
            $status = $statusList[$count % \count($statusList)];

            $reviewId = (string) Str::uuid();
            DB::table('performance_reviews')->insert([
                'id'               => $reviewId,
                'tenant_id'        => $this->tenantId,
                'review_cycle_id'  => $cycle->id,
                'staff_profile_id' => $profileId,
                'reviewer_id'      => $reviewerUserId,
                'status'           => $status,
                'overall_score'    => $score,
                'rating'           => $rating,
                'staff_comments'   => 'I have achieved my targets for the review period and look forward to more responsibilities.',
                'manager_comments' => in_array($status, ['manager_reviewed','hr_approved']) ? 'Good performance. Consistent delivery. Continue maintaining quality standards.' : null,
                'submitted_at'     => now()->subDays(rand(7, 30)),
                'reviewed_at'      => in_array($status, ['manager_reviewed','hr_approved']) ? now()->subDays(rand(1, 7)) : null,
                'created_at'       => now()->subMonths(1),
                'updated_at'       => now(),
            ]);

            // Add 3 review items per review
            $items = [
                ['criterion' => 'Customer Satisfaction',  'weight' => 30, 'self' => min(99,$score),            'manager' => min(99,$score - rand(0,5)),  'max' => 99, 'desc' => 'Maintain high customer satisfaction scores'],
                ['criterion' => 'Sales / Loan Targets',   'weight' => 40, 'self' => min(99,$score-rand(0,10)), 'manager'=> min(99,$score-rand(0,8)),     'max' => 99, 'desc' => 'Achieve assigned loan disbursement targets'],
                ['criterion' => 'Compliance & Conduct',   'weight' => 30, 'self' => min(99,$score+5),          'manager' => min(99,$score+3),             'max' => 99, 'desc' => 'Adhere to regulatory and internal compliance standards'],
            ];
            foreach ($items as $item) {
                DB::table('performance_review_items')->insert([
                    'id'                  => (string) Str::uuid(),
                    'review_id'           => $reviewId,
                    'criterion'           => $item['criterion'],
                    'weight'              => $item['weight'],
                    'self_score'          => $item['self'],
                    'manager_score'       => $item['manager'],
                    'max_score'           => $item['max'],
                    'target_description'  => $item['desc'],
                    'achievement_notes'   => null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
            $count++;
        }
        $this->command->line("  Performance reviews: {$count} (with items)");
    }

    // ─── Salary Advances ──────────────────────────────────────────────────────
    private function seedSalaryAdvances(array $profileIds): void
    {
        $statuses = ['approved', 'approved', 'pending', 'rejected', 'disbursed'];
        $count    = 0;
        $sample   = array_slice($profileIds, 2, 6, true); // skip first 2 (senior)

        foreach ($sample as $userId => $profileId) {
            $status   = $statuses[$count % \count($statuses)];
            $amount   = [50_000, 75_000, 100_000, 50_000, 80_000, 60_000][$count];
            $months   = [3, 3, 6, 3, 4, 3][$count];

            DB::table('salary_advances')->insert([
                'id'                   => (string) Str::uuid(),
                'tenant_id'            => $this->tenantId,
                'user_id'              => $userId,
                'staff_profile_id'     => $profileId,
                'amount_requested'     => $amount,
                'amount_approved'      => in_array($status, ['approved','disbursed']) ? $amount : null,
                'reason'               => 'Medical expenses / emergency household repair.',
                'repayment_months'     => $months,
                'monthly_deduction'    => round($amount / $months, 2),
                'status'               => $status,
                'approval_request_id'  => null,
                'approved_by'          => in_array($status, ['approved','disbursed']) ? 5 : null, // user_id integer
                'approved_at'          => in_array($status, ['approved','disbursed']) ? now()->subDays(rand(3, 14)) : null,
                'rejection_reason'     => $status === 'rejected' ? 'Outstanding advance still being repaid.' : null,
                'disbursed_at'         => $status === 'disbursed' ? now()->subDays(rand(1, 5)) : null,
                'balance_remaining'    => $status === 'disbursed' ? $amount - round($amount / $months, 2) : null,
                'created_at'           => now()->subDays(rand(5, 20)),
                'updated_at'           => now(),
            ]);
            $count++;
        }
        $this->command->line("  Salary advances: {$count}");
    }
}
