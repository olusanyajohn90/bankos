<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinacleModulesSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';
    private int $adminId     = 3;
    private int $complianceId = 2;
    private int $loanOfficerId = 4;

    public function run(): void
    {
        $this->seedTreasuryPlacements();
        $this->seedTreasuryFxDeals();
        $this->seedTradeFinanceInstruments();
        $this->seedCashPositions();
        $this->seedInvestmentPortfoliosAndHoldings();
        $this->seedApiClientsAndLogs();
        $this->seedRiskAssessmentsAndLimits();
        $this->seedRegulatoryReports();
        $this->seedBpmProcessesAndInstances();
    }

    // ── 1. Treasury Placements ────────────────────────────────────────────────

    private function seedTreasuryPlacements(): void
    {
        $now = Carbon::now();

        $placements = [
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'reference'        => 'TRP-2026-00001',
                'type'             => 'placement',
                'counterparty'     => 'Guaranty Trust Bank Plc',
                'principal'        => 500_000_000.00,
                'interest_rate'    => 12.0000,
                'start_date'       => $now->copy()->subDays(30)->toDateString(),
                'maturity_date'    => $now->copy()->addDays(60)->toDateString(),
                'tenor_days'       => 90,
                'expected_interest'=> 14_794_520.55,
                'accrued_interest' => 4_931_506.85,
                'status'           => 'active',
                'notes'            => 'Interbank placement at competitive rate',
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(30),
                'updated_at'       => $now,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'reference'        => 'TRP-2026-00002',
                'type'             => 'placement',
                'counterparty'     => 'Zenith Bank Plc',
                'principal'        => 300_000_000.00,
                'interest_rate'    => 10.5000,
                'start_date'       => $now->copy()->subDays(60)->toDateString(),
                'maturity_date'    => $now->copy()->addDays(120)->toDateString(),
                'tenor_days'       => 180,
                'expected_interest'=> 15_534_246.58,
                'accrued_interest' => 5_178_082.19,
                'status'           => 'active',
                'notes'            => '6-month tenor placement',
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(60),
                'updated_at'       => $now,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'reference'        => 'TRP-2026-00003',
                'type'             => 'placement',
                'counterparty'     => 'Access Bank Plc',
                'principal'        => 200_000_000.00,
                'interest_rate'    => 11.0000,
                'start_date'       => $now->copy()->subDays(35)->toDateString(),
                'maturity_date'    => $now->copy()->subDays(5)->toDateString(),
                'tenor_days'       => 30,
                'expected_interest'=> 1_808_219.18,
                'accrued_interest' => 1_808_219.18,
                'status'           => 'matured',
                'notes'            => 'Short-term placement — matured and settled',
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(35),
                'updated_at'       => $now->copy()->subDays(5),
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'reference'        => 'TRP-2026-00004',
                'type'             => 'borrowing',
                'counterparty'     => 'Central Bank of Nigeria',
                'principal'        => 1_000_000_000.00,
                'interest_rate'    => 8.0000,
                'start_date'       => $now->copy()->subDays(90)->toDateString(),
                'maturity_date'    => $now->copy()->addDays(275)->toDateString(),
                'tenor_days'       => 365,
                'expected_interest'=> 80_000_000.00,
                'accrued_interest' => 19_726_027.40,
                'status'           => 'active',
                'notes'            => 'CBN standing lending facility drawdown',
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(90),
                'updated_at'       => $now,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'reference'        => 'TRP-2026-00005',
                'type'             => 'placement',
                'counterparty'     => 'United Bank for Africa Plc',
                'principal'        => 150_000_000.00,
                'interest_rate'    => 13.0000,
                'start_date'       => $now->copy()->subDays(65)->toDateString(),
                'maturity_date'    => $now->copy()->subDays(5)->toDateString(),
                'tenor_days'       => 60,
                'expected_interest'=> 3_205_479.45,
                'accrued_interest' => 3_205_479.45,
                'status'           => 'rolled_over',
                'notes'            => 'Rolled over into new 60-day tenor at same rate',
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(65),
                'updated_at'       => $now,
            ],
        ];

        DB::table('treasury_placements')->insertOrIgnore($placements);
        $this->command->info('  ✓ 5 treasury placements seeded');
    }

    // ── 2. Treasury FX Deals ──────────────────────────────────────────────────

    private function seedTreasuryFxDeals(): void
    {
        $now = Carbon::now();

        $deals = [
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'reference'       => 'FX-2026-00001',
                'deal_type'       => 'spot',
                'direction'       => 'buy',
                'currency_pair'   => 'USD/NGN',
                'amount'          => 50_000.00,
                'rate'            => 1550.000000,
                'counter_amount'  => 77_500_000.00,
                'trade_date'      => $now->copy()->subDays(10)->toDateString(),
                'settlement_date' => $now->copy()->subDays(8)->toDateString(),
                'status'          => 'settled',
                'counterparty'    => 'Zenith Bank Plc',
                'dealer_id'       => $this->adminId,
                'created_at'      => $now->copy()->subDays(10),
                'updated_at'      => $now->copy()->subDays(8),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'reference'       => 'FX-2026-00002',
                'deal_type'       => 'spot',
                'direction'       => 'sell',
                'currency_pair'   => 'EUR/NGN',
                'amount'          => 20_000.00,
                'rate'            => 1680.000000,
                'counter_amount'  => 33_600_000.00,
                'trade_date'      => $now->copy()->subDays(7)->toDateString(),
                'settlement_date' => $now->copy()->subDays(5)->toDateString(),
                'status'          => 'settled',
                'counterparty'    => 'Access Bank Plc',
                'dealer_id'       => $this->adminId,
                'created_at'      => $now->copy()->subDays(7),
                'updated_at'      => $now->copy()->subDays(5),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'reference'       => 'FX-2026-00003',
                'deal_type'       => 'forward',
                'direction'       => 'buy',
                'currency_pair'   => 'GBP/NGN',
                'amount'          => 30_000.00,
                'rate'            => 1950.000000,
                'counter_amount'  => 58_500_000.00,
                'trade_date'      => $now->copy()->subDays(3)->toDateString(),
                'settlement_date' => $now->copy()->addDays(27)->toDateString(),
                'status'          => 'pending',
                'counterparty'    => 'First Bank of Nigeria',
                'dealer_id'       => $this->adminId,
                'created_at'      => $now->copy()->subDays(3),
                'updated_at'      => $now,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'reference'       => 'FX-2026-00004',
                'deal_type'       => 'spot',
                'direction'       => 'buy',
                'currency_pair'   => 'USD/NGN',
                'amount'          => 100_000.00,
                'rate'            => 1555.000000,
                'counter_amount'  => 155_500_000.00,
                'trade_date'      => $now->toDateString(),
                'settlement_date' => $now->copy()->addDays(2)->toDateString(),
                'status'          => 'pending',
                'counterparty'    => 'Guaranty Trust Bank Plc',
                'dealer_id'       => $this->adminId,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        DB::table('treasury_fx_deals')->insertOrIgnore($deals);
        $this->command->info('  ✓ 4 treasury FX deals seeded');
    }

    // ── 3. Trade Finance Instruments ──────────────────────────────────────────

    private function seedTradeFinanceInstruments(): void
    {
        $now = Carbon::now();

        // Grab first 4 customer IDs from the tenant
        $customerIds = DB::table('customers')
            ->where('tenant_id', $this->tenantId)
            ->orderBy('created_at')
            ->limit(4)
            ->pluck('id')
            ->toArray();

        if (count($customerIds) < 4) {
            $this->command->warn('  ⚠ Not enough customers for trade finance — need 4, have ' . count($customerIds));
            // Pad with first customer if needed
            while (count($customerIds) < 4) {
                $customerIds[] = $customerIds[0] ?? Str::uuid()->toString();
            }
        }

        $instruments = [
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'customer_id'      => $customerIds[0],
                'reference'        => 'TF-LC-2026-00001',
                'type'             => 'letter_of_credit',
                'beneficiary_name' => 'Shanghai Heavy Machinery Co. Ltd',
                'beneficiary_bank' => 'Industrial and Commercial Bank of China',
                'amount'           => 50_000_000.00,
                'currency'         => 'NGN',
                'issue_date'       => $now->copy()->subDays(30)->toDateString(),
                'expiry_date'      => $now->copy()->addDays(150)->toDateString(),
                'purpose'          => 'Import of industrial milling machinery and spare parts',
                'terms'            => 'Irrevocable LC, documents against payment, 90 days usance',
                'commission_rate'  => 1.5000,
                'commission_amount'=> 750_000.00,
                'status'           => 'issued',
                'documents'        => json_encode(['pro_forma_invoice', 'insurance_cert', 'bill_of_lading']),
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(30),
                'updated_at'       => $now,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'customer_id'      => $customerIds[1],
                'reference'        => 'TF-BG-2026-00001',
                'type'             => 'bank_guarantee',
                'beneficiary_name' => 'Federal Ministry of Works',
                'beneficiary_bank' => null,
                'amount'           => 20_000_000.00,
                'currency'         => 'NGN',
                'issue_date'       => $now->copy()->subDays(15)->toDateString(),
                'expiry_date'      => $now->copy()->addDays(345)->toDateString(),
                'purpose'          => 'Bid bond for road construction contract FMW/RC/2026/045',
                'terms'            => 'Performance guarantee, unconditional and irrevocable',
                'commission_rate'  => 2.0000,
                'commission_amount'=> 400_000.00,
                'status'           => 'issued',
                'documents'        => json_encode(['bid_document', 'board_resolution', 'cac_certificate']),
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(15),
                'updated_at'       => $now,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'customer_id'      => $customerIds[2],
                'reference'        => 'TF-BC-2026-00001',
                'type'             => 'bill_for_collection',
                'beneficiary_name' => 'Lagos Commodities Exchange Ltd',
                'beneficiary_bank' => 'First Bank of Nigeria',
                'amount'           => 15_000_000.00,
                'currency'         => 'NGN',
                'issue_date'       => $now->copy()->subDays(45)->toDateString(),
                'expiry_date'      => $now->copy()->subDays(5)->toDateString(),
                'purpose'          => 'Collection of export proceeds for cocoa shipment',
                'terms'            => 'Documents against acceptance, 60 days sight',
                'commission_rate'  => 0.5000,
                'commission_amount'=> 75_000.00,
                'status'           => 'utilized',
                'documents'        => json_encode(['bill_of_exchange', 'shipping_docs', 'certificate_of_origin']),
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(45),
                'updated_at'       => $now->copy()->subDays(5),
            ],
            [
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $this->tenantId,
                'customer_id'      => $customerIds[3],
                'reference'        => 'TF-ID-2026-00001',
                'type'             => 'invoice_discounting',
                'beneficiary_name' => 'Dangote Cement Plc',
                'beneficiary_bank' => null,
                'amount'           => 30_000_000.00,
                'currency'         => 'NGN',
                'issue_date'       => $now->copy()->subDays(2)->toDateString(),
                'expiry_date'      => $now->copy()->addDays(88)->toDateString(),
                'purpose'          => 'Discounting of approved invoices for cement supply contract',
                'terms'            => '80% advance, balance on collection less discount charges',
                'commission_rate'  => 1.0000,
                'commission_amount'=> 300_000.00,
                'status'           => 'draft',
                'documents'        => json_encode(['invoice', 'delivery_note', 'purchase_order']),
                'created_by'       => $this->adminId,
                'created_at'       => $now->copy()->subDays(2),
                'updated_at'       => $now,
            ],
        ];

        DB::table('trade_finance_instruments')->insertOrIgnore($instruments);
        $this->command->info('  ✓ 4 trade finance instruments seeded');
    }

    // ── 4. Cash Positions (last 7 days) ───────────────────────────────────────

    private function seedCashPositions(): void
    {
        $now = Carbon::now();
        $positions = [];
        $openingBalance = 620_000_000.00;

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $inflows  = round(rand(50_000_000, 200_000_000) / 100) * 100;
            $outflows = round(rand(40_000_000, 180_000_000) / 100) * 100;
            $closingBalance = $openingBalance + $inflows - $outflows;
            $vaultCash = round(rand(50_000_000, 100_000_000) / 100) * 100;
            $nostro    = round(rand(200_000_000, 300_000_000) / 100) * 100;

            $positions[] = [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'position_date'   => $date->toDateString(),
                'currency'        => 'NGN',
                'opening_balance' => $openingBalance,
                'total_inflows'   => $inflows,
                'total_outflows'  => $outflows,
                'closing_balance' => $closingBalance,
                'vault_cash'      => $vaultCash,
                'nostro_balance'  => $nostro,
                'breakdown'       => json_encode([
                    'branch_cash' => round($vaultCash * 0.6),
                    'atm_cash'    => round($vaultCash * 0.25),
                    'transit_cash'=> round($vaultCash * 0.15),
                ]),
                'prepared_by'     => $this->adminId,
                'created_at'      => $date,
                'updated_at'      => $date,
            ];

            $openingBalance = $closingBalance;
        }

        DB::table('cash_positions')->insertOrIgnore($positions);
        $this->command->info('  ✓ 7 daily cash positions seeded');
    }

    // ── 5. Investment Portfolios + Holdings ───────────────────────────────────

    private function seedInvestmentPortfoliosAndHoldings(): void
    {
        $now = Carbon::now();

        $customerIds = DB::table('customers')
            ->where('tenant_id', $this->tenantId)
            ->orderBy('created_at')
            ->limit(3)
            ->pluck('id')
            ->toArray();

        if (count($customerIds) < 3) {
            $this->command->warn('  ⚠ Not enough customers for portfolios — need 3, have ' . count($customerIds));
            while (count($customerIds) < 3) {
                $customerIds[] = $customerIds[0] ?? Str::uuid()->toString();
            }
        }

        $portfolioConservative = Str::uuid()->toString();
        $portfolioGrowth       = Str::uuid()->toString();
        $portfolioTreasury     = Str::uuid()->toString();

        $portfolios = [
            [
                'id'             => $portfolioConservative,
                'tenant_id'      => $this->tenantId,
                'customer_id'    => $customerIds[0],
                'portfolio_name' => 'Conservative Fund',
                'risk_profile'   => 'conservative',
                'total_value'    => 125_500_000.00,
                'total_cost'     => 120_000_000.00,
                'unrealized_pnl' => 5_500_000.00,
                'status'         => 'active',
                'advisor_id'     => $this->adminId,
                'created_at'     => $now->copy()->subMonths(6),
                'updated_at'     => $now,
            ],
            [
                'id'             => $portfolioGrowth,
                'tenant_id'      => $this->tenantId,
                'customer_id'    => $customerIds[1],
                'portfolio_name' => 'Growth Portfolio',
                'risk_profile'   => 'aggressive',
                'total_value'    => 85_200_000.00,
                'total_cost'     => 78_000_000.00,
                'unrealized_pnl' => 7_200_000.00,
                'status'         => 'active',
                'advisor_id'     => $this->adminId,
                'created_at'     => $now->copy()->subMonths(4),
                'updated_at'     => $now,
            ],
            [
                'id'             => $portfolioTreasury,
                'tenant_id'      => $this->tenantId,
                'customer_id'    => $customerIds[2],
                'portfolio_name' => 'Bank Treasury Portfolio',
                'risk_profile'   => 'moderate',
                'total_value'    => 2_350_000_000.00,
                'total_cost'     => 2_300_000_000.00,
                'unrealized_pnl' => 50_000_000.00,
                'status'         => 'active',
                'advisor_id'     => $this->adminId,
                'created_at'     => $now->copy()->subMonths(12),
                'updated_at'     => $now,
            ],
        ];

        DB::table('investment_portfolios')->insertOrIgnore($portfolios);

        // Holdings
        $holdings = [
            // Conservative Fund — 3 holdings
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioConservative,
                'asset_type'    => 'treasury_bill',
                'asset_name'    => 'FGN T-Bill 91-Day Mar 2026',
                'asset_code'    => 'NTB-MAR26-91',
                'quantity'      => 500.0000,
                'cost_price'    => 98.5000,
                'current_price' => 99.2000,
                'market_value'  => 49_600_000.00,
                'purchase_date' => $now->copy()->subMonths(3)->toDateString(),
                'maturity_date' => $now->copy()->addDays(15)->toDateString(),
                'yield_rate'    => 14.5000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(3),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioConservative,
                'asset_type'    => 'bond',
                'asset_name'    => 'FGN Bond 10.5% 2030',
                'asset_code'    => 'FGN-2030-105',
                'quantity'      => 500.0000,
                'cost_price'    => 101.2000,
                'current_price' => 102.8000,
                'market_value'  => 51_400_000.00,
                'purchase_date' => $now->copy()->subMonths(8)->toDateString(),
                'maturity_date' => Carbon::create(2030, 3, 15)->toDateString(),
                'yield_rate'    => 10.2000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(8),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioConservative,
                'asset_type'    => 'money_market',
                'asset_name'    => 'ARM Money Market Fund',
                'asset_code'    => 'ARM-MMF',
                'quantity'      => 24_500.0000,
                'cost_price'    => 1.0000,
                'current_price' => 1.0000,
                'market_value'  => 24_500_000.00,
                'purchase_date' => $now->copy()->subMonths(2)->toDateString(),
                'maturity_date' => null,
                'yield_rate'    => 12.8000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(2),
                'updated_at'    => $now,
            ],

            // Growth Portfolio — 4 holdings
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioGrowth,
                'asset_type'    => 'equity',
                'asset_name'    => 'Dangote Cement Plc',
                'asset_code'    => 'DANGCEM',
                'quantity'      => 50_000.0000,
                'cost_price'    => 280.0000,
                'current_price' => 310.0000,
                'market_value'  => 15_500_000.00,
                'purchase_date' => $now->copy()->subMonths(5)->toDateString(),
                'maturity_date' => null,
                'yield_rate'    => null,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(5),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioGrowth,
                'asset_type'    => 'mutual_fund',
                'asset_name'    => 'Stanbic IBTC Nigerian Equity Fund',
                'asset_code'    => 'STANBIC-NEF',
                'quantity'      => 100_000.0000,
                'cost_price'    => 250.0000,
                'current_price' => 268.0000,
                'market_value'  => 26_800_000.00,
                'purchase_date' => $now->copy()->subMonths(4)->toDateString(),
                'maturity_date' => null,
                'yield_rate'    => null,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(4),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioGrowth,
                'asset_type'    => 'bond',
                'asset_name'    => 'FGN Savings Bond Q1 2026',
                'asset_code'    => 'FGNSB-Q1-2026',
                'quantity'      => 200.0000,
                'cost_price'    => 100.0000,
                'current_price' => 101.5000,
                'market_value'  => 20_300_000.00,
                'purchase_date' => $now->copy()->subMonths(3)->toDateString(),
                'maturity_date' => $now->copy()->addYears(2)->toDateString(),
                'yield_rate'    => 11.5000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(3),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioGrowth,
                'asset_type'    => 'treasury_bill',
                'asset_name'    => 'FGN T-Bill 182-Day Apr 2026',
                'asset_code'    => 'NTB-APR26-182',
                'quantity'      => 250.0000,
                'cost_price'    => 93.2000,
                'current_price' => 96.4000,
                'market_value'  => 24_100_000.00,
                'purchase_date' => $now->copy()->subMonths(2)->toDateString(),
                'maturity_date' => $now->copy()->addMonths(4)->toDateString(),
                'yield_rate'    => 15.2000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(2),
                'updated_at'    => $now,
            ],

            // Bank Treasury Portfolio — 3 holdings
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioTreasury,
                'asset_type'    => 'treasury_bill',
                'asset_name'    => 'FGN T-Bill 364-Day Jun 2026',
                'asset_code'    => 'NTB-JUN26-364',
                'quantity'      => 10_000.0000,
                'cost_price'    => 85.0000,
                'current_price' => 91.5000,
                'market_value'  => 915_000_000.00,
                'purchase_date' => $now->copy()->subMonths(9)->toDateString(),
                'maturity_date' => $now->copy()->addMonths(3)->toDateString(),
                'yield_rate'    => 17.6000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(9),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioTreasury,
                'asset_type'    => 'bond',
                'asset_name'    => 'FGN Bond 12.5% 2035',
                'asset_code'    => 'FGN-2035-125',
                'quantity'      => 8_000.0000,
                'cost_price'    => 98.0000,
                'current_price' => 99.5000,
                'market_value'  => 796_000_000.00,
                'purchase_date' => $now->copy()->subMonths(15)->toDateString(),
                'maturity_date' => Carbon::create(2035, 6, 15)->toDateString(),
                'yield_rate'    => 12.8000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(15),
                'updated_at'    => $now,
            ],
            [
                'id'            => Str::uuid()->toString(),
                'portfolio_id'  => $portfolioTreasury,
                'asset_type'    => 'bond',
                'asset_name'    => 'FGN Green Bond 2028',
                'asset_code'    => 'FGN-GREEN-2028',
                'quantity'      => 6_000.0000,
                'cost_price'    => 100.0000,
                'current_price' => 101.5000,
                'market_value'  => 609_000_000.00,
                'purchase_date' => $now->copy()->subMonths(6)->toDateString(),
                'maturity_date' => Carbon::create(2028, 12, 31)->toDateString(),
                'yield_rate'    => 13.2000,
                'status'        => 'active',
                'created_at'    => $now->copy()->subMonths(6),
                'updated_at'    => $now,
            ],
        ];

        DB::table('investment_holdings')->insertOrIgnore($holdings);
        $this->command->info('  ✓ 3 investment portfolios + 10 holdings seeded');
    }

    // ── 6. API Clients + Request Logs ─────────────────────────────────────────

    private function seedApiClientsAndLogs(): void
    {
        $now = Carbon::now();

        $clientPaystack    = Str::uuid()->toString();
        $clientFlutterwave = Str::uuid()->toString();
        $clientMobileApp   = Str::uuid()->toString();

        $clients = [
            [
                'id'                   => $clientPaystack,
                'tenant_id'            => $this->tenantId,
                'name'                 => 'PaystackConnect',
                'description'          => 'Paystack payments integration for collections and disbursements',
                'client_id'            => 'pk_live_' . Str::random(32),
                'client_secret'        => 'sk_live_' . Str::random(48),
                'webhook_url'          => 'https://api.paystack.co/webhook/bankos',
                'allowed_scopes'       => json_encode(['payments:read', 'payments:write', 'transactions:read']),
                'ip_whitelist'         => json_encode(['52.31.139.75', '52.49.173.169']),
                'is_active'            => true,
                'rate_limit_per_minute'=> 100,
                'total_requests'       => 15_432,
                'last_request_at'      => $now->copy()->subMinutes(5),
                'created_by'           => $this->adminId,
                'created_at'           => $now->copy()->subMonths(6),
                'updated_at'           => $now,
            ],
            [
                'id'                   => $clientFlutterwave,
                'tenant_id'            => $this->tenantId,
                'name'                 => 'FlutterwaveSync',
                'description'          => 'Flutterwave integration for inter-bank transfers and FX',
                'client_id'            => 'fw_live_' . Str::random(32),
                'client_secret'        => 'fwsk_live_' . Str::random(48),
                'webhook_url'          => 'https://api.flutterwave.com/webhook/bankos',
                'allowed_scopes'       => json_encode(['transfers:read', 'transfers:write', 'accounts:read']),
                'ip_whitelist'         => json_encode(['3.211.247.102', '3.211.247.103']),
                'is_active'            => true,
                'rate_limit_per_minute'=> 60,
                'total_requests'       => 8_921,
                'last_request_at'      => $now->copy()->subMinutes(12),
                'created_by'           => $this->adminId,
                'created_at'           => $now->copy()->subMonths(4),
                'updated_at'           => $now,
            ],
            [
                'id'                   => $clientMobileApp,
                'tenant_id'            => $this->tenantId,
                'name'                 => 'MobileApp v2',
                'description'          => 'Customer-facing mobile banking application (iOS/Android)',
                'client_id'            => 'mob_v2_' . Str::random(32),
                'client_secret'        => 'mob_sk_' . Str::random(48),
                'webhook_url'          => null,
                'allowed_scopes'       => json_encode(['accounts:read', 'transactions:read', 'transfers:write', 'profile:read', 'profile:write']),
                'ip_whitelist'         => null,
                'is_active'            => true,
                'rate_limit_per_minute'=> 120,
                'total_requests'       => 45_678,
                'last_request_at'      => $now->copy()->subSeconds(30),
                'created_by'           => $this->adminId,
                'created_at'           => $now->copy()->subMonths(3),
                'updated_at'           => $now,
            ],
        ];

        DB::table('api_clients')->insertOrIgnore($clients);

        // Generate 20 request logs
        $endpoints = [
            ['GET',  '/api/v1/accounts',            200],
            ['GET',  '/api/v1/accounts/{id}',        200],
            ['POST', '/api/v1/transfers',            201],
            ['GET',  '/api/v1/transactions',         200],
            ['POST', '/api/v1/payments/initialize',  201],
            ['GET',  '/api/v1/balance',              200],
            ['POST', '/api/v1/transfers/bulk',       201],
            ['GET',  '/api/v1/transactions/{id}',    200],
            ['POST', '/api/v1/webhooks/verify',      200],
            ['GET',  '/api/v1/customers',            200],
        ];

        $errorEndpoints = [
            ['POST', '/api/v1/transfers',            400, 'Insufficient balance'],
            ['GET',  '/api/v1/accounts/{id}',        404, 'Account not found'],
            ['POST', '/api/v1/payments/initialize',  500, 'Gateway timeout'],
            ['GET',  '/api/v1/transactions',         429, 'Rate limit exceeded'],
        ];

        $clientIds = [$clientPaystack, $clientFlutterwave, $clientMobileApp];
        $logs = [];

        for ($i = 0; $i < 20; $i++) {
            $clientId = $clientIds[$i % 3];
            $hoursAgo = rand(1, 168); // within last 7 days

            if ($i < 15) {
                // 15 successful requests
                $ep = $endpoints[array_rand($endpoints)];
                $logs[] = [
                    'client_id'       => $clientId,
                    'method'          => $ep[0],
                    'endpoint'        => $ep[1],
                    'status_code'     => $ep[2],
                    'response_time_ms'=> rand(45, 350),
                    'ip_address'      => '102.89.' . rand(1, 254) . '.' . rand(1, 254),
                    'created_at'      => $now->copy()->subHours($hoursAgo),
                    'updated_at'      => $now->copy()->subHours($hoursAgo),
                ];
            } else {
                // 5 error requests
                $ep = $errorEndpoints[$i - 15] ?? $errorEndpoints[0];
                $logs[] = [
                    'client_id'       => $clientId,
                    'method'          => $ep[0],
                    'endpoint'        => $ep[1],
                    'status_code'     => $ep[2],
                    'response_time_ms'=> rand(200, 5000),
                    'ip_address'      => '102.89.' . rand(1, 254) . '.' . rand(1, 254),
                    'created_at'      => $now->copy()->subHours($hoursAgo),
                    'updated_at'      => $now->copy()->subHours($hoursAgo),
                ];
            }
        }

        DB::table('api_request_logs')->insert($logs);
        $this->command->info('  ✓ 3 API clients + 20 request logs seeded');
    }

    // ── 7. Risk Assessments + Risk Limits ─────────────────────────────────────

    private function seedRiskAssessmentsAndLimits(): void
    {
        $now = Carbon::now();

        $assessments = [
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'risk_type'       => 'concentration',
                'title'           => 'Credit Concentration Risk — Agriculture Sector',
                'description'     => 'Agricultural sector loans represent 28% of total portfolio, approaching the 30% internal limit. Concentration driven by seasonal lending to cooperative farming groups in North-Central region.',
                'severity'        => 'high',
                'exposure_amount' => 450_000_000.00,
                'metrics'         => json_encode(['sector' => 'agriculture', 'pct_of_portfolio' => 28.0, 'limit' => 30.0, 'breached' => false]),
                'status'          => 'open',
                'mitigation_plan' => 'Diversify new lending towards manufacturing and services sectors. Cap new agric disbursements at ₦50M/month until ratio reduces below 25%.',
                'assigned_to'     => $this->complianceId,
                'created_by'      => $this->adminId,
                'created_at'      => $now->copy()->subDays(15),
                'updated_at'      => $now,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'risk_type'       => 'liquidity',
                'title'           => 'Liquidity Gap in 30-Day Maturity Bucket',
                'description'     => 'Negative cumulative gap of ₦120M in the 0-30 day maturity bucket due to concentration of deposit maturities. Gap covered by available CBN facility.',
                'severity'        => 'medium',
                'exposure_amount' => 120_000_000.00,
                'metrics'         => json_encode(['bucket' => '0-30 days', 'gap_amount' => -120_000_000, 'coverage_ratio' => 1.35]),
                'status'          => 'mitigated',
                'mitigation_plan' => 'Activated CBN standing lending facility. Restructured ₦80M of short-term deposits to 90-day tenor.',
                'assigned_to'     => $this->adminId,
                'created_by'      => $this->complianceId,
                'created_at'      => $now->copy()->subDays(30),
                'updated_at'      => $now->copy()->subDays(5),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'risk_type'       => 'credit',
                'title'           => 'Single Obligor Limit Breach — Adewale Industries Ltd',
                'description'     => 'Total exposure to Adewale Industries Ltd (including off-balance sheet) has reached 22% of shareholders equity, exceeding the 20% CBN single obligor limit.',
                'severity'        => 'critical',
                'exposure_amount' => 880_000_000.00,
                'metrics'         => json_encode(['obligor' => 'Adewale Industries Ltd', 'exposure_pct' => 22.0, 'limit_pct' => 20.0, 'breached' => true]),
                'status'          => 'open',
                'mitigation_plan' => 'Immediate: No new facilities. Syndicate ₦200M of existing exposure to partner banks within 60 days. Report to CBN as required.',
                'assigned_to'     => $this->complianceId,
                'created_by'      => $this->adminId,
                'created_at'      => $now->copy()->subDays(3),
                'updated_at'      => $now,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'risk_type'       => 'operational',
                'title'           => 'System Downtime Incident — Core Banking',
                'description'     => 'Unplanned core banking system downtime of 4.5 hours on March 15 due to database connection pool exhaustion. Affected 2,340 transactions.',
                'severity'        => 'low',
                'exposure_amount' => 0.00,
                'metrics'         => json_encode(['downtime_hours' => 4.5, 'affected_transactions' => 2340, 'date' => '2026-03-15']),
                'status'          => 'closed',
                'mitigation_plan' => 'Increased connection pool from 100 to 250. Added automated alerting at 80% utilization. Implemented read replicas for reporting queries.',
                'assigned_to'     => $this->adminId,
                'created_by'      => $this->adminId,
                'created_at'      => $now->copy()->subDays(20),
                'updated_at'      => $now->copy()->subDays(10),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'risk_type'       => 'market',
                'title'           => 'FX Exposure — Open USD Position',
                'description'     => 'Net open USD position of ₦350M against a ₦500M limit. NGN/USD volatility has increased 15% in the last 30 days, raising potential mark-to-market losses.',
                'severity'        => 'medium',
                'exposure_amount' => 350_000_000.00,
                'metrics'         => json_encode(['currency' => 'USD', 'open_position' => 350_000_000, 'limit' => 500_000_000, 'volatility_30d' => 15.2]),
                'status'          => 'open',
                'mitigation_plan' => 'Reduce open position by ₦100M through forward sales. Daily monitoring of NGN/USD rate movements.',
                'assigned_to'     => $this->adminId,
                'created_by'      => $this->complianceId,
                'created_at'      => $now->copy()->subDays(7),
                'updated_at'      => $now,
            ],
        ];

        DB::table('risk_assessments')->insertOrIgnore($assessments);

        $limits = [
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'single_obligor',
                'name'              => 'Single Obligor Limit',
                'limit_value'       => 20.00,
                'current_value'     => 18.00,
                'utilization_pct'   => 90.00,
                'status'            => 'warning',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'sector_concentration',
                'name'              => 'Sector Concentration — Agriculture',
                'limit_value'       => 30.00,
                'current_value'     => 25.00,
                'utilization_pct'   => 83.33,
                'status'            => 'within_limit',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'liquidity_ratio',
                'name'              => 'Liquidity Ratio',
                'limit_value'       => 30.00,
                'current_value'     => 45.00,
                'utilization_pct'   => 66.67,
                'status'            => 'within_limit',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'currency_exposure',
                'name'              => 'FX Open Position Limit',
                'limit_value'       => 500_000_000.00,
                'current_value'     => 350_000_000.00,
                'utilization_pct'   => 70.00,
                'status'            => 'within_limit',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'single_obligor',
                'name'              => 'NPL Ratio',
                'limit_value'       => 5.00,
                'current_value'     => 3.20,
                'utilization_pct'   => 64.00,
                'status'            => 'within_limit',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'tenant_id'         => $this->tenantId,
                'limit_type'        => 'single_obligor',
                'name'              => 'Capital Adequacy Ratio',
                'limit_value'       => 10.00,
                'current_value'     => 15.00,
                'utilization_pct'   => 66.67,
                'status'            => 'within_limit',
                'warning_threshold' => 80.00,
                'created_at'        => $now->copy()->subMonths(6),
                'updated_at'        => $now,
            ],
        ];

        DB::table('risk_limits')->insertOrIgnore($limits);
        $this->command->info('  ✓ 5 risk assessments + 6 risk limits seeded');
    }

    // ── 8. Regulatory Reports ─────────────────────────────────────────────────

    private function seedRegulatoryReports(): void
    {
        $now = Carbon::now();

        $reports = [
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'cbn_returns',
                'report_name'    => 'CBN Monthly Returns — March 2026',
                'period'         => '2026-03',
                'due_date'       => '2026-04-10',
                'submitted_date' => '2026-04-03',
                'status'         => 'submitted',
                'report_data'    => json_encode(['total_assets' => 12_500_000_000, 'total_deposits' => 8_200_000_000, 'total_loans' => 5_100_000_000]),
                'file_path'      => null,
                'notes'          => 'Submitted ahead of deadline',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => $this->adminId,
                'created_at'     => $now->copy()->subDays(5),
                'updated_at'     => $now->copy()->subDays(1),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'cbn_returns',
                'report_name'    => 'CBN Monthly Returns — February 2026',
                'period'         => '2026-02',
                'due_date'       => '2026-03-10',
                'submitted_date' => '2026-03-08',
                'status'         => 'accepted',
                'report_data'    => json_encode(['total_assets' => 12_100_000_000, 'total_deposits' => 7_900_000_000, 'total_loans' => 4_800_000_000]),
                'file_path'      => null,
                'notes'          => 'Accepted by CBN on March 12',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => $this->adminId,
                'created_at'     => $now->copy()->subDays(30),
                'updated_at'     => $now->copy()->subDays(23),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'ndic_premium',
                'report_name'    => 'NDIC Premium Assessment — Q1 2026',
                'period'         => '2026-Q1',
                'due_date'       => '2026-04-30',
                'submitted_date' => '2026-04-02',
                'status'         => 'submitted',
                'report_data'    => json_encode(['insured_deposits' => 6_500_000_000, 'premium_rate' => 0.0035, 'premium_amount' => 22_750_000]),
                'file_path'      => null,
                'notes'          => 'Quarterly deposit insurance premium computation',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => $this->adminId,
                'created_at'     => $now->copy()->subDays(10),
                'updated_at'     => $now->copy()->subDays(2),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'nfiu_ctr',
                'report_name'    => 'NFIU Currency Transaction Report — March 2026',
                'period'         => '2026-03',
                'due_date'       => '2026-04-15',
                'submitted_date' => null,
                'status'         => 'pending',
                'report_data'    => json_encode(['transactions_above_5m' => 127, 'total_value' => 2_850_000_000]),
                'file_path'      => null,
                'notes'          => 'Data compilation in progress, due April 15',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => null,
                'created_at'     => $now->copy()->subDays(3),
                'updated_at'     => $now,
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'prudential_guidelines',
                'report_name'    => 'Prudential Guidelines Compliance — Q1 2026',
                'period'         => '2026-Q1',
                'due_date'       => '2026-04-30',
                'submitted_date' => null,
                'status'         => 'draft',
                'report_data'    => json_encode(['car' => 15.0, 'npl_ratio' => 3.2, 'liquidity_ratio' => 45.0, 'loan_to_deposit' => 62.0]),
                'file_path'      => null,
                'notes'          => 'Draft under review by compliance team',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => null,
                'created_at'     => $now->copy()->subDays(5),
                'updated_at'     => $now,
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'cbn_returns',
                'report_name'    => 'CBN Monthly Returns — April 2026',
                'period'         => '2026-04',
                'due_date'       => '2026-05-10',
                'submitted_date' => null,
                'status'         => 'pending',
                'report_data'    => null,
                'file_path'      => null,
                'notes'          => 'Due May 10, data collection starts end of month',
                'prepared_by'    => null,
                'approved_by'    => null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'ndic_premium',
                'report_name'    => 'NDIC Depositor Information — Q1 2026',
                'period'         => '2026-Q1',
                'due_date'       => '2026-04-15',
                'submitted_date' => '2026-04-01',
                'status'         => 'submitted',
                'report_data'    => json_encode(['total_depositors' => 12_450, 'insured_depositors' => 12_300, 'uninsured_depositors' => 150]),
                'file_path'      => null,
                'notes'          => 'Depositor information return for NDIC',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => $this->adminId,
                'created_at'     => $now->copy()->subDays(7),
                'updated_at'     => $now->copy()->subDays(3),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'report_type'    => 'nfiu_str',
                'report_name'    => 'NFIU Suspicious Transaction Report — March 2026',
                'period'         => '2026-03',
                'due_date'       => '2026-04-10',
                'submitted_date' => '2026-04-04',
                'status'         => 'submitted',
                'report_data'    => json_encode(['strs_filed' => 3, 'total_value' => 45_000_000]),
                'file_path'      => null,
                'notes'          => '3 STRs filed for structuring and unusual pattern activity',
                'prepared_by'    => $this->complianceId,
                'approved_by'    => $this->adminId,
                'created_at'     => $now->copy()->subDays(4),
                'updated_at'     => $now,
            ],
        ];

        DB::table('regulatory_reports')->insertOrIgnore($reports);
        $this->command->info('  ✓ 8 regulatory reports seeded');
    }

    // ── 9. BPM Processes + Instances ──────────────────────────────────────────

    private function seedBpmProcessesAndInstances(): void
    {
        $now = Carbon::now();

        $processAccountOpening    = Str::uuid()->toString();
        $processLoanProcessing    = Str::uuid()->toString();
        $processDisputeResolution = Str::uuid()->toString();

        $processes = [
            [
                'id'                     => $processAccountOpening,
                'tenant_id'              => $this->tenantId,
                'name'                   => 'Account Opening',
                'description'            => 'End-to-end workflow for new customer account opening including KYC verification, document upload, management approval, and account creation.',
                'category'               => 'account_opening',
                'steps'                  => json_encode([
                    ['step' => 1, 'name' => 'KYC Check',        'type' => 'task',     'config' => ['assignee_role' => 'compliance_officer', 'sla_hours' => 4]],
                    ['step' => 2, 'name' => 'Document Upload',  'type' => 'task',     'config' => ['assignee_role' => 'customer_service', 'sla_hours' => 2]],
                    ['step' => 3, 'name' => 'Approval',         'type' => 'approval', 'config' => ['approver_role' => 'branch_manager', 'sla_hours' => 8]],
                    ['step' => 4, 'name' => 'Account Creation', 'type' => 'task',     'config' => ['assignee_role' => 'operations', 'sla_hours' => 1, 'auto' => true]],
                ]),
                'is_active'              => true,
                'avg_completion_hours'   => 6,
                'total_instances'        => 245,
                'created_by'             => $this->adminId,
                'created_at'             => $now->copy()->subMonths(12),
                'updated_at'             => $now,
            ],
            [
                'id'                     => $processLoanProcessing,
                'tenant_id'              => $this->tenantId,
                'name'                   => 'Loan Processing',
                'description'            => 'Complete loan lifecycle from application through credit assessment, appraisal, committee approval, and final disbursement.',
                'category'               => 'loan_processing',
                'steps'                  => json_encode([
                    ['step' => 1, 'name' => 'Application',    'type' => 'task',     'config' => ['assignee_role' => 'loan_officer', 'sla_hours' => 2]],
                    ['step' => 2, 'name' => 'Credit Check',   'type' => 'task',     'config' => ['assignee_role' => 'credit_analyst', 'sla_hours' => 24]],
                    ['step' => 3, 'name' => 'Appraisal',      'type' => 'task',     'config' => ['assignee_role' => 'loan_officer', 'sla_hours' => 48]],
                    ['step' => 4, 'name' => 'Approval',       'type' => 'approval', 'config' => ['approver_role' => 'credit_committee', 'sla_hours' => 72]],
                    ['step' => 5, 'name' => 'Disbursement',   'type' => 'task',     'config' => ['assignee_role' => 'operations', 'sla_hours' => 4, 'auto' => true]],
                ]),
                'is_active'              => true,
                'avg_completion_hours'   => 96,
                'total_instances'        => 178,
                'created_by'             => $this->adminId,
                'created_at'             => $now->copy()->subMonths(12),
                'updated_at'             => $now,
            ],
            [
                'id'                     => $processDisputeResolution,
                'tenant_id'              => $this->tenantId,
                'name'                   => 'Dispute Resolution',
                'description'            => 'Customer complaint and dispute resolution workflow with investigation and resolution tracking.',
                'category'               => 'dispute_resolution',
                'steps'                  => json_encode([
                    ['step' => 1, 'name' => 'Log Complaint',  'type' => 'task',     'config' => ['assignee_role' => 'customer_service', 'sla_hours' => 1]],
                    ['step' => 2, 'name' => 'Investigation',  'type' => 'task',     'config' => ['assignee_role' => 'operations', 'sla_hours' => 48]],
                    ['step' => 3, 'name' => 'Resolution',     'type' => 'approval', 'config' => ['approver_role' => 'branch_manager', 'sla_hours' => 24]],
                ]),
                'is_active'              => true,
                'avg_completion_hours'   => 36,
                'total_instances'        => 92,
                'created_by'             => $this->adminId,
                'created_at'             => $now->copy()->subMonths(12),
                'updated_at'             => $now,
            ],
        ];

        DB::table('bpm_processes')->insertOrIgnore($processes);

        $instances = [
            // Account Opening — completed
            [
                'id'           => Str::uuid()->toString(),
                'process_id'   => $processAccountOpening,
                'tenant_id'    => $this->tenantId,
                'subject_type' => 'customer',
                'subject_id'   => 'CUST-2026-00451',
                'current_step' => 4,
                'status'       => 'completed',
                'step_history' => json_encode([
                    ['step' => 1, 'name' => 'KYC Check',        'action' => 'completed', 'user_id' => $this->complianceId, 'timestamp' => $now->copy()->subDays(5)->toIso8601String(), 'notes' => 'BVN verified, no sanctions match'],
                    ['step' => 2, 'name' => 'Document Upload',  'action' => 'completed', 'user_id' => 5,                   'timestamp' => $now->copy()->subDays(5)->addHours(2)->toIso8601String(), 'notes' => 'Passport, utility bill, BVN slip uploaded'],
                    ['step' => 3, 'name' => 'Approval',         'action' => 'approved',  'user_id' => 6,                   'timestamp' => $now->copy()->subDays(4)->toIso8601String(), 'notes' => 'Approved — all documents in order'],
                    ['step' => 4, 'name' => 'Account Creation', 'action' => 'completed', 'user_id' => $this->adminId,      'timestamp' => $now->copy()->subDays(4)->addHours(1)->toIso8601String(), 'notes' => 'Savings account 1001234567 created'],
                ]),
                'initiated_by' => 5,
                'completed_at' => $now->copy()->subDays(4)->addHours(1),
                'created_at'   => $now->copy()->subDays(5),
                'updated_at'   => $now->copy()->subDays(4),
            ],
            // Account Opening — in progress at step 2
            [
                'id'           => Str::uuid()->toString(),
                'process_id'   => $processAccountOpening,
                'tenant_id'    => $this->tenantId,
                'subject_type' => 'customer',
                'subject_id'   => 'CUST-2026-00478',
                'current_step' => 2,
                'status'       => 'active',
                'step_history' => json_encode([
                    ['step' => 1, 'name' => 'KYC Check', 'action' => 'completed', 'user_id' => $this->complianceId, 'timestamp' => $now->copy()->subDays(1)->toIso8601String(), 'notes' => 'BVN verified, PEP check clear'],
                ]),
                'initiated_by' => 5,
                'completed_at' => null,
                'created_at'   => $now->copy()->subDays(1),
                'updated_at'   => $now,
            ],
            // Loan Processing — completed
            [
                'id'           => Str::uuid()->toString(),
                'process_id'   => $processLoanProcessing,
                'tenant_id'    => $this->tenantId,
                'subject_type' => 'loan',
                'subject_id'   => 'LN-2026-00312',
                'current_step' => 5,
                'status'       => 'completed',
                'step_history' => json_encode([
                    ['step' => 1, 'name' => 'Application',  'action' => 'completed', 'user_id' => $this->loanOfficerId, 'timestamp' => $now->copy()->subDays(14)->toIso8601String(), 'notes' => 'SME loan application ₦5M received'],
                    ['step' => 2, 'name' => 'Credit Check',  'action' => 'completed', 'user_id' => $this->complianceId,  'timestamp' => $now->copy()->subDays(13)->toIso8601String(), 'notes' => 'Credit score 720, no defaults'],
                    ['step' => 3, 'name' => 'Appraisal',    'action' => 'completed', 'user_id' => $this->loanOfficerId, 'timestamp' => $now->copy()->subDays(11)->toIso8601String(), 'notes' => 'Collateral valued at ₦8M, LTV 62.5%'],
                    ['step' => 4, 'name' => 'Approval',     'action' => 'approved',  'user_id' => $this->adminId,       'timestamp' => $now->copy()->subDays(9)->toIso8601String(), 'notes' => 'Approved by credit committee'],
                    ['step' => 5, 'name' => 'Disbursement', 'action' => 'completed', 'user_id' => $this->adminId,       'timestamp' => $now->copy()->subDays(8)->toIso8601String(), 'notes' => 'Disbursed ₦5M to account 1001987654'],
                ]),
                'initiated_by' => $this->loanOfficerId,
                'completed_at' => $now->copy()->subDays(8),
                'created_at'   => $now->copy()->subDays(14),
                'updated_at'   => $now->copy()->subDays(8),
            ],
            // Loan Processing — in progress at step 3
            [
                'id'           => Str::uuid()->toString(),
                'process_id'   => $processLoanProcessing,
                'tenant_id'    => $this->tenantId,
                'subject_type' => 'loan',
                'subject_id'   => 'LN-2026-00335',
                'current_step' => 3,
                'status'       => 'active',
                'step_history' => json_encode([
                    ['step' => 1, 'name' => 'Application',  'action' => 'completed', 'user_id' => $this->loanOfficerId, 'timestamp' => $now->copy()->subDays(5)->toIso8601String(), 'notes' => 'Agricultural loan ₦2.5M for poultry expansion'],
                    ['step' => 2, 'name' => 'Credit Check',  'action' => 'completed', 'user_id' => $this->complianceId,  'timestamp' => $now->copy()->subDays(3)->toIso8601String(), 'notes' => 'Credit score 680, existing customer with good history'],
                ]),
                'initiated_by' => $this->loanOfficerId,
                'completed_at' => null,
                'created_at'   => $now->copy()->subDays(5),
                'updated_at'   => $now,
            ],
            // Dispute Resolution — in progress at step 2
            [
                'id'           => Str::uuid()->toString(),
                'process_id'   => $processDisputeResolution,
                'tenant_id'    => $this->tenantId,
                'subject_type' => 'customer',
                'subject_id'   => 'CUST-2026-00389',
                'current_step' => 2,
                'status'       => 'active',
                'step_history' => json_encode([
                    ['step' => 1, 'name' => 'Log Complaint', 'action' => 'completed', 'user_id' => 5, 'timestamp' => $now->copy()->subDays(2)->toIso8601String(), 'notes' => 'Customer reports unauthorized ATM withdrawal of ₦150,000'],
                ]),
                'initiated_by' => 5,
                'completed_at' => null,
                'created_at'   => $now->copy()->subDays(2),
                'updated_at'   => $now,
            ],
        ];

        DB::table('bpm_instances')->insertOrIgnore($instances);
        $this->command->info('  ✓ 3 BPM processes + 5 instances seeded');
    }
}
