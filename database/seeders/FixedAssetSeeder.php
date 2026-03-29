<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixedAssetSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $this->command->info('Seeding fixed asset categories and assets...');

        $now = now();

        // ── Categories ──────────────────────────────────────────────
        $categories = [
            ['name' => 'Furniture & Fittings',  'useful_life_years' => 10, 'residual_rate' =>   5.00, 'gl_asset_code' => '1700', 'gl_depreciation_code' => '6100'],
            ['name' => 'Computer Equipment',    'useful_life_years' =>  5, 'residual_rate' =>   5.00, 'gl_asset_code' => '1710', 'gl_depreciation_code' => '6110'],
            ['name' => 'Motor Vehicles',        'useful_life_years' =>  8, 'residual_rate' =>  10.00, 'gl_asset_code' => '1720', 'gl_depreciation_code' => '6120'],
            ['name' => 'Office Equipment',      'useful_life_years' =>  7, 'residual_rate' =>   5.00, 'gl_asset_code' => '1730', 'gl_depreciation_code' => '6130'],
            ['name' => 'Buildings',             'useful_life_years' => 50, 'residual_rate' =>  10.00, 'gl_asset_code' => '1740', 'gl_depreciation_code' => '6140'],
            ['name' => 'Land',                  'useful_life_years' => 99, 'residual_rate' => 100.00, 'gl_asset_code' => '1750', 'gl_depreciation_code' => null],
        ];

        $categoryIds = [];

        foreach ($categories as $cat) {
            $id = Str::uuid()->toString();
            $categoryIds[$cat['name']] = $id;

            DB::table('fixed_asset_categories')->insertOrIgnore([
                'id'                    => $id,
                'tenant_id'             => $this->tenantId,
                'name'                  => $cat['name'],
                'depreciation_method'   => 'straight_line',
                'useful_life_years'     => $cat['useful_life_years'],
                'residual_rate'         => $cat['residual_rate'],
                'gl_asset_code'         => $cat['gl_asset_code'],
                'gl_depreciation_code'  => $cat['gl_depreciation_code'],
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        }

        // ── Assets ──────────────────────────────────────────────────
        $assets = [
            // Furniture & Fittings
            ['cat' => 'Furniture & Fittings', 'name' => 'Executive Office Desks (x6)',    'tag' => 'FF-001', 'cost' => 900000,    'date' => '2024-03-15', 'accum' => 135000],
            ['cat' => 'Furniture & Fittings', 'name' => 'Conference Table (12-seater)',    'tag' => 'FF-002', 'cost' => 450000,    'date' => '2024-06-01', 'accum' => 52500],
            ['cat' => 'Furniture & Fittings', 'name' => 'Reception Chairs Set',           'tag' => 'FF-003', 'cost' => 280000,    'date' => '2024-06-01', 'accum' => 32667],
            ['cat' => 'Furniture & Fittings', 'name' => 'Steel Filing Cabinets (x10)',    'tag' => 'FF-004', 'cost' => 600000,    'date' => '2025-01-10', 'accum' => 28500],

            // Computer Equipment
            ['cat' => 'Computer Equipment', 'name' => 'HP EliteBook Laptops (x8)',        'tag' => 'CE-001', 'cost' => 3600000,   'date' => '2024-02-20', 'accum' => 1368000],
            ['cat' => 'Computer Equipment', 'name' => 'Dell PowerEdge Server',            'tag' => 'CE-002', 'cost' => 2800000,   'date' => '2024-04-10', 'accum' => 1008000],
            ['cat' => 'Computer Equipment', 'name' => 'APC Smart-UPS 3000VA (x3)',        'tag' => 'CE-003', 'cost' => 750000,    'date' => '2024-04-10', 'accum' => 270000],
            ['cat' => 'Computer Equipment', 'name' => 'Cisco Network Switches (x2)',      'tag' => 'CE-004', 'cost' => 480000,    'date' => '2025-03-01', 'accum' => 9600],

            // Motor Vehicles
            ['cat' => 'Motor Vehicles', 'name' => 'Toyota Hilux 2024',                    'tag' => 'MV-001', 'cost' => 25000000,  'date' => '2024-01-15', 'accum' => 5625000],
            ['cat' => 'Motor Vehicles', 'name' => 'Toyota Corolla 2023',                  'tag' => 'MV-002', 'cost' => 15000000,  'date' => '2024-05-01', 'accum' => 2812500],
            ['cat' => 'Motor Vehicles', 'name' => 'Bajaj Boxer Motorcycle',               'tag' => 'MV-003', 'cost' => 850000,    'date' => '2025-02-10', 'accum' => 10625],

            // Office Equipment
            ['cat' => 'Office Equipment', 'name' => 'Konica Minolta Photocopier',         'tag' => 'OE-001', 'cost' => 1200000,   'date' => '2024-03-20', 'accum' => 256429],
            ['cat' => 'Office Equipment', 'name' => 'Mikano 60KVA Generator',             'tag' => 'OE-002', 'cost' => 4500000,   'date' => '2024-01-05', 'accum' => 1285714],
            ['cat' => 'Office Equipment', 'name' => 'Hikvision CCTV System (16-Channel)', 'tag' => 'OE-003', 'cost' => 950000,    'date' => '2024-07-15', 'accum' => 135714],
            ['cat' => 'Office Equipment', 'name' => 'Chubb Vault Door & Safe',            'tag' => 'OE-004', 'cost' => 3500000,   'date' => '2024-01-05', 'accum' => 750000],

            // Buildings
            ['cat' => 'Buildings', 'name' => 'Head Office Building - Victoria Island',    'tag' => 'BD-001', 'cost' => 120000000, 'date' => '2024-01-01', 'accum' => 4320000],

            // Land
            ['cat' => 'Land', 'name' => 'Plot at Lekki Phase 2 (1200sqm)',                'tag' => 'LD-001', 'cost' => 85000000,  'date' => '2024-06-15', 'accum' => 0],
        ];

        // One fully depreciated item
        $fullyDepreciated = [
            'cat' => 'Computer Equipment', 'name' => 'Old HP ProLiant Server (Retired)', 'tag' => 'CE-099',
            'cost' => 1500000, 'date' => '2019-01-10', 'accum' => 1425000,
            'status' => 'fully_depreciated',
        ];

        foreach (array_merge($assets, [$fullyDepreciated]) as $asset) {
            $catName  = $asset['cat'];
            $catId    = $categoryIds[$catName];
            $catData  = collect($categories)->firstWhere('name', $catName);

            $residualValue = $asset['cost'] * ($catData['residual_rate'] / 100);
            $bookValue     = $asset['cost'] - $asset['accum'];
            $status        = $asset['status'] ?? 'active';

            DB::table('fixed_assets')->insertOrIgnore([
                'id'                        => Str::uuid()->toString(),
                'tenant_id'                 => $this->tenantId,
                'category_id'               => $catId,
                'name'                      => $asset['name'],
                'asset_tag'                 => $asset['tag'],
                'description'               => null,
                'purchase_date'             => $asset['date'],
                'purchase_cost'             => $asset['cost'],
                'current_book_value'        => $bookValue,
                'accumulated_depreciation'  => $asset['accum'],
                'depreciation_method'       => 'straight_line',
                'useful_life_years'         => $catData['useful_life_years'],
                'residual_value'            => $residualValue,
                'last_depreciation_date'    => $asset['accum'] > 0 ? '2026-03-01' : null,
                'status'                    => $status,
                'disposed_at'              => null,
                'disposal_value'           => null,
                'disposal_notes'           => null,
                'branch_id'                => null,
                'purchased_by'             => null,
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);
        }

        $this->command->info('Fixed assets seeded: 6 categories, ' . (count($assets) + 1) . ' assets.');
    }
}
