<?php

namespace Database\Seeders;

use App\Models\ExchangeRate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Super Admin user (no tenant)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@bankos.io'],
            [
                'name'               => 'Super Admin',
                'password'           => Hash::make('BankOS@2026!'),
                'email_verified_at'  => now(),
                'status'             => 'active',
            ]
        );
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // 3. Demo tenant — UUID is pinned to match DemoDataSeeder's hardcoded tenantId
        $demoTenant = Tenant::firstOrCreate(
            ['id' => 'a14a96f2-006d-4232-973e-9683ef578737'],
            [
                'short_name'          => 'demo',
                'name'                => 'Demo Microfinance Bank',
                'type'                => 'bank',
                'account_prefix'      => '100',
                'primary_currency'    => 'NGN',
                'supported_currencies' => ['NGN', 'USD', 'GBP'],
                'domain'              => 'demo.bankos.test',
                'cbn_license_number'  => 'CBN/MFB/2024/001',
                'contact_email'       => 'admin@demomfb.com',
                'contact_phone'       => '+2348012345678',
                'status'              => 'active',
            ]
        );

        // 4. Compliance Officer
        $complianceOfficer = User::firstOrCreate(
            ['email' => 'compliance@demomfb.com'],
            [
                'name'               => 'Compliance Officer',
                'password'           => Hash::make('BankOS@2026!'),
                'email_verified_at'  => now(),
                'tenant_id'          => $demoTenant->id,
                'status'             => 'active',
            ]
        );
        if (!$complianceOfficer->hasRole('compliance_officer')) {
            $complianceOfficer->assignRole('compliance_officer');
        }

        // 5. Tenant Admin
        $tenantAdmin = User::firstOrCreate(
            ['email' => 'admin@demomfb.com'],
            [
                'name'                => 'Bank Admin',
                'password'            => Hash::make('BankOS@2026!'),
                'email_verified_at'   => now(),
                'tenant_id'           => $demoTenant->id,
                'must_change_password' => false,
                'status'              => 'active',
            ]
        );
        if (!$tenantAdmin->hasRole('tenant_admin')) {
            $tenantAdmin->assignRole('tenant_admin');
        }

        // 6. Loan Officer
        $loanOfficer = User::firstOrCreate(
            ['email' => 'loans@demomfb.com'],
            [
                'name'               => 'Loan Officer',
                'password'           => Hash::make('BankOS@2026!'),
                'email_verified_at'  => now(),
                'tenant_id'          => $demoTenant->id,
                'status'             => 'active',
            ]
        );
        if (!$loanOfficer->hasRole('loan_officer')) {
            $loanOfficer->assignRole('loan_officer');
        }

        // 7. GL Chart of Accounts
        $this->call(ChartOfAccountsSeeder::class);

        // 8. Exchange rates
        $rates = [
            ['NGN/USD', 1550.00, 1560.00, 1555.00],
            ['NGN/GBP', 1950.00, 1970.00, 1960.00],
            ['NGN/EUR', 1680.00, 1700.00, 1690.00],
            ['NGN/CAD', 1100.00, 1115.00, 1107.50],
            ['NGN/CNY', 212.00,  216.00,  214.00],
        ];
        foreach ($rates as [$pair, $buy, $sell, $mid]) {
            ExchangeRate::firstOrCreate(
                ['pair' => $pair, 'effective_date' => now()->toDateString()],
                ['buy_rate' => $buy, 'sell_rate' => $sell, 'mid_rate' => $mid]
            );
        }

        // 9. Demo customers, branches, products, loans, transactions
        $this->call(DemoDataSeeder::class);

        // 10. Field agents with float transactions and visits
        $this->call(AgentSeeder::class);

        // 11. Multi-state branches with managers, staff, customers & activity
        $this->call(BranchSeeder::class);

        // 12. System-wide reference data (KPIs, pay components, payroll runs, training)
        $this->call(KpiDefinitionSeeder::class);
        $this->call(SystemDataSeeder::class);
        $this->call(NotificationTemplatesSeeder::class);
        $this->call(DefaultTransactionLimitsSeeder::class);

        // 13. Assign loan officers to loans & accounts (must run after DemoData + Branch)
        $this->call(LoanOfficerAttributionSeeder::class);

        // 14. Fixed assets (categories + assets with depreciation)
        $this->call(FixedAssetSeeder::class);

        // 15. Transfer providers + bank list
        $this->call(TransferProviderSeeder::class);

        // 16. Group lending (centres, groups, members)
        $this->call(GroupLendingSeeder::class);

        // 17. HR module (org structure, leave types, staff profiles)
        $this->call(HRSeeder::class);

        // 18. Banking products (fixed deposits, standing orders, cheque books)
        $this->call(BankingProductsSeeder::class);

        // 19. Document management & communications
        $this->call(DocumentsCommsSeeder::class);

        // 20. AML sanctions list
        $this->call(SanctionsListSeeder::class);

        // 21. Workspace (chat, support tickets, CRM, visitors)
        $this->call(WorkspaceSupportSeeder::class);

        // 22. Cooperative module (dividends, contributions)
        $this->call(CooperativeSeeder::class);

        // 23. Chat enhancements (channels, threads, polls, tasks, calls, canvas, etc.)
        $this->call(ChatEnhancementsSeeder::class);

        // 24. Calendar (calendars, events, attendees, reminders)
        $this->call(CalendarSeeder::class);
    }
}
