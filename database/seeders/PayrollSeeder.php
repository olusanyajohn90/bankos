<?php
namespace Database\Seeders;

use App\Models\PayComponent;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // System-level pay components (tenant_id = null for global reference)
        // In practice, when a tenant is created, these get copied to their tenant
        // For now, they serve as reference/template components

        $components = [
            // Earnings
            ['code' => 'BASIC',       'name' => 'Basic Salary',         'type' => 'earning',   'is_statutory' => false, 'is_taxable' => true,  'computation_type' => 'fixed',   'value' => null, 'formula_key' => null],
            ['code' => 'HOUSING',     'name' => 'Housing Allowance',    'type' => 'earning',   'is_statutory' => false, 'is_taxable' => true,  'computation_type' => 'fixed',   'value' => null, 'formula_key' => null],
            ['code' => 'TRANSPORT',   'name' => 'Transport Allowance',  'type' => 'earning',   'is_statutory' => false, 'is_taxable' => true,  'computation_type' => 'fixed',   'value' => null, 'formula_key' => null],
            ['code' => 'MEAL',        'name' => 'Meal Allowance',       'type' => 'earning',   'is_statutory' => false, 'is_taxable' => true,  'computation_type' => 'fixed',   'value' => null, 'formula_key' => null],
            // Statutory Deductions
            ['code' => 'PAYE',        'name' => 'PAYE (Income Tax)',         'type' => 'deduction', 'is_statutory' => true,  'is_taxable' => false, 'computation_type' => 'formula', 'value' => null, 'formula_key' => 'paye'],
            ['code' => 'PENSION_EE',  'name' => 'Employee Pension (8%)',     'type' => 'deduction', 'is_statutory' => true,  'is_taxable' => false, 'computation_type' => 'formula', 'value' => 8.00, 'formula_key' => 'pension_employee'],
            ['code' => 'PENSION_ER',  'name' => 'Employer Pension (10%)',    'type' => 'deduction', 'is_statutory' => true,  'is_taxable' => false, 'computation_type' => 'formula', 'value' => 10.00, 'formula_key' => 'pension_employer'],
            ['code' => 'NHF',         'name' => 'NHF (2.5%)',                'type' => 'deduction', 'is_statutory' => true,  'is_taxable' => false, 'computation_type' => 'formula', 'value' => 2.50, 'formula_key' => 'nhf'],
            ['code' => 'NSITF',       'name' => 'NSITF (1% Employer)',       'type' => 'deduction', 'is_statutory' => true,  'is_taxable' => false, 'computation_type' => 'formula', 'value' => 1.00, 'formula_key' => 'nsitf'],
        ];

        $this->command->info('Payroll seeder: System pay components are reference-only.');
        $this->command->info('Add tenant-specific components via the Payroll Setup interface.');
    }
}
