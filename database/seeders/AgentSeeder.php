<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentFloatTransaction;
use App\Models\AgentVisit;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('short_name', 'demo')->firstOrFail();
        $tid    = $tenant->id;

        $branches = Branch::where('tenant_id', $tid)->get()->keyBy('name');
        $ho       = $branches['Head Office']   ?? $branches->first();
        $ikeja    = $branches['Ikeja Branch']  ?? $ho;
        $abuja    = $branches['Abuja Branch']  ?? $ho;

        $agentData = [
            [
                'first_name'           => 'Emeka',
                'last_name'            => 'Okafor',
                'phone'                => '+2348031001001',
                'email'                => 'emeka.okafor@agents.demomfb.com',
                'bvn'                  => '22345678901',
                'nin'                  => '12345678901',
                'address'              => '12 Bode Thomas Street, Surulere, Lagos',
                'branch_id'            => $ho->id,
                'float_balance'        => 250000.00,
                'daily_cash_in_limit'  => 500000.00,
                'daily_cash_out_limit' => 200000.00,
                'daily_transfer_limit' => 150000.00,
                'commission_rate'      => 0.005,
                'home_latitude'        => 6.5003,
                'home_longitude'       => 3.3572,
                'total_commission_earned' => 18750.00,
                'status'               => 'active',
            ],
            [
                'first_name'           => 'Fatima',
                'last_name'            => 'Abdullahi',
                'phone'                => '+2348031002002',
                'email'                => 'fatima.a@agents.demomfb.com',
                'bvn'                  => '22345678902',
                'nin'                  => '12345678902',
                'address'              => '7 Allen Avenue, Ikeja, Lagos',
                'branch_id'            => $ikeja->id,
                'float_balance'        => 180000.00,
                'daily_cash_in_limit'  => 400000.00,
                'daily_cash_out_limit' => 150000.00,
                'daily_transfer_limit' => 100000.00,
                'commission_rate'      => 0.005,
                'home_latitude'        => 6.6018,
                'home_longitude'       => 3.3515,
                'total_commission_earned' => 12400.00,
                'status'               => 'active',
            ],
            [
                'first_name'           => 'Chukwudi',
                'last_name'            => 'Eze',
                'phone'                => '+2348031003003',
                'email'                => 'chukwudi.eze@agents.demomfb.com',
                'bvn'                  => '22345678903',
                'nin'                  => '12345678903',
                'address'              => '3 Wuse Zone 5, Abuja',
                'branch_id'            => $abuja->id,
                'float_balance'        => 320000.00,
                'daily_cash_in_limit'  => 600000.00,
                'daily_cash_out_limit' => 250000.00,
                'daily_transfer_limit' => 200000.00,
                'commission_rate'      => 0.0075,
                'home_latitude'        => 9.0579,
                'home_longitude'       => 7.4951,
                'total_commission_earned' => 31200.00,
                'status'               => 'active',
            ],
            [
                'first_name'           => 'Ngozi',
                'last_name'            => 'Nwosu',
                'phone'                => '+2348031004004',
                'email'                => 'ngozi.n@agents.demomfb.com',
                'bvn'                  => '22345678904',
                'nin'                  => '12345678904',
                'address'              => '45 Broad Street, Lagos Island',
                'branch_id'            => $ho->id,
                'float_balance'        => 95000.00,
                'daily_cash_in_limit'  => 300000.00,
                'daily_cash_out_limit' => 100000.00,
                'daily_transfer_limit' => 80000.00,
                'commission_rate'      => 0.005,
                'home_latitude'        => 6.4550,
                'home_longitude'       => 3.3841,
                'total_commission_earned' => 7800.00,
                'status'               => 'active',
            ],
            [
                'first_name'           => 'Yusuf',
                'last_name'            => 'Lawal',
                'phone'                => '+2348031005005',
                'email'                => 'yusuf.l@agents.demomfb.com',
                'bvn'                  => '22345678905',
                'nin'                  => '12345678905',
                'address'              => '18 Ring Road, Ibadan',
                'branch_id'            => $ikeja->id,
                'float_balance'        => 0.00,
                'daily_cash_in_limit'  => 200000.00,
                'daily_cash_out_limit' => 100000.00,
                'daily_transfer_limit' => 50000.00,
                'commission_rate'      => 0.005,
                'home_latitude'        => 7.3775,
                'home_longitude'       => 3.9470,
                'total_commission_earned' => 2100.00,
                'status'               => 'suspended',
            ],
        ];

        $createdAgents = [];
        foreach ($agentData as $data) {
            $agent = Agent::firstOrCreate(
                ['phone' => $data['phone']],
                array_merge($data, ['tenant_id' => $tid])
            );
            $createdAgents[] = $agent;
        }

        // ─── Float Transactions ──────────────────────────────────────────────
        $floatEntries = [
            // agent index, type (fund|debit|commission|reversal), amount, narration, days ago
            [0, 'fund',   250000, 'Initial float allocation',            30],
            [0, 'debit',   45000, 'Loan disbursement to customer',       20],
            [0, 'fund',    38000, 'Float replenishment — collections',   15],
            [0, 'fund',    80000, 'Float replenishment',                  7],
            [0, 'debit',   73000, 'Loan disbursement',                    3],

            [1, 'fund',   200000, 'Initial float allocation',            28],
            [1, 'debit',   60000, 'Loan disbursement',                   18],
            [1, 'fund',    55000, 'Float replenishment — collections',   12],
            [1, 'debit',   15000, 'Loan disbursement',                    5],

            [2, 'fund',   350000, 'Initial float allocation',            25],
            [2, 'debit',   80000, 'Loan disbursement',                   14],
            [2, 'fund',    95000, 'Float replenishment — collections',    8],
            [2, 'debit',   45000, 'Loan disbursement',                    2],

            [3, 'fund',   120000, 'Initial float allocation',            20],
            [3, 'debit',   25000, 'Loan disbursement',                   10],

            [4, 'fund',    50000, 'Initial float allocation',            45],
            [4, 'debit',   50000, 'Loan disbursement',                   40],
        ];

        foreach ($floatEntries as [$idx, $type, $amount, $narration, $daysAgo]) {
            if (!isset($createdAgents[$idx])) continue;
            AgentFloatTransaction::firstOrCreate(
                [
                    'agent_id' => $createdAgents[$idx]->id,
                    'narration' => $narration,
                    'created_at' => now()->subDays($daysAgo)->toDateTimeString(),
                ],
                [
                    'tenant_id'      => $tid,
                    'type'           => $type,
                    'amount'         => $amount,
                    'balance_after'  => 0, // simplified
                    'reference'      => 'FLT-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                    'narration'      => $narration,
                    'created_at'     => now()->subDays($daysAgo),
                    'updated_at'     => now()->subDays($daysAgo),
                ]
            );
        }

        // ─── Agent Visits ────────────────────────────────────────────────────
        $customers = Customer::where('tenant_id', $tid)->take(6)->get();
        if ($customers->count() >= 2) {
            $visitData = [
                [0, 0, 6.5010, 3.3580, 'collection',      'Collected monthly repayment',   35000, 12],
                [0, 1, 6.5020, 3.3590, 'account_opening', 'Opened new savings account',       0,  8],
                [1, 0, 6.6025, 3.3520, 'collection',      'Collected loan repayment',      28000,  6],
                [1, 2, 6.6030, 3.3530, 'kyc',             'KYC document verification',         0,  3],
                [2, 3, 9.0585, 7.4955, 'collection',      'Collected group loan payment',  60000, 10],
                [2, 4, 9.0590, 7.4960, 'account_opening', 'New customer onboarding',           0,  5],
                [3, 1, 6.4555, 3.3845, 'collection',      'Collected overdue payment',     15000,  4],
                [4, 0, 7.3780, 3.9475, 'collection',      'Last collection before suspend', 50000, 42],
            ];

            foreach ($visitData as [$agentIdx, $custIdx, $lat, $lng, $purpose, $notes, $amount, $daysAgo]) {
                if (!isset($createdAgents[$agentIdx])) continue;
                $customer = $customers->values()->get($custIdx % $customers->count());
                if (!$customer) continue;

                AgentVisit::firstOrCreate(
                    [
                        'agent_id'    => $createdAgents[$agentIdx]->id,
                        'customer_id' => $customer->id,
                        'visited_at'  => now()->subDays($daysAgo)->toDateTimeString(),
                    ],
                    [
                        'tenant_id'        => $tid,
                        'latitude'         => $lat,
                        'longitude'        => $lng,
                        'address_resolved' => 'Lagos, Nigeria',
                        'purpose'          => $purpose,
                        'notes'            => $notes,
                        'amount_collected' => $amount,
                        'visited_at'       => now()->subDays($daysAgo),
                    ]
                );
            }
        }
    }
}
