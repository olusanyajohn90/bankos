<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds comprehensive customer portal data for Zainab Aliyu (CUS-66304).
 * Safe to re-run — deletes Zainab's existing portal rows first.
 */
class ZainabPortalSeeder extends Seeder
{
    private string $customerId = '23ba8a59-5e5f-46b3-9291-3f2e651632f4';
    private string $tenantId   = 'a14a96f2-006d-4232-973e-9683ef578737';
    private string $accountId  = '78337da8-bf14-4094-b98c-a2edb94ccd13';

    public function run(): void
    {
        $this->cleanup();

        $this->seedNotificationPreferences();
        $this->seedBeneficiaries();
        $this->seedSavingsPockets();
        $this->seedSavingsChallenges();
        $this->seedBudgets();
        $this->seedInvestments();
        $this->seedAirtimeOrders();
        $this->seedCreditScore();
        $this->seedKycUpgradeRequest();
        $this->seedLoanApplication();
        $this->seedNotifications();

        $this->command->info('Zainab portal data seeded.');
    }

    private function cleanup(): void
    {
        DB::table('notification_preferences')->where('customer_id', $this->customerId)->delete();
        DB::table('beneficiaries')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_savings_challenges')->where('customer_id', $this->customerId)->delete();
        DB::table('savings_pockets')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_budgets')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_investments')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_airtime_orders')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_credit_scores')->where('customer_id', $this->customerId)->delete();
        DB::table('kyc_upgrade_requests')->where('customer_id', $this->customerId)->delete();
        DB::table('loan_applications')->where('customer_id', $this->customerId)->delete();
        DB::table('portal_notifications')->where('customer_id', $this->customerId)->delete();
    }

    // ─── Notification Preferences ─────────────────────────────────────────────
    private function seedNotificationPreferences(): void
    {
        DB::table('notification_preferences')->insert([
            'id'                  => (string) Str::uuid(),
            'customer_id'         => $this->customerId,
            'tenant_id'           => $this->tenantId,
            'transaction_alerts'  => true,
            'low_balance_alerts'  => true,
            'weekly_statements'   => true,
            'loan_reminders'      => true,
            'marketing'           => false,
            'created_at'          => now()->subDays(30),
            'updated_at'          => now(),
        ]);
        $this->command->line('  Notification preferences: 1');
    }

    // ─── Beneficiaries ────────────────────────────────────────────────────────
    private function seedBeneficiaries(): void
    {
        $list = [
            ['nickname' => 'Mum',          'account_number' => '2034501298', 'account_name' => 'Fatima Aliyu',       'is_intrabank' => false, 'bank_code' => '044', 'bank_name' => 'Access Bank',  'count' => 12],
            ['nickname' => 'Husband',       'account_number' => '1023456789', 'account_name' => 'Ibrahim Aliyu',      'is_intrabank' => false, 'bank_code' => '058', 'bank_name' => 'GTBank',       'count' => 8],
            ['nickname' => 'Office Rent',   'account_number' => '3012345678', 'account_name' => 'PH Properties Ltd',  'is_intrabank' => false, 'bank_code' => '011', 'bank_name' => 'First Bank',   'count' => 6],
            ['nickname' => 'Sister Amina',  'account_number' => '1006543210', 'account_name' => 'Amina Suleiman',     'is_intrabank' => true,  'bank_code' => null,  'bank_name' => null,           'count' => 4],
            ['nickname' => 'DSTV',          'account_number' => '2056789012', 'account_name' => 'MultiChoice Nigeria','is_intrabank' => false, 'bank_code' => '033', 'bank_name' => 'United Bank',  'count' => 3],
        ];

        foreach ($list as $b) {
            DB::table('beneficiaries')->insert([
                'id'               => (string) Str::uuid(),
                'customer_id'      => $this->customerId,
                'tenant_id'        => $this->tenantId,
                'nickname'         => $b['nickname'],
                'account_number'   => $b['account_number'],
                'account_name'     => $b['account_name'],
                'is_intrabank'     => $b['is_intrabank'],
                'bank_code'        => $b['bank_code'],
                'bank_name'        => $b['bank_name'],
                'transfer_count'   => $b['count'],
                'last_transfer_at' => now()->subDays(rand(3, 60)),
                'created_at'       => now()->subMonths(rand(1, 6)),
                'updated_at'       => now(),
            ]);
        }
        $this->command->line('  Beneficiaries: ' . \count($list));
    }

    // ─── Savings Pockets ──────────────────────────────────────────────────────
    private function seedSavingsPockets(): void
    {
        $pockets = [
            ['name' => 'Emergency Fund',    'emoji' => '🛡️',  'target' => 500_000,  'balance' => 185_000, 'type' => 'manual',     'status' => 'active'],
            ['name' => 'School Fees 2026',  'emoji' => '🎓',  'target' => 350_000,  'balance' => 210_000, 'type' => 'scheduled',  'status' => 'active'],
            ['name' => 'New Phone',         'emoji' => '📱',  'target' => 150_000,  'balance' => 150_000, 'type' => 'manual',     'status' => 'completed'],
            ['name' => 'Vacation Abuja',    'emoji' => '✈️',  'target' => 200_000,  'balance' => 45_000,  'type' => 'round_up',   'status' => 'active'],
        ];

        foreach ($pockets as $p) {
            DB::table('savings_pockets')->insert([
                'id'            => (string) Str::uuid(),
                'tenant_id'     => $this->tenantId,
                'account_id'    => $this->accountId,
                'customer_id'   => $this->customerId,
                'name'          => $p['name'],
                'emoji'         => $p['emoji'],
                'target_amount' => $p['target'],
                'target_date'   => now()->addMonths(rand(2, 8))->toDateString(),
                'balance'       => $p['balance'],
                'type'          => $p['type'],
                'auto_rule'     => null,
                'locked_until'  => null,
                'interest_rate' => 4.5,
                'status'        => $p['status'],
                'created_at'    => now()->subMonths(rand(1, 5)),
                'updated_at'    => now(),
            ]);
        }
        $this->command->line('  Savings pockets: ' . \count($pockets));
    }

    // ─── Savings Challenges ───────────────────────────────────────────────────
    private function seedSavingsChallenges(): void
    {
        $challenges = [
            [
                'name'           => '52-Week Challenge',
                'emoji'          => '🏆',
                'target_amount'  => 137_800,
                'amount_per_save'=> 1_000,
                'frequency'      => 'weekly',
                'current_amount' => 15_000,
                'streak_count'   => 14,
                'total_saves'    => 15,
                'status'         => 'active',
                'start_date'     => now()->subWeeks(15)->toDateString(),
            ],
            [
                'name'           => 'Daily ₦500 Habit',
                'emoji'          => '💪',
                'target_amount'  => 182_500,
                'amount_per_save'=> 500,
                'frequency'      => 'daily',
                'current_amount' => 42_000,
                'streak_count'   => 7,
                'total_saves'    => 84,
                'status'         => 'active',
                'start_date'     => now()->subDays(90)->toDateString(),
            ],
        ];

        foreach ($challenges as $c) {
            DB::table('portal_savings_challenges')->insert([
                'id'              => (string) Str::uuid(),
                'customer_id'     => $this->customerId,
                'tenant_id'       => $this->tenantId,
                'account_id'      => $this->accountId,
                'pocket_id'       => null,
                'name'            => $c['name'],
                'emoji'           => $c['emoji'],
                'target_amount'   => $c['target_amount'],
                'amount_per_save' => $c['amount_per_save'],
                'frequency'       => $c['frequency'],
                'current_amount'  => $c['current_amount'],
                'streak_count'    => $c['streak_count'],
                'total_saves'     => $c['total_saves'],
                'start_date'      => $c['start_date'],
                'target_date'     => now()->addMonths(8)->toDateString(),
                'status'          => $c['status'],
                'completed_at'    => null,
                'created_at'      => $c['start_date'],
                'updated_at'      => now(),
            ]);
        }
        $this->command->line('  Savings challenges: ' . \count($challenges));
    }

    // ─── Monthly Budgets ──────────────────────────────────────────────────────
    private function seedBudgets(): void
    {
        $month = now()->format('Y-m');

        $categories = [
            ['category' => 'food',          'monthly_limit' => 80_000,  'color_hex' => '#f97316'],
            ['category' => 'transport',      'monthly_limit' => 30_000,  'color_hex' => '#3b82f6'],
            ['category' => 'bills',          'monthly_limit' => 50_000,  'color_hex' => '#8b5cf6'],
            ['category' => 'entertainment',  'monthly_limit' => 20_000,  'color_hex' => '#ec4899'],
            ['category' => 'health',         'monthly_limit' => 25_000,  'color_hex' => '#10b981'],
            ['category' => 'shopping',       'monthly_limit' => 40_000,  'color_hex' => '#f59e0b'],
            ['category' => 'education',      'monthly_limit' => 60_000,  'color_hex' => '#6366f1'],
        ];

        foreach ($categories as $c) {
            DB::table('portal_budgets')->insert([
                'id'            => (string) Str::uuid(),
                'customer_id'   => $this->customerId,
                'tenant_id'     => $this->tenantId,
                'category'      => $c['category'],
                'monthly_limit' => $c['monthly_limit'],
                'month'         => $month,
                'color_hex'     => $c['color_hex'],
                'created_at'    => now()->startOfMonth(),
                'updated_at'    => now(),
            ]);
        }
        $this->command->line('  Budget categories: ' . \count($categories));
    }

    // ─── Portal Investments ───────────────────────────────────────────────────
    private function seedInvestments(): void
    {
        $product = DB::table('portal_investment_products')
            ->where('tenant_id', $this->tenantId)
            ->where('duration_days', 180)
            ->first();

        if (!$product) {
            $this->command->warn('  No portal investment products found — run BankingProductsSeeder first.');
            return;
        }

        $principal = 200_000;
        $interest  = round($principal * ($product->interest_rate / 100) * ($product->duration_days / 365), 2);
        $startDate = now()->subDays(45);

        DB::table('portal_investments')->insert([
            'id'               => (string) Str::uuid(),
            'customer_id'      => $this->customerId,
            'tenant_id'        => $this->tenantId,
            'account_id'       => $this->accountId,
            'reference'        => 'INV-' . strtoupper(Str::random(8)),
            'name'             => $product->name,
            'principal'        => $principal,
            'interest_rate'    => $product->interest_rate,
            'duration_days'    => $product->duration_days,
            'expected_interest'=> $interest,
            'maturity_amount'  => $principal + $interest,
            'start_date'       => $startDate->toDateString(),
            'maturity_date'    => $startDate->copy()->addDays($product->duration_days)->toDateString(),
            'status'           => 'active',
            'matured_at'       => null,
            'broken_at'        => null,
            'penalty_amount'   => 0,
            'created_at'       => $startDate,
            'updated_at'       => now(),
        ]);
        $this->command->line('  Portal investments: 1');
    }

    // ─── Airtime / Data Orders ────────────────────────────────────────────────
    private function seedAirtimeOrders(): void
    {
        $orders = [
            ['type' => 'airtime', 'phone' => '+234821870304', 'network' => 'mtn',     'data_plan' => null,       'amount' => 2_000,  'daysAgo' => 2],
            ['type' => 'data',    'phone' => '+234821870304', 'network' => 'mtn',     'data_plan' => '10GB/30D', 'amount' => 3_500,  'daysAgo' => 7],
            ['type' => 'airtime', 'phone' => '+234803456789', 'network' => 'glo',     'data_plan' => null,       'amount' => 1_000,  'daysAgo' => 14],
            ['type' => 'data',    'phone' => '+234821870304', 'network' => 'airtel',  'data_plan' => '5GB/30D',  'amount' => 2_000,  'daysAgo' => 21],
        ];

        foreach ($orders as $o) {
            DB::table('portal_airtime_orders')->insert([
                'id'         => (string) Str::uuid(),
                'customer_id'=> $this->customerId,
                'tenant_id'  => $this->tenantId,
                'account_id' => $this->accountId,
                'type'       => $o['type'],
                'phone'      => $o['phone'],
                'network'    => $o['network'],
                'data_plan'  => $o['data_plan'],
                'amount'     => $o['amount'],
                'reference'  => 'AIR-' . strtoupper(Str::random(10)),
                'status'     => 'completed',
                'created_at' => now()->subDays($o['daysAgo']),
                'updated_at' => now()->subDays($o['daysAgo']),
            ]);
        }
        $this->command->line('  Airtime/data orders: ' . \count($orders));
    }

    // ─── Credit Score ─────────────────────────────────────────────────────────
    private function seedCreditScore(): void
    {
        DB::table('portal_credit_scores')->insert([
            'id'                   => (string) Str::uuid(),
            'customer_id'          => $this->customerId,
            'tenant_id'            => $this->tenantId,
            'score'                => 718,
            'grade'                => 'Good',
            'payment_history_score'=> 85,
            'utilization_score'    => 72,
            'account_age_score'    => 68,
            'account_mix_score'    => 75,
            'activity_score'       => 80,
            'factors'              => json_encode([
                'positive' => ['Consistent repayment history', 'Active savings habit', 'Low credit utilization'],
                'negative'  => ['Short credit history', 'Limited credit mix'],
            ]),
            'created_at'           => now()->subDays(5),
            'updated_at'           => now()->subDays(5),
        ]);
        $this->command->line('  Credit score: 718 (Good)');
    }

    // ─── KYC Upgrade Request ──────────────────────────────────────────────────
    private function seedKycUpgradeRequest(): void
    {
        DB::table('kyc_upgrade_requests')->insert([
            'id'                  => (string) Str::uuid(),
            'customer_id'         => $this->customerId,
            'tenant_id'           => $this->tenantId,
            'current_tier'        => 'level_1',
            'target_tier'         => 'level_2',
            'bvn'                 => '22977773200',
            'nin'                 => '36591392499',
            'id_type'             => 'national_id',
            'id_number'           => 'A12345678',
            'id_document_path'    => null,
            'selfie_path'         => null,
            'address_proof_path'  => null,
            'status'              => 'submitted',
            'reviewer_notes'      => null,
            'reviewed_by'         => null,
            'reviewed_at'         => null,
            'created_at'          => now()->subDays(3),
            'updated_at'          => now()->subDays(3),
        ]);
        $this->command->line('  KYC upgrade request: level_1 → level_2 (pending)');
    }

    // ─── Portal Loan Application ──────────────────────────────────────────────
    private function seedLoanApplication(): void
    {
        DB::table('loan_applications')->insert([
            'id'                     => (string) Str::uuid(),
            'customer_id'            => $this->customerId,
            'tenant_id'              => $this->tenantId,
            'account_id'             => $this->accountId,
            'reference'              => 'LAPP-' . strtoupper(Str::random(8)),
            'loan_type'              => 'personal',
            'requested_amount'       => 300_000,
            'requested_tenor_months' => 12,
            'monthly_income'         => 250_000,
            'employment_status'      => 'employed',
            'employer_name'          => 'Ogun State Government',
            'purpose'                => 'Home renovation and purchase of furniture',
            'collateral_description' => null,
            'collateral_value'       => null,
            'status'                 => 'submitted',
            'officer_notes'          => null,
            'reviewed_by'            => null,
            'reviewed_at'            => null,
            'resulting_loan_id'      => null,
            'created_at'             => now()->subDays(1),
            'updated_at'             => now()->subDays(1),
        ]);
        $this->command->line('  Loan application: ₦300,000 personal (pending)');
    }

    // ─── Portal Notifications ─────────────────────────────────────────────────
    private function seedNotifications(): void
    {
        $notifications = [
            ['type' => 'credit',      'icon' => '💰', 'title' => 'Account Credited',           'body' => 'Your account was credited with ₦50,000 from Ibrahim Aliyu.',           'read_at' => null,           'daysAgo' => 0],
            ['type' => 'investment',  'icon' => '📈', 'title' => 'Investment Update',           'body' => 'Your 6-Month Fixed Return investment is earning well. 45 days active.', 'read_at' => null,           'daysAgo' => 1],
            ['type' => 'kyc',         'icon' => '🔍', 'title' => 'KYC Upgrade Submitted',       'body' => 'Your Level 2 upgrade request is under review. Expected: 2-3 days.',     'read_at' => null,           'daysAgo' => 3],
            ['type' => 'savings',     'icon' => '🏆', 'title' => 'Savings Milestone!',          'body' => 'You saved ₦15,000 on your 52-Week Challenge. Keep it up!',              'read_at' => now()->subDays(3), 'daysAgo' => 4],
            ['type' => 'debit',       'icon' => '💸', 'title' => 'Account Debited',             'body' => 'Your account was debited ₦2,000 for MTN airtime recharge.',              'read_at' => now()->subDays(2), 'daysAgo' => 2],
            ['type' => 'loan',        'icon' => '📋', 'title' => 'Loan Application Received',   'body' => 'Your ₦300,000 personal loan application has been received and is being reviewed.', 'read_at' => null, 'daysAgo' => 1],
            ['type' => 'security',    'icon' => '🔐', 'title' => 'Portal Access Activated',     'body' => 'Welcome to bankOS Portal! Your account is now active.',                  'read_at' => now()->subDays(10), 'daysAgo' => 10],
        ];

        foreach ($notifications as $n) {
            DB::table('portal_notifications')->insert([
                'id'          => (string) Str::uuid(),
                'customer_id' => $this->customerId,
                'tenant_id'   => $this->tenantId,
                'type'        => $n['type'],
                'icon'        => $n['icon'],
                'title'       => $n['title'],
                'body'        => $n['body'],
                'data'        => null,
                'action_url'  => null,
                'read_at'     => $n['read_at'],
                'created_at'  => now()->subDays($n['daysAgo']),
                'updated_at'  => now()->subDays($n['daysAgo']),
            ]);
        }
        $this->command->line('  Portal notifications: ' . \count($notifications));
    }
}
