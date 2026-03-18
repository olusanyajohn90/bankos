<?php

namespace Database\Seeders;

use App\Models\GlAccount;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed GL accounts for all active tenants
        $tenants = Tenant::where('status', 'active')->get();
        foreach ($tenants as $tenant) {
            $this->seedForTenant($tenant);
        }
    }

    public static function seedForTenant(Tenant $tenant): void
    {
        // Standard Nigerian Chart of Accounts (simplified)
        $coa = [
            // Assets
            ['1000', 'Assets', 'asset', 1, null],
            ['1100', 'Cash and Bank Balances', 'asset', 2, '1000'],
            ['1110', 'Cash on Hand', 'asset', 3, '1100'],
            ['1120', 'Cash at Bank', 'asset', 3, '1100'],
            ['1130', 'Cash with CBN', 'asset', 3, '1100'],
            ['1200', 'Loans and Advances', 'asset', 2, '1000'],
            ['1210', 'Performing Loans', 'asset', 3, '1200'],
            ['1220', 'Non-Performing Loans', 'asset', 3, '1200'],
            ['1230', 'Staff Loans', 'asset', 3, '1200'],
            ['1300', 'Investments', 'asset', 2, '1000'],
            ['1310', 'Treasury Bills', 'asset', 3, '1300'],
            ['1320', 'Government Bonds', 'asset', 3, '1300'],
            ['1400', 'Fixed Assets', 'asset', 2, '1000'],
            ['1410', 'Furniture and Fittings', 'asset', 3, '1400'],
            ['1420', 'Computer Equipment', 'asset', 3, '1400'],
            ['1430', 'Motor Vehicles', 'asset', 3, '1400'],
            ['1440', 'Leasehold Improvements', 'asset', 3, '1400'],
            ['1500', 'Other Assets', 'asset', 2, '1000'],
            ['1510', 'Prepayments', 'asset', 3, '1500'],
            ['1520', 'Accrued Interest Receivable', 'asset', 3, '1500'],

            // Liabilities
            ['2000', 'Liabilities', 'liability', 1, null],
            ['2100', 'Customer Deposits', 'liability', 2, '2000'],
            ['2110', 'Savings Deposits', 'liability', 3, '2100'],
            ['2120', 'Current Account Deposits', 'liability', 3, '2100'],
            ['2130', 'Fixed Deposits', 'liability', 3, '2100'],
            ['2140', 'Wallet Balances', 'liability', 3, '2100'],
            ['2200', 'Borrowings', 'liability', 2, '2000'],
            ['2210', 'Interbank Borrowings', 'liability', 3, '2200'],
            ['2300', 'Other Liabilities', 'liability', 2, '2000'],
            ['2310', 'Accrued Interest Payable', 'liability', 3, '2300'],
            ['2320', 'Tax Payable', 'liability', 3, '2300'],
            ['2330', 'Unearned Fees', 'liability', 3, '2300'],
            ['2340', 'Loan Loss Provision', 'liability', 3, '2300'],

            // Equity
            ['3000', 'Equity', 'equity', 1, null],
            ['3100', 'Share Capital', 'equity', 2, '3000'],
            ['3200', 'Retained Earnings', 'equity', 2, '3000'],
            ['3300', 'Statutory Reserves', 'equity', 2, '3000'],

            // Income
            ['4000', 'Income', 'income', 1, null],
            ['4100', 'Interest Income', 'income', 2, '4000'],
            ['4110', 'Interest on Loans', 'income', 3, '4100'],
            ['4120', 'Interest on Investments', 'income', 3, '4100'],
            ['4200', 'Fee and Commission Income', 'income', 2, '4000'],
            ['4210', 'Loan Processing Fees', 'income', 3, '4200'],
            ['4220', 'Account Maintenance Fees', 'income', 3, '4200'],
            ['4230', 'Transfer Fees', 'income', 3, '4200'],
            ['4240', 'Insurance Commission', 'income', 3, '4200'],
            ['4300', 'Other Income', 'income', 2, '4000'],
            ['4310', 'Penalty Income', 'income', 3, '4300'],
            ['4320', 'FX Gain', 'income', 3, '4300'],

            // Expenses
            ['5000', 'Expenses', 'expense', 1, null],
            ['5100', 'Interest Expense', 'expense', 2, '5000'],
            ['5110', 'Interest on Deposits', 'expense', 3, '5100'],
            ['5120', 'Interest on Borrowings', 'expense', 3, '5100'],
            ['5200', 'Operating Expenses', 'expense', 2, '5000'],
            ['5210', 'Staff Costs', 'expense', 3, '5200'],
            ['5220', 'Rent and Occupancy', 'expense', 3, '5200'],
            ['5230', 'Technology Costs', 'expense', 3, '5200'],
            ['5240', 'Marketing and Advertising', 'expense', 3, '5200'],
            ['5250', 'Legal and Professional Fees', 'expense', 3, '5200'],
            ['5260', 'Insurance Expense', 'expense', 3, '5200'],
            ['5270', 'Depreciation', 'expense', 3, '5200'],
            ['5300', 'Provision for Credit Losses', 'expense', 2, '5000'],
            ['5310', 'Loan Impairment Charge', 'expense', 3, '5300'],
            ['5400', 'Other Expenses', 'expense', 2, '5000'],
            ['5410', 'FX Loss', 'expense', 3, '5400'],
            ['5420', 'Bank Charges', 'expense', 3, '5400'],
        ];

        // Map account_number to id for parent lookups
        $idMap = [];

        foreach ($coa as $item) {
            [$accountNumber, $name, $category, $level, $parentNumber] = $item;

            $parentId = $parentNumber ? ($idMap[$parentNumber] ?? null) : null;

            $gl = GlAccount::firstOrCreate(
                ['tenant_id' => $tenant->id, 'account_number' => $accountNumber],
                [
                    'name'      => $name,
                    'category'  => $category,
                    'level'     => $level,
                    'parent_id' => $parentId,
                ]
            );

            $idMap[$accountNumber] = $gl->id;
        }
    }
}
