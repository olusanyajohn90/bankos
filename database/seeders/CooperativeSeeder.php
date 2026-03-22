<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CooperativeSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';
        $now = now();

        // ── DIVIDEND DECLARATION ──────────────────────────────────
        $divId = Str::uuid()->toString();
        DB::table('dividend_declarations')->insert([
            'id' => $divId, 'tenant_id' => $tenantId,
            'title' => '2025 Annual Dividend Distribution',
            'financial_year' => '2025',
            'total_surplus' => 5000000.00,
            'dividend_rate' => 8.5000,
            'total_distributed' => 0,
            'eligible_members' => 15,
            'declaration_date' => '2026-01-15',
            'payment_date' => '2026-02-01',
            'status' => 'completed',
            'notes' => 'Annual dividend for FY2025. Approved at AGM on 2026-01-10.',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Dividend payouts
        $members = DB::table('member_shares')
            ->where('tenant_id', $tenantId)->where('status', 'active')
            ->selectRaw('customer_id, SUM(quantity) as total_shares')
            ->groupBy('customer_id')->get();

        $totalDist = 0;
        foreach ($members as $m) {
            $amount = $m->total_shares * 85;
            $totalDist += $amount;
            $acct = DB::table('accounts')->where('customer_id', $m->customer_id)->where('status', 'active')->first();
            DB::table('dividend_payouts')->insert([
                'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                'dividend_declaration_id' => $divId, 'customer_id' => $m->customer_id,
                'shares_held' => $m->total_shares, 'amount' => $amount,
                'account_id' => $acct->id ?? null, 'status' => 'paid',
                'paid_at' => '2026-02-01 10:00:00',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        DB::table('dividend_declarations')->where('id', $divId)->update(['total_distributed' => $totalDist]);

        // Draft dividend for 2026
        DB::table('dividend_declarations')->insert([
            'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
            'title' => '2026 Mid-Year Interim Dividend',
            'financial_year' => '2026',
            'total_surplus' => 3200000.00,
            'dividend_rate' => 5.0000,
            'total_distributed' => 0,
            'eligible_members' => 15,
            'declaration_date' => '2026-06-30',
            'payment_date' => null,
            'status' => 'draft',
            'notes' => 'Proposed interim dividend for H1 2026. Pending board approval.',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // ── CONTRIBUTION SCHEDULES ───────────────────────────────
        $monthlyDuesId = Str::uuid()->toString();
        $welfareId = Str::uuid()->toString();
        $buildingId = Str::uuid()->toString();

        DB::table('contribution_schedules')->insert([
            ['id' => $monthlyDuesId, 'tenant_id' => $tenantId, 'name' => 'Monthly Dues', 'description' => 'Mandatory monthly membership dues', 'amount' => 5000.00, 'frequency' => 'monthly', 'mandatory' => true, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $welfareId, 'tenant_id' => $tenantId, 'name' => 'Welfare Fund', 'description' => 'Monthly welfare contribution for member emergencies', 'amount' => 2000.00, 'frequency' => 'monthly', 'mandatory' => true, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $buildingId, 'tenant_id' => $tenantId, 'name' => 'Building Levy', 'description' => 'One-time levy for new cooperative office building', 'amount' => 50000.00, 'frequency' => 'one_time', 'mandatory' => true, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Seed contributions for last 3 months
        $customerIds = DB::table('member_shares')
            ->where('tenant_id', $tenantId)->where('status', 'active')
            ->distinct()->pluck('customer_id');

        $periods = ['2026-01', '2026-02', '2026-03'];
        foreach ($customerIds as $cid) {
            foreach ($periods as $period) {
                if (rand(1, 10) <= 9) {
                    DB::table('member_contributions')->insert([
                        'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                        'customer_id' => $cid, 'contribution_schedule_id' => $monthlyDuesId,
                        'amount' => 5000.00, 'period' => $period,
                        'payment_method' => ['cash', 'transfer', 'deduction'][rand(0, 2)],
                        'reference' => 'CTB-' . strtoupper(Str::random(8)),
                        'status' => 'paid', 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
                if (rand(1, 10) <= 8) {
                    DB::table('member_contributions')->insert([
                        'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                        'customer_id' => $cid, 'contribution_schedule_id' => $welfareId,
                        'amount' => 2000.00, 'period' => $period,
                        'payment_method' => ['cash', 'transfer'][rand(0, 1)],
                        'reference' => 'CTB-' . strtoupper(Str::random(8)),
                        'status' => 'paid', 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
            if (rand(1, 10) <= 7) {
                DB::table('member_contributions')->insert([
                    'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
                    'customer_id' => $cid, 'contribution_schedule_id' => $buildingId,
                    'amount' => 50000.00, 'period' => '2026',
                    'payment_method' => 'transfer',
                    'reference' => 'CTB-' . strtoupper(Str::random(8)),
                    'status' => 'paid', 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // ── MEMBER EXITS ─────────────────────────────────────────
        DB::table('member_exits')->insert([
            'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
            'customer_id' => '78a09e40-f625-430f-96f2-fc2e7c7ac103',
            'exit_type' => 'voluntary', 'reason' => 'Relocating to another city',
            'share_refund' => 25000.00, 'savings_balance' => 113000.00,
            'outstanding_loans' => 0, 'pending_contributions' => 5000.00,
            'net_settlement' => 133000.00, 'status' => 'settled',
            'exit_date' => '2026-02-15', 'settlement_date' => '2026-02-28',
            'notes' => 'All dues cleared. Share certificate returned.',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('member_exits')->insert([
            'id' => Str::uuid()->toString(), 'tenant_id' => $tenantId,
            'customer_id' => '9f63dc33-4a42-44c0-9f9a-3de07903aa21',
            'exit_type' => 'voluntary', 'reason' => 'Financial difficulties, unable to meet obligations',
            'share_refund' => 15000.00, 'savings_balance' => 197000.00,
            'outstanding_loans' => 50000.00, 'pending_contributions' => 12000.00,
            'net_settlement' => 150000.00, 'status' => 'pending',
            'exit_date' => null, 'settlement_date' => null,
            'notes' => 'Has outstanding loan. Awaiting clearance.',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->command->info('Cooperative data seeded successfully!');
    }
}
