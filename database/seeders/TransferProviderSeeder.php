<?php

namespace Database\Seeders;

use App\Services\Nip\NipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferProviderSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $this->command->info('Seeding transfer providers and bank list...');

        $now = now();

        $providers = [
            [
                'name'           => 'NIBSS NIP',
                'code'           => 'nip',
                'provider_class' => 'App\\Services\\TransferProviders\\NipProvider',
                'config'         => null,
                'is_active'      => true,
                'is_default'     => true,
                'max_amount'     => 5000000.00,
                'min_amount'     => 0,
                'flat_fee'       => 10.75,
                'percentage_fee' => 0,
                'fee_cap'        => null,
                'priority'       => 10,
            ],
            [
                'name'           => 'Paystack',
                'code'           => 'paystack',
                'provider_class' => 'App\\Services\\TransferProviders\\NipProvider',
                'config'         => json_encode(['api_key' => 'sk_test_placeholder', 'base_url' => 'https://api.paystack.co']),
                'is_active'      => true,
                'is_default'     => false,
                'max_amount'     => 10000000.00,
                'min_amount'     => 0,
                'flat_fee'       => 50.00,
                'percentage_fee' => 0.0050,
                'fee_cap'        => 2000.00,
                'priority'       => 5,
            ],
            [
                'name'           => 'Flutterwave',
                'code'           => 'flutterwave',
                'provider_class' => 'App\\Services\\TransferProviders\\NipProvider',
                'config'         => json_encode(['secret_key' => 'FLWSECK_TEST-placeholder', 'base_url' => 'https://api.flutterwave.com/v3']),
                'is_active'      => false,
                'is_default'     => false,
                'max_amount'     => null,
                'min_amount'     => 0,
                'flat_fee'       => 25.00,
                'percentage_fee' => 0.0050,
                'fee_cap'        => 3500.00,
                'priority'       => 3,
            ],
        ];

        foreach ($providers as $provider) {
            DB::table('transfer_providers')->updateOrInsert(
                ['tenant_id' => $this->tenantId, 'code' => $provider['code']],
                array_merge($provider, [
                    'id'         => Str::uuid()->toString(),
                    'tenant_id'  => $this->tenantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $this->command->info('Transfer providers seeded: ' . count($providers) . ' providers.');

        // ── Bank List ───────────────────────────────────────────────
        // NipService already has a comprehensive seedBankList() method.
        $nipService = app(NipService::class);
        $nipService->seedBankList();

        $this->command->info('Bank list seeded via NipService::seedBankList().');
    }
}
