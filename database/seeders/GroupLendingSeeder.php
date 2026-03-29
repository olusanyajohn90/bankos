<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupLendingSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $this->command->info('Seeding centres, groups, and group members...');

        $now = now();

        // Get a loan officer for group assignment
        $officers = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('users.tenant_id', $this->tenantId)
            ->where('roles.name', 'loan_officer')
            ->pluck('users.id')
            ->values()
            ->toArray();

        if (empty($officers)) {
            $this->command->warn('No loan officers found. Groups will be created without officer assignment.');
        }

        // Get a branch for reference
        $branchId = DB::table('branches')
            ->where('tenant_id', $this->tenantId)
            ->value('id');

        // ── Centres ─────────────────────────────────────────────────
        $centres = [
            [
                'name'             => 'Ikeja Market Centre',
                'code'             => 'CTR-IKJ',
                'meeting_location' => 'Ikeja Computer Village Community Hall',
                'meeting_day'      => 'monday',
                'meeting_time'     => '09:00:00',
            ],
            [
                'name'             => 'Surulere Women Centre',
                'code'             => 'CTR-SRL',
                'meeting_location' => 'Surulere Town Hall, Adeniran Ogunsanya',
                'meeting_day'      => 'wednesday',
                'meeting_time'     => '10:00:00',
            ],
        ];

        $centreIds = [];
        foreach ($centres as $centre) {
            $existing = DB::table('centres')
                ->where('tenant_id', $this->tenantId)
                ->where('code', $centre['code'])
                ->value('id');

            if ($existing) {
                $centreIds[] = $existing;
                continue;
            }

            $id = Str::uuid()->toString();
            $centreIds[] = $id;

            DB::table('centres')->insert(array_merge($centre, [
                'id'         => $id,
                'tenant_id'  => $this->tenantId,
                'branch_id'  => $branchId,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ── Groups ──────────────────────────────────────────────────
        $groups = [
            ['name' => 'Iya Alaro Traders',      'code' => 'GRP-IAT', 'centre_index' => 0, 'solidarity' => true,  'notes' => 'Market women trading in foodstuffs at Ikeja market.'],
            ['name' => 'Balogun Market Women',    'code' => 'GRP-BMW', 'centre_index' => 0, 'solidarity' => true,  'notes' => 'Textile and fashion traders from Balogun market.'],
            ['name' => 'Surulere Artisans',       'code' => 'GRP-SAR', 'centre_index' => 1, 'solidarity' => false, 'notes' => 'Skilled artisans including tailors, hairdressers, and shoemakers.'],
            ['name' => 'Tech Hub Cooperative',    'code' => 'GRP-THC', 'centre_index' => 1, 'solidarity' => true,  'notes' => 'Small-scale tech entrepreneurs and phone repair technicians.'],
        ];

        $groupIds = [];
        foreach ($groups as $i => $group) {
            $existing = DB::table('groups')
                ->where('tenant_id', $this->tenantId)
                ->where('code', $group['code'])
                ->value('id');

            if ($existing) {
                $groupIds[] = $existing;
                continue;
            }

            $id = Str::uuid()->toString();
            $groupIds[] = $id;

            $officerId = !empty($officers) ? $officers[$i % count($officers)] : null;

            DB::table('groups')->insert([
                'id'                  => $id,
                'tenant_id'           => $this->tenantId,
                'centre_id'           => $centreIds[$group['centre_index']],
                'branch_id'           => $branchId,
                'loan_officer_id'     => $officerId,
                'name'                => $group['name'],
                'code'                => $group['code'],
                'solidarity_guarantee'=> $group['solidarity'],
                'status'              => 'active',
                'notes'               => $group['notes'],
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        // ── Group Members ───────────────────────────────────────────
        // Get existing customers to assign as group members
        $customerIds = DB::table('customers')
            ->where('tenant_id', $this->tenantId)
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();

        if (count($customerIds) < 16) {
            $this->command->warn('Not enough customers (' . count($customerIds) . ') to fully populate 4 groups. Using available customers.');
        }

        // Distribute customers across groups: ~4-6 per group
        $memberCounts  = [5, 6, 4, 5]; // target members per group
        $customerIndex = 0;
        $totalMembers  = 0;

        foreach ($groupIds as $gi => $groupId) {
            $count = min($memberCounts[$gi], count($customerIds) - $customerIndex);

            for ($m = 0; $m < $count; $m++) {
                if ($customerIndex >= count($customerIds)) {
                    break 2;
                }

                $customerId = $customerIds[$customerIndex];
                $customerIndex++;

                // Assign roles: first member is leader, second is treasurer, rest are members
                $role = match ($m) {
                    0 => 'leader',
                    1 => 'treasurer',
                    default => 'member',
                };

                DB::table('group_members')->insertOrIgnore([
                    'id'          => Str::uuid()->toString(),
                    'tenant_id'   => $this->tenantId,
                    'group_id'    => $groupId,
                    'customer_id' => $customerId,
                    'role'        => $role,
                    'joined_at'   => now()->subDays(rand(30, 180))->toDateString(),
                    'status'      => 'active',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);

                $totalMembers++;
            }
        }

        $this->command->info("Seeded 2 centres, 4 groups, and {$totalMembers} group members.");
    }
}
