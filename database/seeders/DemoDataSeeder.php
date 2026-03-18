<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $this->command->info('🌱 Seeding bankOS demo data (Nigerian MFB)...');

        $this->seedSubscriptionPlans();
        $this->seedTenantSubscription();
        $this->seedBranches();
        $this->seedSavingsProducts();
        $this->seedLoanProducts();
        $this->seedCustomers();
        $this->seedAccounts();
        $this->seedLoans();
        $this->seedTransactions();
        $this->seedAmlRules();
        $this->seedTransactionLimits();
        $this->seedFeeRules();
        $this->seedFeatureFlags();
        $this->seedExchangeRates();

        $this->command->info('✅ Done!');
    }

    // ── Subscription Plans ────────────────────────────────────────────────────

    private function seedSubscriptionPlans(): void
    {
        $plans = [
            ['name'=>'Starter',    'slug'=>'starter',    'price_monthly'=>15000,  'price_yearly'=>150000,  'max_customers'=>500,    'max_staff_users'=>5,   'max_branches'=>1,  'max_transactions_monthly'=>5000,   'features'=>['core_banking','customer_portal','basic_reports']],
            ['name'=>'Growth',     'slug'=>'growth',     'price_monthly'=>45000,  'price_yearly'=>450000,  'max_customers'=>5000,   'max_staff_users'=>25,  'max_branches'=>5,  'max_transactions_monthly'=>50000,  'features'=>['core_banking','customer_portal','advanced_reports','aml','api_access','webhooks']],
            ['name'=>'Enterprise', 'slug'=>'enterprise', 'price_monthly'=>120000, 'price_yearly'=>1200000, 'max_customers'=>999999, 'max_staff_users'=>999, 'max_branches'=>999,'max_transactions_monthly'=>999999, 'features'=>['core_banking','customer_portal','advanced_reports','aml','api_access','webhooks','white_label','dedicated_support']],
        ];

        foreach ($plans as $p) {
            $exists = DB::table('subscription_plans')->where('slug', $p['slug'])->exists();
            if ($exists) {
                DB::table('subscription_plans')->where('slug', $p['slug'])->update([
                    'name'                     => $p['name'],
                    'price_monthly'            => $p['price_monthly'],
                    'price_yearly'             => $p['price_yearly'],
                    'max_customers'            => $p['max_customers'],
                    'max_staff_users'          => $p['max_staff_users'],
                    'max_branches'             => $p['max_branches'],
                    'max_transactions_monthly' => $p['max_transactions_monthly'],
                    'features'                 => json_encode($p['features']),
                    'is_active'                => 1,
                    'updated_at'               => now(),
                ]);
            } else {
                DB::table('subscription_plans')->insert([
                    'id'                       => Str::uuid(),
                    'name'                     => $p['name'],
                    'slug'                     => $p['slug'],
                    'price_monthly'            => $p['price_monthly'],
                    'price_yearly'             => $p['price_yearly'],
                    'max_customers'            => $p['max_customers'],
                    'max_staff_users'          => $p['max_staff_users'],
                    'max_branches'             => $p['max_branches'],
                    'max_transactions_monthly' => $p['max_transactions_monthly'],
                    'features'                 => json_encode($p['features']),
                    'is_active'                => 1,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
            }
        }
        $this->command->info('  ✓ Subscription plans (Starter ₦15k / Growth ₦45k / Enterprise ₦120k)');
    }

    private function seedTenantSubscription(): void
    {
        $planId = DB::table('subscription_plans')->where('slug', 'growth')->value('id');
        if (!$planId) return;

        DB::table('tenant_subscriptions')->updateOrInsert(
            ['tenant_id' => $this->tenantId],
            [
                'id'                   => Str::uuid(),
                'plan_id'              => $planId,
                'status'               => 'active',
                'current_period_start' => now()->startOfMonth(),
                'current_period_end'   => now()->endOfMonth(),
                'amount_paid'          => 45000,
                'billing_cycle'        => 'monthly',
                'created_at'           => now()->subMonths(3),
                'updated_at'           => now(),
            ]
        );
        DB::table('tenants')->where('id', $this->tenantId)->update(['subscription_plan' => 'growth']);
        $this->command->info('  ✓ Tenant on Growth plan (active)');
    }

    // ── Branches ──────────────────────────────────────────────────────────────

    private function seedBranches(): void
    {
        if (DB::table('branches')->where('tenant_id', $this->tenantId)->count() >= 3) {
            $this->command->info('  ✓ Branches already present, skipping');
            return;
        }

        $branches = [
            ['code'=>'HO',  'name'=>'Head Office',     'city'=>'Lagos Island', 'state'=>'Lagos', 'street'=>'12 Broad Street'],
            ['code'=>'IKJ', 'name'=>'Ikeja Branch',    'city'=>'Ikeja',        'state'=>'Lagos', 'street'=>'5 Oba Akran Avenue'],
            ['code'=>'SUR', 'name'=>'Surulere Branch', 'city'=>'Surulere',     'state'=>'Lagos', 'street'=>'22 Adelabu Street'],
            ['code'=>'LEK', 'name'=>'Lekki Branch',    'city'=>'Lekki',        'state'=>'Lagos', 'street'=>'7 Admiralty Way'],
            ['code'=>'ABJ', 'name'=>'Abuja Branch',    'city'=>'Garki',        'state'=>'Abuja', 'street'=>'15 Tafawa Balewa Way'],
        ];

        foreach ($branches as $b) {
            DB::table('branches')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $b['code']],
                [
                    'id'         => Str::uuid(),
                    'tenant_id'  => $this->tenantId,
                    'name'       => $b['name'],
                    'code'       => $b['code'],
                    'branch_code'=> $b['code'],
                    'phone'      => '+234801' . rand(1000000, 9999999),
                    'email'      => strtolower(preg_replace('/\s+/', '', $b['name'])) . '@demomfb.com',
                    'street'     => $b['street'],
                    'city'       => $b['city'],
                    'state'      => $b['state'],
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('  ✓ 5 branches (Lagos ×4, Abuja ×1)');
    }

    // ── Products ──────────────────────────────────────────────────────────────

    private function seedSavingsProducts(): void
    {
        if (DB::table('savings_products')->where('tenant_id', $this->tenantId)->count() >= 3) {
            $this->command->info('  ✓ Savings products already present, skipping');
            return;
        }

        $products = [
            ['name'=>'Regular Savings',  'code'=>'REG-SAV', 'rate'=>2.5, 'type'=>'standard',       'min_bal'=>1000,  'min_open'=>5000,  'lock'=>0],
            ['name'=>'Target Savings',   'code'=>'TGT-SAV', 'rate'=>4.0, 'type'=>'goal_based',     'min_bal'=>5000,  'min_open'=>10000, 'lock'=>90],
            ['name'=>'Kids Savings',     'code'=>'KID-SAV', 'rate'=>3.0, 'type'=>'standard',       'min_bal'=>500,   'min_open'=>1000,  'lock'=>0],
            ['name'=>'Business Savings', 'code'=>'BIZ-SAV', 'rate'=>3.5, 'type'=>'standard',       'min_bal'=>10000, 'min_open'=>50000, 'lock'=>0],
            ['name'=>'Fixed Deposit',    'code'=>'FXD-DEP', 'rate'=>8.0, 'type'=>'fixed_deposit',  'min_bal'=>50000, 'min_open'=>100000,'lock'=>180],
        ];

        foreach ($products as $p) {
            DB::table('savings_products')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $p['code']],
                [
                    'id'                       => Str::uuid(),
                    'tenant_id'                => $this->tenantId,
                    'name'                     => $p['name'],
                    'code'                     => $p['code'],
                    'description'              => $p['name'] . ' product',
                    'currency'                 => 'NGN',
                    'interest_rate'            => $p['rate'],
                    'interest_frequency'       => 'monthly',
                    'min_balance'              => $p['min_bal'],
                    'min_opening'              => $p['min_open'],
                    'max_withdrawal_daily'     => 500000,
                    'max_withdrawals_monthly'  => 10,
                    'lock_in_period'           => $p['lock'],
                    'early_withdrawal_penalty' => $p['lock'] > 0 ? 1.5 : 0,
                    'monthly_fee'              => 0,
                    'min_balance_penalty'      => 0,
                    'product_type'             => $p['type'],
                    'status'                   => 'active',
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]
            );
        }
        $this->command->info('  ✓ 5 savings products');
    }

    private function seedLoanProducts(): void
    {
        if (DB::table('loan_products')->where('tenant_id', $this->tenantId)->count() >= 3) {
            $this->command->info('  ✓ Loan products already present, skipping');
            return;
        }

        $products = [
            ['name'=>'Salary Advance', 'code'=>'SAL-ADV', 'max'=>500000,   'min'=>10000,  'rate'=>5.0,  'method'=>'flat',             'amort'=>'bullet',            'min_t'=>14,  'max_t'=>30,  'group'=>0],
            ['name'=>'SME Loan',       'code'=>'SME-LON', 'max'=>5000000,  'min'=>100000, 'rate'=>3.0,  'method'=>'reducing_balance',  'amort'=>'equal_installment', 'min_t'=>90,  'max_t'=>365, 'group'=>0],
            ['name'=>'Group Loan',     'code'=>'GRP-LON', 'max'=>200000,   'min'=>10000,  'rate'=>2.5,  'method'=>'flat',             'amort'=>'equal_installment', 'min_t'=>90,  'max_t'=>180, 'group'=>1],
            ['name'=>'Asset Finance',  'code'=>'AST-FIN', 'max'=>10000000, 'min'=>500000, 'rate'=>2.0,  'method'=>'reducing_balance',  'amort'=>'equal_installment', 'min_t'=>180, 'max_t'=>730, 'group'=>0],
            ['name'=>'Emergency Loan', 'code'=>'EMG-LON', 'max'=>100000,   'min'=>5000,   'rate'=>10.0, 'method'=>'flat',             'amort'=>'bullet',            'min_t'=>7,   'max_t'=>14,  'group'=>0],
            ['name'=>'Agric Loan',     'code'=>'AGR-LON', 'max'=>1000000,  'min'=>50000,  'rate'=>1.5,  'method'=>'reducing_balance',  'amort'=>'equal_installment', 'min_t'=>180, 'max_t'=>365, 'group'=>0],
        ];

        foreach ($products as $p) {
            DB::table('loan_products')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $p['code']],
                [
                    'id'                     => Str::uuid(),
                    'tenant_id'              => $this->tenantId,
                    'name'                   => $p['name'],
                    'code'                   => $p['code'],
                    'description'            => $p['name'] . ' — Nigerian MFB standard product',
                    'currency'               => 'NGN',
                    'interest_rate'          => $p['rate'],
                    'interest_method'        => $p['method'],
                    'amortization'           => $p['amort'],
                    'min_amount'             => $p['min'],
                    'max_amount'             => $p['max'],
                    'min_tenure'             => $p['min_t'],
                    'max_tenure'             => $p['max_t'],
                    'max_dti'                => 40,
                    'processing_fee'         => 1.0,
                    'insurance_fee'          => 0.5,
                    'grace_period'           => 3,
                    'group_lending'          => $p['group'],
                    'ai_assessment'          => 0,
                    'early_repayment'        => 1,
                    'early_repayment_penalty'=> 0,
                    'collateral_types'       => json_encode(['none','guarantor','asset']),
                    'status'                 => 'active',
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]
            );
        }
        $this->command->info('  ✓ 6 loan products');
    }

    // ── Customers ─────────────────────────────────────────────────────────────

    private function seedCustomers(): void
    {
        if (DB::table('customers')->where('tenant_id', $this->tenantId)->count() >= 30) {
            $this->command->info('  ✓ Customers already seeded, skipping');
            return;
        }

        $branches = DB::table('branches')->where('tenant_id', $this->tenantId)->pluck('id')->toArray();

        $firstNames = ['Adebayo','Chioma','Ibrahim','Ngozi','Emeka','Fatima','Oluwaseun','Blessing',
                       'Abdullahi','Amaka','Chukwuemeka','Halima','Olumide','Ifeoma','Musa','Chiamaka',
                       'Taiwo','Yetunde','Suleiman','Adaeze','Babatunde','Nneka','Garba','Chinwe',
                       'Olamide','Mariam','Chidi','Rukayat','Kayode','Obiageli','Tunde','Adeola',
                       'Umar','Chinyere','Segun','Zainab','Femi','Nkechi','Bello','Uchenna'];
        $lastNames  = ['Okonkwo','Abubakar','Adeyemi','Ibrahim','Nwosu','Babatunde','Eze','Suleiman',
                       'Okafor','Musa','Adeleke','Usman','Nwachukwu','Yusuf','Olawale','Hassan',
                       'Chukwu','Garba','Obioma','Aliyu','Fasanya','Danladi','Obi','Lawal',
                       'Effiong','Jimoh','Onyekachi','Bello','Afolabi','Nduka'];
        $states     = ['Lagos','Lagos','Lagos','Lagos','Abuja','Rivers','Kano','Ogun','Anambra','Oyo'];
        $cities     = ['Ikeja','Lagos Island','Lekki','Surulere','Victoria Island','Garki','Wuse','Port Harcourt','Kano','Ibadan'];
        $occupations= ['Civil Servant','Trader','Teacher','Nurse','Engineer','Lawyer','Farmer','Driver','Accountant','Entrepreneur','Artisan','Student'];

        $customers = [];
        $usedNums  = [];

        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName  = $lastNames[array_rand($lastNames)];
            $state     = $states[$i % count($states)];
            $city      = $cities[$i % count($cities)];

            do { $custNum = 'CUS-' . rand(10000, 99999); } while (in_array($custNum, $usedNums));
            $usedNums[] = $custNum;

            $kycTier   = ['level_1','level_1','level_2','level_2','level_2','level_3','level_3'][$i % 7];
            // kyc_status enum: auto_approved, manual_review, approved, rejected

            $customers[] = [
                'id'              => Str::uuid(),
                'tenant_id'       => $this->tenantId,
                'branch_id'       => $branches[$i % count($branches)],
                'customer_number' => $custNum,
                'type'            => 'individual',
                'first_name'      => $firstName,
                'last_name'       => $lastName,
                'date_of_birth'   => Carbon::now()->subYears(rand(22, 65))->subDays(rand(0, 364))->toDateString(),
                'gender'          => $i % 2 === 0 ? 'male' : 'female',
                'email'           => strtolower($firstName . '.' . $lastName . ($i + 1)) . '@gmail.com',
                'portal_active'   => rand(0, 9) < 6 ? 1 : 0,
                'phone'           => '+2348' . rand(10000000, 99999999),
                'occupation'      => $occupations[array_rand($occupations)],
                'marital_status'  => ['single','married','married'][array_rand(['single','married','married'])],
                'address'         => json_encode([
                    'street'  => rand(1, 99) . ' ' . $lastName . ' Street',
                    'city'    => $city,
                    'state'   => $state,
                    'country' => 'Nigeria',
                ]),
                'bvn'             => '22' . rand(100000000, 999999999),
                'nin'             => (string)rand(10000000000, 99999999999),
                'bvn_verified'    => in_array($kycTier, ['level_2', 'level_3']) ? 1 : 0,
                'nin_verified'    => $kycTier === 'level_3' ? 1 : 0,
                'kyc_tier'        => $kycTier,
                'kyc_status'      => in_array($kycTier, ['level_2', 'level_3']) ? 'approved' : (rand(0, 3) > 0 ? 'approved' : 'manual_review'),
                'status'          => 'active',
                'notify_sms'      => 1,
                'notify_email'    => 1,
                'referral_code'   => strtoupper(Str::random(8)),
                'created_at'      => Carbon::now()->subMonths(rand(1, 18))->subDays(rand(0, 28)),
                'updated_at'      => now(),
            ];
        }

        DB::table('customers')->insert($customers);
        $this->command->info('  ✓ 50 customers (Nigerian names, BVN, NIN)');
    }

    // ── Accounts ──────────────────────────────────────────────────────────────

    private function seedAccounts(): void
    {
        if (DB::table('accounts')->where('tenant_id', $this->tenantId)->count() >= 30) {
            $this->command->info('  ✓ Accounts already seeded, skipping');
            return;
        }

        $customers = DB::table('customers')->where('tenant_id', $this->tenantId)->get();
        $savingsId = DB::table('savings_products')->where('tenant_id', $this->tenantId)->where('code', 'REG-SAV')->value('id');
        $bizSavId  = DB::table('savings_products')->where('tenant_id', $this->tenantId)->where('code', 'BIZ-SAV')->value('id');

        $accounts = [];
        $usedNums = [];

        foreach ($customers as $cust) {
            do { $accNum = '100' . rand(1000000, 9999999); } while (in_array($accNum, $usedNums));
            $usedNums[] = $accNum;
            $bal = rand(5, 1500) * 1000;

            $accounts[] = [
                'id'                 => Str::uuid(),
                'tenant_id'          => $this->tenantId,
                'branch_id'          => $cust->branch_id,
                'customer_id'        => $cust->id,
                'account_number'     => $accNum,
                'account_name'       => $cust->first_name . ' ' . $cust->last_name,
                'type'               => 'savings',
                'currency'           => 'NGN',
                'available_balance'  => $bal,
                'ledger_balance'     => $bal,
                'savings_product_id' => $savingsId,
                'status'             => 'active',
                'created_at'         => $cust->created_at,
                'updated_at'         => now(),
            ];

            // 40% also get a current account
            if (in_array($cust->kyc_tier, ['level_2', 'level_3']) && rand(0, 9) < 4) {
                do { $accNum2 = '100' . rand(1000000, 9999999); } while (in_array($accNum2, $usedNums));
                $usedNums[] = $accNum2;
                $bal2 = rand(20, 3000) * 1000;

                $accounts[] = [
                    'id'                 => Str::uuid(),
                    'tenant_id'          => $this->tenantId,
                    'branch_id'          => $cust->branch_id,
                    'customer_id'        => $cust->id,
                    'account_number'     => $accNum2,
                    'account_name'       => $cust->first_name . ' ' . $cust->last_name,
                    'type'               => 'current',
                    'currency'           => 'NGN',
                    'available_balance'  => $bal2,
                    'ledger_balance'     => $bal2,
                    'savings_product_id' => $bizSavId,
                    'status'             => 'active',
                    'created_at'         => $cust->created_at,
                    'updated_at'         => now(),
                ];
            }
        }

        DB::table('accounts')->insert($accounts);
        $this->command->info('  ✓ ' . count($accounts) . ' accounts (savings + some current)');
    }

    // ── Loans ─────────────────────────────────────────────────────────────────

    private function seedLoans(): void
    {
        if (DB::table('loans')->where('tenant_id', $this->tenantId)->count() >= 15) {
            $this->command->info('  ✓ Loans already seeded, skipping');
            return;
        }

        $customers = DB::table('customers')->where('tenant_id', $this->tenantId)->get();
        $products  = DB::table('loan_products')->where('tenant_id', $this->tenantId)->get()->keyBy('code');
        $officer   = DB::table('users')->where('tenant_id', $this->tenantId)->value('id');

        $loanConfigs = [
            ['code'=>'SAL-ADV', 'amounts'=>[50000,100000,200000,300000,500000],    'statuses'=>['active','active','closed','overdue']],
            ['code'=>'SME-LON', 'amounts'=>[200000,500000,1000000,2000000,3000000],'statuses'=>['active','active','active','overdue','closed']],
            ['code'=>'EMG-LON', 'amounts'=>[20000,50000,80000,100000],             'statuses'=>['active','closed','closed','overdue']],
            ['code'=>'AST-FIN', 'amounts'=>[1000000,2500000,5000000],              'statuses'=>['active','active','active']],
            ['code'=>'AGR-LON', 'amounts'=>[100000,300000,500000],                 'statuses'=>['active','pending','closed']],
        ];

        $loans = [];
        $seq   = 1;

        foreach ($customers as $idx => $cust) {
            if ($idx >= 30) break;
            if ($idx % 2 !== 0) continue; // every other customer gets a loan

            $cfg     = $loanConfigs[$idx % count($loanConfigs)];
            $product = $products[$cfg['code']] ?? null;
            if (!$product) continue;

            $principal  = $cfg['amounts'][array_rand($cfg['amounts'])];
            $status     = $cfg['statuses'][array_rand($cfg['statuses'])];
            $disbursedAt= Carbon::now()->subDays(rand(30, 300));
            $maturity   = $disbursedAt->copy()->addDays($product->max_tenure);
            $outstanding= in_array($status, ['active','overdue'])
                ? round($principal * (rand(20, 85) / 100), 2)
                : ($status === 'closed' ? 0 : $principal);

            $account = DB::table('accounts')
                ->where('tenant_id', $this->tenantId)
                ->where('customer_id', $cust->id)
                ->value('id');

            $loans[] = [
                'id'                     => Str::uuid(),
                'tenant_id'              => $this->tenantId,
                'branch_id'              => $cust->branch_id,
                'customer_id'            => $cust->id,
                'officer_id'             => $officer,
                'account_id'             => $account,
                'product_id'             => $product->id,
                'loan_number'            => 'LN-' . date('Y') . '-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
                'principal_amount'       => $principal,
                'outstanding_balance'    => $outstanding,
                'interest_rate'          => $product->interest_rate,
                'interest_method'        => $product->interest_method,
                'amortization'           => $product->amortization,
                'tenure_days'            => $product->max_tenure,
                'repayment_frequency'    => 'monthly',
                'purpose'                => ['Business expansion','Working capital','Asset purchase','Emergency','Farm inputs'][array_rand(['Business expansion','Working capital','Asset purchase','Emergency','Farm inputs'])],
                'source_channel'         => ['branch','web','agent'][array_rand(['branch','web','agent'])],
                'ifrs9_stage'            => $status === 'overdue' ? 'stage_2' : 'stage_1',
                'status'                 => $status,
                'expected_maturity_date' => $maturity->toDateString(),
                'disbursed_at'           => in_array($status, ['active','overdue','closed']) ? $disbursedAt : null,
                'created_at'             => $disbursedAt->copy()->subDays(rand(3, 10)),
                'updated_at'             => now(),
            ];
        }

        DB::table('loans')->insert($loans);
        $this->command->info('  ✓ ' . count($loans) . ' loans (active/overdue/completed/pending)');
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    private function seedTransactions(): void
    {
        if (DB::table('transactions')->where('tenant_id', $this->tenantId)->count() >= 80) {
            $this->command->info('  ✓ Transactions already seeded, skipping');
            return;
        }

        $accounts = DB::table('accounts')->where('tenant_id', $this->tenantId)->pluck('id')->toArray();
        $officer  = DB::table('users')->where('tenant_id', $this->tenantId)->value('id');

        if (empty($accounts)) {
            $this->command->warn('  ! No accounts found, skipping transactions');
            return;
        }

        $typeData = [
            'deposit'         => ['Cash Deposit','Salary Credit','Business Income','Transfer Credit','Agent Deposit','USSD Transfer In'],
            'withdrawal'      => ['ATM Withdrawal','Cash Withdrawal','POS Purchase','Mobile Transfer Out'],
            'transfer'        => ['NIP Transfer','USSD Transfer','Portal Transfer','Standing Order'],
            'fee'             => ['SMS Alert Fee','Transfer Fee','Account Maintenance','Card Fee'],
            'interest' => ['Monthly Interest','Savings Interest'],
        ];

        $transactions = [];

        for ($i = 0; $i < 200; $i++) {
            $type  = array_rand($typeData);
            $descs = $typeData[$type];
            $date  = Carbon::now()->subDays(rand(1, 180))->subHours(rand(0, 23));

            $amount = match($type) {
                'deposit'    => rand(5, 500) * 1000,
                'withdrawal' => rand(2, 200) * 1000,
                'transfer'   => rand(5, 300) * 1000,
                'fee'        => rand(1, 10) * 100,
                'interest'   => rand(500, 50000),
            };

            $transactions[] = [
                'id'          => Str::uuid(),
                'tenant_id'   => $this->tenantId,
                'account_id'  => $accounts[array_rand($accounts)],
                'reference'   => 'TXN-' . $date->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'type'        => $type,
                'amount'      => $amount,
                'currency'    => 'NGN',
                'description' => $descs[array_rand($descs)],
                'status'      => 'success',
                'performed_by'=> $officer,
                'created_at'  => $date,
                'updated_at'  => $date,
            ];
        }

        DB::table('transactions')->insert($transactions);
        $this->command->info('  ✓ 200 transactions (6 months history)');
    }

    // ── AML Rules ─────────────────────────────────────────────────────────────

    private function seedAmlRules(): void
    {
        if (DB::table('aml_rules')->where('tenant_id', $this->tenantId)->count() > 0) {
            $this->command->info('  ✓ AML rules already seeded, skipping');
            return;
        }

        $rules = [
            // rule_type enum: velocity, amount_threshold, structuring, round_amount, dormancy_reactivation
            ['rule_code'=>'LARGE_AMT',   'rule_name'=>'Large Cash Transaction (₦5m+)',  'rule_type'=>'amount_threshold',      'threshold_amount'=>5000000,  'threshold_count'=>null,'time_window_hours'=>null,'severity'=>'high',    'auto_block'=>0],
            ['rule_code'=>'VERY_LARGE',  'rule_name'=>'CTR — Very Large Transaction',   'rule_type'=>'amount_threshold',      'threshold_amount'=>25000000, 'threshold_count'=>null,'time_window_hours'=>null,'severity'=>'critical','auto_block'=>1],
            ['rule_code'=>'ROUND_AMT',   'rule_name'=>'Round Amount Structuring',       'rule_type'=>'round_amount',          'threshold_amount'=>1000000,  'threshold_count'=>null,'time_window_hours'=>null,'severity'=>'medium',  'auto_block'=>0],
            ['rule_code'=>'VELOCITY_HR', 'rule_name'=>'Hourly Velocity Check',          'rule_type'=>'velocity',              'threshold_amount'=>null,     'threshold_count'=>10,  'time_window_hours'=>1,   'severity'=>'high',    'auto_block'=>0],
            ['rule_code'=>'VELOCITY_DAY','rule_name'=>'Daily Velocity Check',           'rule_type'=>'velocity',              'threshold_amount'=>null,     'threshold_count'=>25,  'time_window_hours'=>24,  'severity'=>'medium',  'auto_block'=>0],
            ['rule_code'=>'STRUCTURING', 'rule_name'=>'Structuring Detection (NFIU)',   'rule_type'=>'structuring',           'threshold_amount'=>4900000,  'threshold_count'=>3,   'time_window_hours'=>24,  'severity'=>'critical','auto_block'=>0],
            ['rule_code'=>'DORMANT_ACT', 'rule_name'=>'Dormant Account Reactivation',  'rule_type'=>'dormancy_reactivation', 'threshold_amount'=>100000,   'threshold_count'=>null,'time_window_hours'=>null,'severity'=>'medium',  'auto_block'=>0],
            ['rule_code'=>'RAPID_MOV',   'rule_name'=>'Rapid Fund Movement',            'rule_type'=>'velocity',              'threshold_amount'=>2000000,  'threshold_count'=>5,   'time_window_hours'=>2,   'severity'=>'high',    'auto_block'=>0],
        ];

        foreach ($rules as $rule) {
            DB::table('aml_rules')->insert(array_merge($rule, [
                'id'         => Str::uuid(),
                'tenant_id'  => $this->tenantId,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        $this->command->info('  ✓ 8 AML rules (CBN-compliant thresholds)');
    }

    // ── Transaction Limits ────────────────────────────────────────────────────

    private function seedTransactionLimits(): void
    {
        if (DB::table('transaction_limits')->where('tenant_id', $this->tenantId)->count() > 0) {
            $this->command->info('  ✓ Transaction limits already seeded, skipping');
            return;
        }

        $limits = [
            ['level_1','portal', 'transfer',   20000,    100000,    500000],
            ['level_1','portal', 'withdrawal', 20000,    100000,    500000],
            ['level_1','ussd',   'transfer',   20000,    100000,    500000],
            ['level_2','portal', 'transfer',   200000,   1000000,   5000000],
            ['level_2','portal', 'withdrawal', 200000,   1000000,   5000000],
            ['level_2','ussd',   'transfer',   50000,    300000,    1000000],
            ['level_2','agent',  'withdrawal', 100000,   500000,    2000000],
            ['level_2','teller', 'withdrawal', 500000,   2000000,   10000000],
            ['level_3','portal', 'transfer',   5000000,  20000000,  100000000],
            ['level_3','portal', 'withdrawal', 5000000,  20000000,  100000000],
            ['level_3','ussd',   'transfer',   1000000,  5000000,   20000000],
            ['level_3','agent',  'withdrawal', 500000,   2000000,   10000000],
            ['level_3','teller', 'withdrawal', 5000000,  20000000,  100000000],
        ];

        foreach ($limits as [$tier, $channel, $type, $single, $daily, $monthly]) {
            DB::table('transaction_limits')->insert([
                'id'               => Str::uuid(),
                'tenant_id'        => $this->tenantId,
                'kyc_tier'         => $tier,
                'channel'          => $channel,
                'transaction_type' => $type,
                'single_limit'     => $single,
                'daily_limit'      => $daily,
                'monthly_limit'    => $monthly,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
        $this->command->info('  ✓ 13 transaction limits (3 KYC tiers × channels)');
    }

    // ── Fee Rules ─────────────────────────────────────────────────────────────

    private function seedFeeRules(): void
    {
        if (DB::table('fee_rules')->where('tenant_id', $this->tenantId)->count() > 0) {
            $this->command->info('  ✓ Fee rules already seeded, skipping');
            return;
        }

        $rules = [
            ['name'=>'NIP Transfer Fee',    'transaction_type'=>'transfer',    'account_type'=>null,      'fee_type'=>'flat',       'amount'=>50,   'min_fee'=>50,   'max_fee'=>50,    'min_transaction_amount'=>0,    'max_transaction_amount'=>null],
            ['name'=>'Account Maintenance', 'transaction_type'=>'maintenance', 'account_type'=>'current', 'fee_type'=>'flat',       'amount'=>100,  'min_fee'=>100,  'max_fee'=>100,   'min_transaction_amount'=>null, 'max_transaction_amount'=>null],
            ['name'=>'SMS Alert Fee',       'transaction_type'=>'any',         'account_type'=>null,      'fee_type'=>'flat',       'amount'=>10,   'min_fee'=>10,   'max_fee'=>10,    'min_transaction_amount'=>0,    'max_transaction_amount'=>null],
            ['name'=>'Loan Processing',     'transaction_type'=>'loan',        'account_type'=>null,      'fee_type'=>'percentage', 'amount'=>1.0,  'min_fee'=>500,  'max_fee'=>50000, 'min_transaction_amount'=>50000,'max_transaction_amount'=>null],
            ['name'=>'ATM Withdrawal',      'transaction_type'=>'withdrawal',  'account_type'=>null,      'fee_type'=>'flat',       'amount'=>35,   'min_fee'=>35,   'max_fee'=>35,    'min_transaction_amount'=>0,    'max_transaction_amount'=>null],
            ['name'=>'Card Issuance',       'transaction_type'=>'card',        'account_type'=>null,      'fee_type'=>'flat',       'amount'=>1000, 'min_fee'=>1000, 'max_fee'=>1000,  'min_transaction_amount'=>null, 'max_transaction_amount'=>null],
        ];

        foreach ($rules as $rule) {
            DB::table('fee_rules')->insert(array_merge($rule, [
                'id'         => Str::uuid(),
                'tenant_id'  => $this->tenantId,
                'is_active'  => 1,
                'waivable'   => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        $this->command->info('  ✓ 6 fee rules (CBN standard schedule)');
    }

    // ── Feature Flags ─────────────────────────────────────────────────────────

    private function seedFeatureFlags(): void
    {
        $keys = [
            'portal_savings_pockets','portal_investments','portal_loan_apply','portal_pay_requests',
            'portal_virtual_cards','portal_credit_score','portal_fx_rates','portal_referral',
            'portal_budget','portal_disputes','portal_kyc_upgrade','portal_2fa',
            'portal_account_freeze','portal_bills','portal_beneficiaries','portal_standing_orders',
            'ussd_banking','agent_banking','nip_transfers',
            'loan_restructure','loan_topup','ecl_provisioning',
            'teller_module','cheque_management','fixed_deposits','payroll_module',
        ];

        foreach ($keys as $key) {
            DB::table('tenant_feature_flags')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'customer_id' => null, 'feature_key' => $key],
                [
                    'tenant_id'  => $this->tenantId,
                    'feature_key'=> $key,
                    'is_enabled' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('  ✓ Feature flags (all portal features enabled)');
    }

    // ── Exchange Rates ────────────────────────────────────────────────────────

    private function seedExchangeRates(): void
    {
        if (DB::table('exchange_rates')->count() > 0) {
            $this->command->info('  ✓ Exchange rates already seeded, skipping');
            return;
        }

        $rates = [
            ['pair'=>'USD/NGN', 'buy_rate'=>1530.00, 'sell_rate'=>1550.00, 'mid_rate'=>1540.00],
            ['pair'=>'GBP/NGN', 'buy_rate'=>1910.00, 'sell_rate'=>1930.00, 'mid_rate'=>1920.00],
            ['pair'=>'EUR/NGN', 'buy_rate'=>1650.00, 'sell_rate'=>1670.00, 'mid_rate'=>1660.00],
            ['pair'=>'NGN/USD', 'buy_rate'=>0.00063,  'sell_rate'=>0.00067,  'mid_rate'=>0.00065],
            ['pair'=>'NGN/GBP', 'buy_rate'=>0.00050,  'sell_rate'=>0.00054,  'mid_rate'=>0.00052],
            ['pair'=>'NGN/EUR', 'buy_rate'=>0.00058,  'sell_rate'=>0.00062,  'mid_rate'=>0.00060],
        ];

        foreach ($rates as $r) {
            DB::table('exchange_rates')->insert(array_merge($r, [
                'id'             => Str::uuid(),
                'effective_date' => now()->toDateString(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]));
        }
        $this->command->info('  ✓ Exchange rates (USD/GBP/EUR vs NGN)');
    }
}
