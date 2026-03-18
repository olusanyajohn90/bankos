<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultTransactionLimitsSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = DB::table('tenants')->get(['id']);

        $defaults = [
            ['kyc_tier' => 'level_1', 'channel' => 'portal', 'transaction_type' => 'all', 'single_limit' => 50000.00,    'daily_limit' => 200000.00,    'monthly_limit' => null],
            ['kyc_tier' => 'level_1', 'channel' => 'ussd',   'transaction_type' => 'all', 'single_limit' => 20000.00,    'daily_limit' => 100000.00,    'monthly_limit' => null],
            ['kyc_tier' => 'level_1', 'channel' => 'agent',  'transaction_type' => 'all', 'single_limit' => 50000.00,    'daily_limit' => 200000.00,    'monthly_limit' => null],
            ['kyc_tier' => 'level_2', 'channel' => 'portal', 'transaction_type' => 'all', 'single_limit' => 500000.00,   'daily_limit' => 1000000.00,   'monthly_limit' => null],
            ['kyc_tier' => 'level_2', 'channel' => 'ussd',   'transaction_type' => 'all', 'single_limit' => 100000.00,   'daily_limit' => 500000.00,    'monthly_limit' => null],
            ['kyc_tier' => 'level_2', 'channel' => 'agent',  'transaction_type' => 'all', 'single_limit' => 300000.00,   'daily_limit' => 1000000.00,   'monthly_limit' => null],
            ['kyc_tier' => 'level_3', 'channel' => 'portal', 'transaction_type' => 'all', 'single_limit' => 5000000.00,  'daily_limit' => 10000000.00,  'monthly_limit' => null],
            ['kyc_tier' => 'level_3', 'channel' => 'ussd',   'transaction_type' => 'all', 'single_limit' => 500000.00,   'daily_limit' => 2000000.00,   'monthly_limit' => null],
            ['kyc_tier' => 'level_3', 'channel' => 'teller', 'transaction_type' => 'all', 'single_limit' => 5000000.00,  'daily_limit' => 10000000.00,  'monthly_limit' => null],
        ];

        $now = now();

        foreach ($tenants as $tenant) {
            foreach ($defaults as $def) {
                // Skip if already exists
                $exists = DB::table('transaction_limits')
                    ->where('tenant_id', $tenant->id)
                    ->where('kyc_tier', $def['kyc_tier'])
                    ->where('channel', $def['channel'])
                    ->where('transaction_type', $def['transaction_type'])
                    ->exists();

                if (! $exists) {
                    DB::table('transaction_limits')->insert(array_merge($def, [
                        'id'         => Str::uuid()->toString(),
                        'tenant_id'  => $tenant->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]));
                }
            }
        }

        $this->command->info('Default transaction limits seeded for ' . count($tenants) . ' tenant(s).');
    }
}
