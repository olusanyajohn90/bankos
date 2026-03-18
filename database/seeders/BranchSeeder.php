<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\SavingsProduct;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('short_name', 'demo')->firstOrFail();
        $tid    = $tenant->id;

        // ─── Branch definitions across 5 states ─────────────────────────────
        $branchDefs = [
            [
                'name'              => 'Head Office — Lagos Island',
                'branch_code'       => 'HO001',
                'code'              => 'HO001',
                'sort_code'         => '000-001',
                'routing_number'    => 'DMB0000001',
                'phone'             => '+2341-234-5678',
                'email'             => 'ho@demomfb.com',
                'street'            => '14 Marina Street, Lagos Island',
                'city'              => 'Lagos Island',
                'local_government'  => 'Lagos Island',
                'state'             => 'Lagos',
                'manager_email'     => 'manager.ho@demomfb.com',
                'manager_name'      => 'Adewale Balogun',
            ],
            [
                'name'              => 'Ikeja Branch',
                'branch_code'       => 'IKJ001',
                'code'              => 'IKJ001',
                'sort_code'         => '000-002',
                'routing_number'    => 'DMB0000002',
                'phone'             => '+2341-234-9012',
                'email'             => 'ikeja@demomfb.com',
                'street'            => '5 Allen Avenue, Ikeja',
                'city'              => 'Ikeja',
                'local_government'  => 'Ikeja',
                'state'             => 'Lagos',
                'manager_email'     => 'manager.ikeja@demomfb.com',
                'manager_name'      => 'Chidinma Okonkwo',
            ],
            [
                'name'              => 'Abuja — Wuse Branch',
                'branch_code'       => 'ABJ001',
                'code'              => 'ABJ001',
                'sort_code'         => '000-003',
                'routing_number'    => 'DMB0000003',
                'phone'             => '+2349-012-3456',
                'email'             => 'abuja@demomfb.com',
                'street'            => '22 Wuse Zone 5, Abuja',
                'city'              => 'Abuja',
                'local_government'  => 'Municipal Area Council',
                'state'             => 'FCT',
                'manager_email'     => 'manager.abuja@demomfb.com',
                'manager_name'      => 'Musa Aliyu Ibrahim',
            ],
            [
                'name'              => 'Kano — Sabon Gari Branch',
                'branch_code'       => 'KAN001',
                'code'              => 'KAN001',
                'sort_code'         => '000-004',
                'routing_number'    => 'DMB0000004',
                'phone'             => '+23464-123-456',
                'email'             => 'kano@demomfb.com',
                'street'            => '8 Bompai Road, Sabon Gari, Kano',
                'city'              => 'Kano',
                'local_government'  => 'Fagge',
                'state'             => 'Kano',
                'manager_email'     => 'manager.kano@demomfb.com',
                'manager_name'      => 'Hauwa Suleiman Musa',
            ],
            [
                'name'              => 'Port Harcourt — GRA Branch',
                'branch_code'       => 'PHC001',
                'code'              => 'PHC001',
                'sort_code'         => '000-005',
                'routing_number'    => 'DMB0000005',
                'phone'             => '+23484-456-789',
                'email'             => 'phc@demomfb.com',
                'street'            => '3 Aba Road, GRA Phase II, Port Harcourt',
                'city'              => 'Port Harcourt',
                'local_government'  => 'Port Harcourt City',
                'state'             => 'Rivers',
                'manager_email'     => 'manager.phc@demomfb.com',
                'manager_name'      => 'Tonye Briggs-Williams',
            ],
            [
                'name'              => 'Enugu — Independence Layout Branch',
                'branch_code'       => 'ENG001',
                'code'              => 'ENG001',
                'sort_code'         => '000-006',
                'routing_number'    => 'DMB0000006',
                'phone'             => '+23442-789-012',
                'email'             => 'enugu@demomfb.com',
                'street'            => '15 Ogui Road, Independence Layout, Enugu',
                'city'              => 'Enugu',
                'local_government'  => 'Enugu North',
                'state'             => 'Enugu',
                'manager_email'     => 'manager.enugu@demomfb.com',
                'manager_name'      => 'Obinna Ezeh',
            ],
            [
                'name'              => 'Ibadan — Bodija Branch',
                'branch_code'       => 'IBD001',
                'code'              => 'IBD001',
                'sort_code'         => '000-007',
                'routing_number'    => 'DMB0000007',
                'phone'             => '+23422-345-678',
                'email'             => 'ibadan@demomfb.com',
                'street'            => '22 Bodija Market Road, Ibadan',
                'city'              => 'Ibadan',
                'local_government'  => 'Ibadan North',
                'state'             => 'Oyo',
                'manager_email'     => 'manager.ibadan@demomfb.com',
                'manager_name'      => 'Olusegun Adeyemi',
            ],
        ];

        $createdBranches = [];
        $branchManagers  = [];

        foreach ($branchDefs as $def) {
            // Create branch manager user
            $manager = User::firstOrCreate(
                ['email' => $def['manager_email']],
                [
                    'name'              => $def['manager_name'],
                    'password'          => Hash::make('BankOS@2026!'),
                    'email_verified_at' => now(),
                    'tenant_id'         => $tid,
                    'status'            => 'active',
                ]
            );
            if (!$manager->hasRole('branch_manager')) {
                $manager->assignRole('branch_manager');
            }

            // Create branch
            $branch = Branch::firstOrCreate(
                ['tenant_id' => $tid, 'branch_code' => $def['branch_code']],
                [
                    'name'             => $def['name'],
                    'code'             => $def['code'],
                    'sort_code'        => $def['sort_code'],
                    'routing_number'   => $def['routing_number'],
                    'phone'            => $def['phone'],
                    'email'            => $def['email'],
                    'street'           => $def['street'],
                    'city'             => $def['city'],
                    'local_government' => $def['local_government'],
                    'state'            => $def['state'],
                    'manager_id'       => $manager->id,
                    'status'           => 'active',
                ]
            );

            $createdBranches[$def['branch_code']] = $branch;
            $branchManagers[$def['branch_code']]  = $manager;
        }

        // ─── Assign existing demo users to Head Office branch ─────────────
        User::where('tenant_id', $tid)
            ->whereNull('branch_id')
            ->whereNotIn('email', array_column($branchDefs, 'manager_email'))
            ->update(['branch_id' => $createdBranches['HO001']->id]);

        // ─── Per-branch staff: loan officers & tellers ────────────────────
        $staffDefs = [
            'IKJ001' => [
                ['name' => 'Blessing Eze',        'email' => 'lo.ikeja@demomfb.com',   'role' => 'loan_officer'],
                ['name' => 'Segun Adeoye',         'email' => 'teller.ikeja@demomfb.com','role' => 'teller'],
            ],
            'ABJ001' => [
                ['name' => 'Amina Garba',          'email' => 'lo.abuja@demomfb.com',   'role' => 'loan_officer'],
                ['name' => 'Emeka Nwachukwu',      'email' => 'teller.abuja@demomfb.com','role' => 'teller'],
            ],
            'KAN001' => [
                ['name' => 'Bashir Usman',         'email' => 'lo.kano@demomfb.com',    'role' => 'loan_officer'],
                ['name' => 'Aisha Mohammed',       'email' => 'teller.kano@demomfb.com','role' => 'teller'],
            ],
            'PHC001' => [
                ['name' => 'Chukwuemeka Obi',      'email' => 'lo.phc@demomfb.com',     'role' => 'loan_officer'],
                ['name' => 'Ifunanya Amadi',       'email' => 'teller.phc@demomfb.com', 'role' => 'teller'],
            ],
            'ENG001' => [
                ['name' => 'Ikenna Okafor',        'email' => 'lo.enugu@demomfb.com',   'role' => 'loan_officer'],
                ['name' => 'Ngozi Ugwu',           'email' => 'teller.enugu@demomfb.com','role' => 'teller'],
            ],
            'IBD001' => [
                ['name' => 'Adebimpe Ogunleye',    'email' => 'lo.ibadan@demomfb.com',  'role' => 'loan_officer'],
                ['name' => 'Taiwo Salami',         'email' => 'teller.ibadan@demomfb.com','role' => 'teller'],
            ],
        ];

        foreach ($staffDefs as $branchCode => $staff) {
            $branch = $createdBranches[$branchCode] ?? null;
            if (!$branch) continue;
            foreach ($staff as $s) {
                $user = User::firstOrCreate(
                    ['email' => $s['email']],
                    [
                        'name'              => $s['name'],
                        'password'          => Hash::make('BankOS@2026!'),
                        'email_verified_at' => now(),
                        'tenant_id'         => $tid,
                        'branch_id'         => $branch->id,
                        'status'            => 'active',
                    ]
                );
                if (!$user->hasRole($s['role'])) {
                    $user->assignRole($s['role']);
                }
            }
        }

        // ─── Per-branch customers & loan/savings activity ──────────────────
        $microLoan = LoanProduct::where('tenant_id', $tid)->first();
        $savProd   = SavingsProduct::where('tenant_id', $tid)->first();

        $branchCustomers = [
            'KAN001' => [
                ['Halima',  'Yusuf',    '+23481-100-2001', 'F', 75000,  'active'],
                ['Sani',    'Garba',    '+23481-100-2002', 'M', 120000, 'active'],
                ['Zainab',  'Ahmed',    '+23481-100-2003', 'F', 50000,  'overdue'],
                ['Ibrahim', 'Bello',    '+23481-100-2004', 'M', 200000, 'active'],
            ],
            'PHC001' => [
                ['Tamara',  'Princewill', '+23481-100-3001', 'F', 180000, 'active'],
                ['Chidi',   'Nwosu',      '+23481-100-3002', 'M', 95000,  'active'],
                ['Eucheria','Dike',       '+23481-100-3003', 'F', 60000,  'overdue'],
                ['Boma',    'Hart',       '+23481-100-3004', 'M', 300000, 'active'],
            ],
            'ENG001' => [
                ['Adaeze',  'Obi',      '+23481-100-4001', 'F', 85000,  'active'],
                ['Chukwudi','Eze',      '+23481-100-4002', 'M', 150000, 'active'],
                ['Nkechi',  'Ogbu',     '+23481-100-4003', 'F', 40000,  'closed'],
                ['Emeka',   'Nweze',    '+23481-100-4004', 'M', 250000, 'active'],
            ],
            'IBD001' => [
                ['Folake',  'Adesanya', '+23481-100-5001', 'F', 70000,  'active'],
                ['Kunle',   'Bakare',   '+23481-100-5002', 'M', 130000, 'active'],
                ['Yetunde', 'Raji',     '+23481-100-5003', 'F', 55000,  'overdue'],
                ['Seun',    'Omotosho', '+23481-100-5004', 'M', 95000,  'active'],
            ],
        ];

        foreach ($branchCustomers as $branchCode => $custList) {
            $branch     = $createdBranches[$branchCode] ?? null;
            $loanOfficer = User::where('email', 'lo.' . strtolower(str_replace(
                ['KAN001', 'PHC001', 'ENG001', 'IBD001'],
                ['kano',   'phc',    'enugu',  'ibadan'],
                $branchCode
            ) . '@demomfb.com'))->first();

            if (!$branch || !$microLoan || !$savProd) continue;

            foreach ($custList as [$first, $last, $phone, $gender, $loanAmt, $loanStatus]) {
                // Skip if phone already exists
                if (\App\Models\Customer::where('phone', $phone)->exists()) continue;

                $customer = \App\Models\Customer::create([
                    'tenant_id'       => $tid,
                    'customer_number' => 'CST' . strtoupper(Str::random(7)),
                    'first_name'      => $first,
                    'last_name'       => $last,
                    'phone'           => $phone,
                    'gender'          => $gender === 'F' ? 'female' : 'male',
                    'date_of_birth'   => now()->subYears(rand(28, 55))->subDays(rand(0, 364))->toDateString(),
                    'address'         => ['street' => $branch->street, 'city' => $branch->city, 'state' => $branch->state],
                    'kyc_status'      => 'approved',
                    'kyc_tier'        => 'level_2',
                    'status'          => 'active',
                ]);

                if (!$savProd) continue;

                // Savings account
                $accNum = $tenant->account_prefix . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
                $savBal = rand(5000, 80000);
                $account = Account::create([
                    'tenant_id'         => $tid,
                    'customer_id'       => $customer->id,
                    'savings_product_id'=> $savProd->id,
                    'account_number'    => $accNum,
                    'account_name'      => $customer->first_name . ' ' . $customer->last_name,
                    'type'              => 'savings',
                    'currency'          => 'NGN',
                    'ledger_balance'    => $savBal,
                    'available_balance' => $savBal,
                    'status'            => 'active',
                ]);

                // Opening deposit
                Transaction::create([
                    'tenant_id'    => $tid,
                    'account_id'   => $account->id,
                    'reference'    => 'DEP-' . strtoupper(Str::random(10)),
                    'type'         => 'deposit',
                    'amount'       => $savBal,
                    'currency'     => 'NGN',
                    'description'  => 'Initial deposit',
                    'status'       => 'success',
                    'created_at'   => now()->subMonths(rand(2, 12)),
                ]);

                if (!$microLoan) continue;

                // Loan
                $tenor          = rand(3, 12);
                $rate           = $microLoan->interest_rate / 100;
                $totalInterest  = $loanAmt * $rate * $tenor;
                $totalPayable   = $loanAmt + $totalInterest;
                $monthly        = round($totalPayable / $tenor, 2);
                $disbursedAt    = now()->subMonths(rand(1, 6));
                $paidMonths     = match($loanStatus) {
                    'active'  => rand(1, max(1, $tenor - 1)),
                    'overdue' => rand(0, 1),
                    'closed'  => $tenor,
                    default   => 0,
                };
                $amountPaid  = $monthly * $paidMonths;
                $outstanding = max(0, round($totalPayable - $amountPaid, 2));

                $loan = Loan::create([
                    'tenant_id'             => $tid,
                    'customer_id'           => $customer->id,
                    'product_id'            => $microLoan->id,
                    'account_id'            => $account->id,
                    'loan_number'           => 'LN' . strtoupper(Str::random(8)),
                    'principal_amount'      => $loanAmt,
                    'outstanding_balance'   => $outstanding,
                    'interest_rate'         => $microLoan->interest_rate,
                    'interest_method'       => $microLoan->interest_method,
                    'amortization'          => 'equal_installment',
                    'tenure_days'           => $tenor * 30,
                    'repayment_frequency'   => 'monthly',
                    'purpose'               => 'Business working capital',
                    'status'                => $loanStatus,
                    'disbursed_at'          => $disbursedAt,
                    'expected_maturity_date'=> (clone $disbursedAt)->addMonths($tenor),
                ]);

                // Disbursement transaction
                Transaction::create([
                    'tenant_id'   => $tid,
                    'account_id'  => $account->id,
                    'reference'   => 'DSB-' . strtoupper(Str::random(10)),
                    'type'        => 'disbursement',
                    'amount'      => $loanAmt,
                    'currency'    => 'NGN',
                    'description' => 'Loan disbursement - ' . $loan->loan_number,
                    'status'      => 'success',
                    'created_at'  => $disbursedAt,
                ]);

                // Repayment transactions
                for ($m = 0; $m < $paidMonths; $m++) {
                    Transaction::create([
                        'tenant_id'   => $tid,
                        'account_id'  => $account->id,
                        'reference'   => 'RPY-' . strtoupper(Str::random(10)),
                        'type'        => 'repayment',
                        'amount'      => $monthly,
                        'currency'    => 'NGN',
                        'description' => 'Loan repayment #' . ($m + 1) . ' - ' . $loan->loan_number,
                        'status'      => 'success',
                        'created_at'  => $disbursedAt->copy()->addMonths($m + 1),
                    ]);
                }
            }
        }
    }
}
