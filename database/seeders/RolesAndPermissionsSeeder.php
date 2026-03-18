<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Customers
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',

            // KYC
            'kyc.view', 'kyc.approve', 'kyc.reject',

            // Accounts
            'accounts.view', 'accounts.create', 'accounts.edit', 'accounts.close',

            // Savings Products
            'savings_products.view', 'savings_products.create', 'savings_products.edit',

            // Transactions
            'transactions.view', 'transactions.create', 'transactions.reverse',

            // Loans
            'loans.view', 'loans.create', 'loans.approve_l1', 'loans.approve_l2', 'loans.approve_l3',
            'loans.disburse', 'loans.restructure', 'loans.write_off',

            // Loan Products
            'loan_products.view', 'loan_products.create', 'loan_products.edit',

            // Workflows
            'workflows.view', 'workflows.manage',

            // Reports
            'reports.view', 'reports.export',

            // Branches
            'branches.view', 'branches.create', 'branches.edit',

            // GL Accounts
            'gl.view', 'gl.create', 'gl.edit',

            // Users
            'users.view', 'users.create', 'users.edit', 'users.approve',

            // Tenants
            'tenants.view', 'tenants.create', 'tenants.edit',

            // Exchange Rates
            'exchange_rates.view', 'exchange_rates.refresh',

            // Audit Logs
            'audit_logs.view', 'audit_logs.export',

            // Scheduled Jobs
            'scheduled_jobs.view', 'scheduled_jobs.run',

            // Settings
            'settings.view', 'settings.edit',

            // Agent Banking
            'agents.view', 'agents.create', 'agents.manage',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin — has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Compliance Officer
        $complianceOfficer = Role::firstOrCreate(['name' => 'compliance_officer']);
        $complianceOfficer->givePermissionTo([
            'dashboard.view',
            'customers.view', 'customers.edit',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'accounts.view',
            'transactions.view',
            'loans.view', 'loans.approve_l2', 'loans.approve_l3', 'loans.restructure', 'loans.write_off',
            'workflows.view', 'workflows.manage',
            'reports.view', 'reports.export',
            'gl.view',
            'users.view',
            'audit_logs.view', 'audit_logs.export',
        ]);

        // Tenant Admin (Bank Admin)
        $tenantAdmin = Role::firstOrCreate(['name' => 'tenant_admin']);
        $tenantAdmin->givePermissionTo([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.edit',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'savings_products.view', 'savings_products.create', 'savings_products.edit',
            'transactions.view', 'transactions.create',
            'loans.view', 'loans.create', 'loans.approve_l1',
            'loan_products.view', 'loan_products.create', 'loan_products.edit',
            'workflows.view', 'workflows.manage',
            'reports.view', 'reports.export',
            'branches.view', 'branches.create', 'branches.edit',
            'gl.view', 'gl.create', 'gl.edit',
            'users.view', 'users.create', 'users.edit', 'users.approve',
            'exchange_rates.view',
            'audit_logs.view',
            'scheduled_jobs.view', 'scheduled_jobs.run',
            'settings.view', 'settings.edit',
            'agents.view', 'agents.create', 'agents.manage',
        ]);

        // Branch Manager
        $branchManager = Role::firstOrCreate(['name' => 'branch_manager']);
        $branchManager->givePermissionTo([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.edit',
            'kyc.view', 'kyc.approve',
            'accounts.view', 'accounts.create', 'accounts.edit',
            'transactions.view', 'transactions.create',
            'loans.view', 'loans.create', 'loans.approve_l1',
            'loan_products.view',
            'workflows.view', 'workflows.manage',
            'reports.view', 'reports.export',
            'branches.view',
            'users.view',
            'agents.view', 'agents.manage',
        ]);

        // Loan Officer
        $loanOfficer = Role::firstOrCreate(['name' => 'loan_officer']);
        $loanOfficer->givePermissionTo([
            'dashboard.view',
            'customers.view', 'customers.create', 'customers.edit',
            'kyc.view',
            'accounts.view',
            'transactions.view',
            'loans.view', 'loans.create', 'loans.approve_l1', 'loans.disburse',
            'loan_products.view',
            'workflows.view',
            'reports.view',
        ]);

        // Teller
        $teller = Role::firstOrCreate(['name' => 'teller']);
        $teller->givePermissionTo([
            'dashboard.view',
            'customers.view',
            'accounts.view',
            'transactions.view', 'transactions.create',
        ]);

        // Agent
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $agent->givePermissionTo([
            'dashboard.view',
            'customers.view', 'customers.create',
            'accounts.view',
            'transactions.view', 'transactions.create',
            'loans.view',
        ]);

        // Auditor (read-only)
        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'dashboard.view',
            'customers.view',
            'accounts.view',
            'transactions.view',
            'loans.view',
            'reports.view', 'reports.export',
            'gl.view',
            'audit_logs.view', 'audit_logs.export',
        ]);
    }
}
