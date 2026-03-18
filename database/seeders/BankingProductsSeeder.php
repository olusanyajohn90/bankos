<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankingProductsSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId  = DB::table('tenants')->value('id');
        $customers = DB::table('customers')->where('tenant_id', $tenantId)->get(['id', 'first_name', 'last_name', 'branch_id']);
        $accounts  = DB::table('accounts')->where('tenant_id', $tenantId)->get(['id', 'customer_id', 'account_number']);
        $adminId   = DB::table('users')->where('tenant_id', $tenantId)->value('id');

        if ($customers->isEmpty() || $accounts->isEmpty()) {
            $this->command->warn('No customers/accounts found — run DemoDataSeeder first.');
            return;
        }

        $this->seedFixedDepositProducts($tenantId);
        $this->seedFixedDeposits($tenantId, $customers, $accounts, $adminId);
        $this->seedChequeBooks($tenantId, $accounts, $adminId);
        $this->seedStandingOrders($tenantId, $accounts, $adminId);
        $this->seedOverdraftFacilities($tenantId, $accounts, $adminId);
        $this->seedPortalInvestmentProducts($tenantId);
        $this->seedCardTemplates($tenantId);

        $this->command->info('Banking products seeded successfully.');
    }

    // ─── Fixed Deposit Products ────────────────────────────────────────────────
    private function seedFixedDepositProducts(string $tenantId): void
    {
        $products = [
            ['name' => '3-Month Fixed Deposit',  'code' => 'FDP-90',  'min_tenure_days' => 90,  'max_tenure_days' => 90,  'interest_rate' => 8.5,  'min_amount' => 50_000,   'max_amount' => 50_000_000,  'interest_payment' => 'on_maturity'],
            ['name' => '6-Month Fixed Deposit',  'code' => 'FDP-180', 'min_tenure_days' => 180, 'max_tenure_days' => 180, 'interest_rate' => 10.0, 'min_amount' => 50_000,   'max_amount' => 50_000_000,  'interest_payment' => 'on_maturity'],
            ['name' => '12-Month Fixed Deposit', 'code' => 'FDP-365', 'min_tenure_days' => 365, 'max_tenure_days' => 365, 'interest_rate' => 12.5, 'min_amount' => 100_000,  'max_amount' => 100_000_000, 'interest_payment' => 'monthly'],
            ['name' => '24-Month Fixed Deposit', 'code' => 'FDP-730', 'min_tenure_days' => 730, 'max_tenure_days' => 730, 'interest_rate' => 14.0, 'min_amount' => 250_000,  'max_amount' => 100_000_000, 'interest_payment' => 'monthly'],
            ['name' => '30-Day Call Deposit',    'code' => 'FDP-30',  'min_tenure_days' => 30,  'max_tenure_days' => 30,  'interest_rate' => 6.0,  'min_amount' => 500_000,  'max_amount' => 500_000_000, 'interest_payment' => 'on_maturity'],
        ];

        foreach ($products as $p) {
            DB::table('fixed_deposit_products')->insert([
                'id'                       => (string) Str::uuid(),
                'tenant_id'                => $tenantId,
                'name'                     => $p['name'],
                'code'                     => $p['code'],
                'description'              => "Earn {$p['interest_rate']}% p.a. on a {$p['min_tenure_days']}-day fixed deposit.",
                'interest_rate'            => $p['interest_rate'],
                'interest_payment'         => $p['interest_payment'],
                'min_tenure_days'          => $p['min_tenure_days'],
                'max_tenure_days'          => $p['max_tenure_days'],
                'min_amount'               => $p['min_amount'],
                'max_amount'               => $p['max_amount'],
                'early_liquidation_penalty'=> 2.0,
                'allow_top_up'             => false,
                'allow_early_liquidation'  => true,
                'auto_rollover'            => false,
                'status'                   => 'active',
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        }
        $this->command->line('  Fixed deposit products: ' . count($products));
    }

    // ─── Fixed Deposits ────────────────────────────────────────────────────────
    private function seedFixedDeposits(string $tenantId, $customers, $accounts, ?string $adminId): void
    {
        $products = DB::table('fixed_deposit_products')->where('tenant_id', $tenantId)->get();
        if ($products->isEmpty()) return;

        $statuses = ['active', 'active', 'active', 'matured', 'liquidated'];
        $count    = 0;

        foreach ($customers->take(8) as $i => $customer) {
            $account   = $accounts->where('customer_id', $customer->id)->first() ?? $accounts->random();
            $product   = $products[$i % $products->count()];
            $principal = [50_000, 100_000, 200_000, 500_000, 1_000_000, 250_000, 750_000, 300_000][$i];
            $status    = $statuses[$i % count($statuses)];
            $startDate = now()->subMonths(rand(1, 10));
            $maturity  = (clone $startDate)->addDays($product->min_tenure_days);
            $interest  = round($principal * ($product->interest_rate / 100) * ($product->min_tenure_days / 365), 2);

            DB::table('fixed_deposits')->insert([
                'id'               => (string) Str::uuid(),
                'tenant_id'        => $tenantId,
                'customer_id'      => $customer->id,
                'source_account_id'=> $account->id,
                'product_id'       => $product->id,
                'fd_number'        => 'FD' . now()->format('Y') . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'principal_amount' => $principal,
                'interest_rate'    => $product->interest_rate,
                'tenure_days'      => $product->min_tenure_days,
                'expected_interest'=> $interest,
                'accrued_interest' => $status === 'matured' ? $interest : round($interest * 0.6, 2),
                'paid_interest'    => $status === 'matured' ? $interest : 0,
                'start_date'       => $startDate->toDateString(),
                'maturity_date'    => $maturity->toDateString(),
                'status'           => $status,
                'auto_rollover'    => $i % 3 === 0,
                'liquidated_at'    => $status === 'liquidated' ? now()->subDays(rand(5, 30)) : null,
                'liquidation_amount'=> $status === 'liquidated' ? $principal + round($interest * 0.7, 2) : null,
                'penalty_amount'   => $status === 'liquidated' ? round($principal * 0.02, 2) : null,
                'liquidation_reason'=> $status === 'liquidated' ? 'Customer requested early liquidation' : null,
                'created_by'       => $adminId,
                'branch_id'        => $customer->branch_id,
                'created_at'       => $startDate,
                'updated_at'       => now(),
            ]);
            $count++;
        }
        $this->command->line("  Fixed deposits: {$count}");
    }

    // ─── Cheque Books ──────────────────────────────────────────────────────────
    private function seedChequeBooks(string $tenantId, $accounts, ?string $adminId): void
    {
        $statuses = ['active', 'active', 'exhausted', 'cancelled'];
        $count    = 0;

        foreach ($accounts->take(6) as $i => $account) {
            $issued  = now()->subMonths(rand(2, 12));
            $leaves  = 25;
            $used    = rand(0, $leaves);
            $status  = $statuses[$i % count($statuses)];

            DB::table('cheque_books')->insert([
                'id'           => (string) Str::uuid(),
                'tenant_id'    => $tenantId,
                'account_id'   => $account->id,
                'series_start' => str_pad($i * 25 + 1, 6, '0', STR_PAD_LEFT),
                'series_end'   => str_pad($i * 25 + $leaves, 6, '0', STR_PAD_LEFT),
                'leaves'       => $leaves,
                'leaves_used'  => $used,
                'issued_date'  => $issued->toDateString(),
                'status'       => $status,
                'issued_by'    => $adminId,
                'created_at'   => $issued,
                'updated_at'   => now(),
            ]);
            $count++;
        }
        $this->command->line("  Cheque books: {$count}");
    }

    // ─── Standing Orders ───────────────────────────────────────────────────────
    private function seedStandingOrders(string $tenantId, $accounts, ?string $adminId): void
    {
        $rows = [
            ['beneficiary_name' => 'Aisha Properties Ltd',   'beneficiary_account_number' => '2034567890', 'beneficiary_bank_code' => '011', 'amount' => 150_000, 'narration' => 'Monthly Rent',      'frequency' => 'monthly',   'transfer_type' => 'external'],
            ['beneficiary_name' => 'Quick Micro Finance',     'beneficiary_account_number' => '3012345678', 'beneficiary_bank_code' => '044', 'amount' => 25_000,  'narration' => 'Loan Repayment',    'frequency' => 'monthly',   'transfer_type' => 'external'],
            ['beneficiary_name' => 'EKEDC Electricity',       'beneficiary_account_number' => '1098765432', 'beneficiary_bank_code' => '058', 'amount' => 15_000,  'narration' => 'Electricity Bill',  'frequency' => 'monthly',   'transfer_type' => 'external'],
            ['beneficiary_name' => 'Bright Future School',    'beneficiary_account_number' => '2056789012', 'beneficiary_bank_code' => '033', 'amount' => 75_000,  'narration' => 'School Fees',       'frequency' => 'quarterly', 'transfer_type' => 'external'],
            ['beneficiary_name' => 'Savings Pocket Transfer', 'beneficiary_account_number' => '1034567891', 'beneficiary_bank_code' => '000', 'amount' => 10_000,  'narration' => 'Weekly Savings',    'frequency' => 'weekly',    'transfer_type' => 'internal'],
        ];

        $statuses = ['active', 'active', 'paused', 'active', 'active'];
        $count    = 0;

        foreach ($rows as $i => $row) {
            $account   = $accounts[$i % $accounts->count()];
            $startDate = now()->subMonths(rand(1, 6));

            DB::table('standing_orders')->insert([
                'id'                        => (string) Str::uuid(),
                'tenant_id'                 => $tenantId,
                'source_account_id'         => $account->id,
                'beneficiary_account_number'=> $row['beneficiary_account_number'],
                'beneficiary_bank_code'     => $row['beneficiary_bank_code'],
                'beneficiary_name'          => $row['beneficiary_name'],
                'internal_dest_account_id'  => null,
                'transfer_type'             => $row['transfer_type'],
                'amount'                    => $row['amount'],
                'narration'                 => $row['narration'],
                'frequency'                 => $row['frequency'],
                'start_date'                => $startDate->toDateString(),
                'end_date'                  => null,
                'next_run_date'             => now()->addDays(rand(1, 30))->toDateString(),
                'max_runs'                  => null,
                'runs_completed'            => rand(1, 12),
                'last_run_at'               => now()->subDays(rand(5, 35)),
                'status'                    => $statuses[$i],
                'last_failure_reason'       => null,
                'created_by'                => $adminId,
                'created_at'                => $startDate,
                'updated_at'                => now(),
            ]);
            $count++;
        }
        $this->command->line("  Standing orders: {$count}");
    }

    // ─── Overdraft Facilities ──────────────────────────────────────────────────
    private function seedOverdraftFacilities(string $tenantId, $accounts, ?string $adminId): void
    {
        $limits    = [100_000, 250_000, 500_000, 1_000_000];
        $usedPct   = [0, 0.3, 0.7, 0.1];
        $statuses  = ['active', 'active', 'suspended', 'active'];
        $rates     = [24.0, 22.0, 20.0, 18.0];
        $count     = 0;

        foreach ($accounts->take(4) as $i => $account) {
            $limit    = $limits[$i];
            $used     = round($limit * $usedPct[$i], 2);
            $approved = now()->subMonths(rand(1, 6));

            DB::table('overdraft_facilities')->insert([
                'id'           => (string) Str::uuid(),
                'tenant_id'    => $tenantId,
                'account_id'   => $account->id,
                'limit_amount' => $limit,
                'used_amount'  => $used,
                'interest_rate'=> $rates[$i],
                'accrued_interest' => round($used * ($rates[$i] / 100) * (30 / 365), 2),
                'approved_date'=> $approved->toDateString(),
                'expiry_date'  => $approved->copy()->addYear()->toDateString(),
                'status'       => $statuses[$i],
                'approved_by'  => $adminId,
                'notes'        => 'Approved based on account history and income verification.',
                'created_at'   => $approved,
                'updated_at'   => now(),
            ]);
            $count++;
        }
        $this->command->line("  Overdraft facilities: {$count}");
    }

    // ─── Portal Investment Products ────────────────────────────────────────────
    private function seedPortalInvestmentProducts(string $tenantId): void
    {
        $products = [
            ['name' => '3-Month Treasury Note',   'duration_days' => 90,  'interest_rate' => 9.5,  'min_amount' => 50_000,  'max_amount' => 10_000_000],
            ['name' => '6-Month Fixed Return',     'duration_days' => 180, 'interest_rate' => 11.0, 'min_amount' => 100_000, 'max_amount' => 50_000_000],
            ['name' => '12-Month Premium Bond',    'duration_days' => 365, 'interest_rate' => 13.5, 'min_amount' => 250_000, 'max_amount' => 100_000_000],
            ['name' => '24-Month High Yield',      'duration_days' => 730, 'interest_rate' => 15.0, 'min_amount' => 500_000, 'max_amount' => 100_000_000],
        ];

        foreach ($products as $i => $p) {
            DB::table('portal_investment_products')->insert([
                'id'            => (string) Str::uuid(),
                'tenant_id'     => $tenantId,
                'name'          => $p['name'],
                'description'   => "Earn {$p['interest_rate']}% p.a. over {$p['duration_days']} days. Capital guaranteed.",
                'duration_days' => $p['duration_days'],
                'interest_rate' => $p['interest_rate'],
                'min_amount'    => $p['min_amount'],
                'max_amount'    => $p['max_amount'],
                'is_active'     => true,
                'sort_order'    => $i + 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
        $this->command->line('  Portal investment products: ' . count($products));
    }

    // ─── Card Templates (HR ID cards) ─────────────────────────────────────────
    private function seedCardTemplates(string $tenantId): void
    {
        $templates = [
            ['name' => 'Standard Staff ID',   'primary_color' => '#1e3a5f', 'secondary_color' => '#2563eb', 'text_color' => '#ffffff', 'background_color' => '#f0f4f8', 'is_default' => true],
            ['name' => 'Executive ID',         'primary_color' => '#7c3aed', 'secondary_color' => '#a78bfa', 'text_color' => '#ffffff', 'background_color' => '#1e1b4b', 'is_default' => false],
            ['name' => 'Security Staff ID',    'primary_color' => '#991b1b', 'secondary_color' => '#ef4444', 'text_color' => '#ffffff', 'background_color' => '#fef2f2', 'is_default' => false],
            ['name' => 'Contractor/Visitor ID','primary_color' => '#92400e', 'secondary_color' => '#f59e0b', 'text_color' => '#1f2937', 'background_color' => '#fffbeb', 'is_default' => false],
        ];

        foreach ($templates as $t) {
            DB::table('card_templates')->insert([
                'id'                     => (string) Str::uuid(),
                'tenant_id'              => $tenantId,
                'name'                   => $t['name'],
                'primary_color'          => $t['primary_color'],
                'secondary_color'        => $t['secondary_color'],
                'text_color'             => $t['text_color'],
                'background_color'       => $t['background_color'],
                'logo_path'              => null,
                'background_image_path'  => null,
                'show_qr'                => true,
                'show_photo'             => true,
                'show_department'        => true,
                'show_grade'             => $t['is_default'],
                'show_blood_group'       => true,
                'show_emergency_contact' => false,
                'expiry_years'           => 2,
                'is_default'             => $t['is_default'],
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
        $this->command->line('  Card templates: ' . count($templates));
    }
}
