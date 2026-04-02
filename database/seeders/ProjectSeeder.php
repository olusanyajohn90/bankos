<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Find a tenant and some users
        $tenant = DB::table('tenants')->first();
        if (!$tenant) {
            $this->command->warn('No tenant found. Skipping ProjectSeeder.');
            return;
        }

        $users = DB::table('users')->where('tenant_id', $tenant->id)->limit(6)->pluck('id')->toArray();
        if (empty($users)) {
            $this->command->warn('No users found. Skipping ProjectSeeder.');
            return;
        }

        $ownerId = $users[0];
        $now = Carbon::now();

        // ── Projects ────────────────────────────────────────────────────
        $projects = [
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => $tenant->id,
                'name'        => 'Core Banking Platform v2',
                'code'        => 'CBP',
                'description' => 'Major upgrade of the core banking engine including real-time settlement, multi-currency support, and API gateway modernization.',
                'color'       => '#3B82F6',
                'owner_id'    => $ownerId,
                'status'      => 'active',
                'visibility'  => 'public',
                'start_date'  => '2026-01-15',
                'end_date'    => '2026-06-30',
                'progress'    => 35,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => $tenant->id,
                'name'        => 'Branch Expansion Initiative',
                'code'        => 'BEI',
                'description' => 'Opening 5 new branches across Lagos, Abuja, and Port Harcourt with full digital readiness.',
                'color'       => '#10B981',
                'owner_id'    => $ownerId,
                'status'      => 'active',
                'visibility'  => 'public',
                'start_date'  => '2026-02-01',
                'end_date'    => '2026-08-31',
                'progress'    => 20,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'tenant_id'   => $tenant->id,
                'name'        => 'Digital Lending Launch',
                'code'        => 'DLL',
                'description' => 'Launch instant digital lending product with AI credit scoring, e-mandate, and automated disbursement.',
                'color'       => '#F59E0B',
                'owner_id'    => $ownerId,
                'status'      => 'active',
                'visibility'  => 'public',
                'start_date'  => '2026-03-01',
                'end_date'    => '2026-07-15',
                'progress'    => 15,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        DB::table('pm_projects')->insert($projects);

        // ── Labels (shared across projects) ─────────────────────────────
        $labelDefs = [
            ['name' => 'Bug',           'color' => '#EF4444'],
            ['name' => 'Feature',       'color' => '#3B82F6'],
            ['name' => 'Enhancement',   'color' => '#8B5CF6'],
            ['name' => 'Documentation', 'color' => '#6B7280'],
            ['name' => 'Urgent',        'color' => '#DC2626'],
            ['name' => 'Design',        'color' => '#EC4899'],
        ];

        $labelsByProject = [];
        foreach ($projects as $proj) {
            $projectLabels = [];
            foreach ($labelDefs as $lbl) {
                $lid = Str::uuid()->toString();
                $projectLabels[$lbl['name']] = $lid;
                DB::table('pm_labels')->insert([
                    'id'         => $lid,
                    'project_id' => $proj['id'],
                    'name'       => $lbl['name'],
                    'color'      => $lbl['color'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $labelsByProject[$proj['id']] = $projectLabels;
        }

        // ── Boards & Columns ────────────────────────────────────────────
        $defaultCols = [
            ['name' => 'To Do',      'color' => '#94A3B8', 'position' => 0, 'is_done_column' => false],
            ['name' => 'In Progress','color' => '#3B82F6', 'position' => 1, 'is_done_column' => false],
            ['name' => 'Review',     'color' => '#F59E0B', 'position' => 2, 'is_done_column' => false],
            ['name' => 'Done',       'color' => '#10B981', 'position' => 3, 'is_done_column' => true],
        ];

        $columnsByProject = [];
        foreach ($projects as $proj) {
            $boardId = Str::uuid()->toString();
            DB::table('pm_boards')->insert([
                'id'         => $boardId,
                'project_id' => $proj['id'],
                'name'       => 'Main Board',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $cols = [];
            foreach ($defaultCols as $col) {
                $cid = Str::uuid()->toString();
                $cols[$col['name']] = $cid;
                DB::table('pm_columns')->insert(array_merge($col, [
                    'id'         => $cid,
                    'board_id'   => $boardId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
            $columnsByProject[$proj['id']] = $cols;
        }

        // ── Project Members ─────────────────────────────────────────────
        foreach ($projects as $proj) {
            DB::table('pm_project_members')->insert([
                'project_id' => $proj['id'],
                'user_id'    => $ownerId,
                'role'       => 'owner',
                'joined_at'  => $now,
            ]);
            foreach (array_slice($users, 1, 4) as $uid) {
                DB::table('pm_project_members')->insert([
                    'project_id' => $proj['id'],
                    'user_id'    => $uid,
                    'role'       => 'member',
                    'joined_at'  => $now,
                ]);
            }
        }

        // ── Sprints (2 per project) ─────────────────────────────────────
        $sprintsByProject = [];
        foreach ($projects as $proj) {
            $s1 = Str::uuid()->toString();
            $s2 = Str::uuid()->toString();
            DB::table('pm_sprints')->insert([
                [
                    'id'         => $s1,
                    'project_id' => $proj['id'],
                    'name'       => 'Sprint 1',
                    'goal'       => 'Complete foundational setup and core integrations',
                    'start_date' => '2026-03-01',
                    'end_date'   => '2026-03-14',
                    'status'     => 'completed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id'         => $s2,
                    'project_id' => $proj['id'],
                    'name'       => 'Sprint 2',
                    'goal'       => 'User-facing features and testing',
                    'start_date' => '2026-03-15',
                    'end_date'   => '2026-03-28',
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
            $sprintsByProject[$proj['id']] = [$s1, $s2];
        }

        // ── Tasks ───────────────────────────────────────────────────────
        $taskDefs = [
            // Core Banking Platform v2
            ['project' => 0, 'col' => 'Done', 'title' => 'Set up CI/CD pipeline for v2 branch', 'priority' => 'high', 'sp' => 5, 'labels' => ['Feature'], 'completed' => true],
            ['project' => 0, 'col' => 'Done', 'title' => 'Design new database schema for multi-currency', 'priority' => 'critical', 'sp' => 8, 'labels' => ['Feature','Design'], 'completed' => true],
            ['project' => 0, 'col' => 'Done', 'title' => 'Implement real-time transaction settlement engine', 'priority' => 'critical', 'sp' => 13, 'labels' => ['Feature'], 'completed' => true],
            ['project' => 0, 'col' => 'Review', 'title' => 'API gateway migration to new auth system', 'priority' => 'high', 'sp' => 8, 'labels' => ['Feature']],
            ['project' => 0, 'col' => 'Review', 'title' => 'Write integration tests for settlement module', 'priority' => 'medium', 'sp' => 5, 'labels' => ['Enhancement']],
            ['project' => 0, 'col' => 'In Progress', 'title' => 'Implement NIBSS NIP 2.0 adapter', 'priority' => 'high', 'sp' => 8, 'labels' => ['Feature']],
            ['project' => 0, 'col' => 'In Progress', 'title' => 'Build currency conversion rate engine', 'priority' => 'medium', 'sp' => 5, 'labels' => ['Feature']],
            ['project' => 0, 'col' => 'To Do', 'title' => 'Fix rounding errors in interest calculation', 'priority' => 'critical', 'sp' => 3, 'labels' => ['Bug','Urgent']],
            ['project' => 0, 'col' => 'To Do', 'title' => 'API documentation for external partners', 'priority' => 'low', 'sp' => 3, 'labels' => ['Documentation']],
            ['project' => 0, 'col' => 'To Do', 'title' => 'Performance load testing for 10k TPS', 'priority' => 'high', 'sp' => 5, 'labels' => ['Enhancement']],

            // Branch Expansion Initiative
            ['project' => 1, 'col' => 'Done', 'title' => 'Secure lease for Lekki Phase 1 branch', 'priority' => 'high', 'sp' => 3, 'labels' => ['Feature'], 'completed' => true],
            ['project' => 1, 'col' => 'Done', 'title' => 'Procure ATMs and POS terminals for new branches', 'priority' => 'high', 'sp' => 5, 'labels' => ['Feature'], 'completed' => true],
            ['project' => 1, 'col' => 'Review', 'title' => 'Network infrastructure design for Abuja branch', 'priority' => 'medium', 'sp' => 5, 'labels' => ['Design']],
            ['project' => 1, 'col' => 'In Progress', 'title' => 'Staff recruitment for Port Harcourt branch', 'priority' => 'high', 'sp' => 5, 'labels' => ['Feature']],
            ['project' => 1, 'col' => 'In Progress', 'title' => 'Interior design approval for all branches', 'priority' => 'medium', 'sp' => 3, 'labels' => ['Design']],
            ['project' => 1, 'col' => 'To Do', 'title' => 'CBN licensing application for new branches', 'priority' => 'critical', 'sp' => 8, 'labels' => ['Feature','Urgent']],
            ['project' => 1, 'col' => 'To Do', 'title' => 'Digital signage setup and branding materials', 'priority' => 'low', 'sp' => 2, 'labels' => ['Design']],
            ['project' => 1, 'col' => 'To Do', 'title' => 'Training program for new branch staff', 'priority' => 'medium', 'sp' => 5, 'labels' => ['Documentation']],

            // Digital Lending Launch
            ['project' => 2, 'col' => 'Done', 'title' => 'Define loan product parameters and interest tiers', 'priority' => 'critical', 'sp' => 5, 'labels' => ['Feature'], 'completed' => true],
            ['project' => 2, 'col' => 'Review', 'title' => 'AI credit scoring model v1 training complete', 'priority' => 'high', 'sp' => 13, 'labels' => ['Feature']],
            ['project' => 2, 'col' => 'In Progress', 'title' => 'Build e-mandate integration with NIBSS', 'priority' => 'high', 'sp' => 8, 'labels' => ['Feature']],
            ['project' => 2, 'col' => 'In Progress', 'title' => 'Mobile app UI for loan application flow', 'priority' => 'medium', 'sp' => 8, 'labels' => ['Design','Feature']],
            ['project' => 2, 'col' => 'In Progress', 'title' => 'Automated disbursement via NIP', 'priority' => 'high', 'sp' => 5, 'labels' => ['Feature']],
            ['project' => 2, 'col' => 'To Do', 'title' => 'Bug: OTP not delivered for loan verification', 'priority' => 'critical', 'sp' => 2, 'labels' => ['Bug','Urgent']],
            ['project' => 2, 'col' => 'To Do', 'title' => 'Regulatory compliance review for digital lending', 'priority' => 'high', 'sp' => 5, 'labels' => ['Documentation']],
            ['project' => 2, 'col' => 'To Do', 'title' => 'Build repayment reminder notification system', 'priority' => 'medium', 'sp' => 3, 'labels' => ['Enhancement']],
            ['project' => 2, 'col' => 'To Do', 'title' => 'Stress test lending engine with 50k concurrent applications', 'priority' => 'medium', 'sp' => 5, 'labels' => ['Enhancement']],
        ];

        $taskNum = [];
        foreach ($taskDefs as $td) {
            $proj = $projects[$td['project']];
            $projId = $proj['id'];
            if (!isset($taskNum[$projId])) $taskNum[$projId] = 0;
            $taskNum[$projId]++;

            $colId = $columnsByProject[$projId][$td['col']];
            $assignee = $users[array_rand($users)];
            $labelIds = array_map(fn($n) => $labelsByProject[$projId][$n] ?? null, $td['labels']);
            $labelIds = array_filter($labelIds);

            $taskId = Str::uuid()->toString();
            $dueDate = Carbon::now()->addDays(rand(3, 30))->toDateString();

            DB::table('pm_tasks')->insert([
                'id'              => $taskId,
                'project_id'      => $projId,
                'column_id'       => $colId,
                'parent_id'       => null,
                'task_number'     => $taskNum[$projId],
                'title'           => $td['title'],
                'description'     => null,
                'priority'        => $td['priority'],
                'status'          => isset($td['completed']) ? 'done' : ($td['col'] === 'In Progress' ? 'in_progress' : ($td['col'] === 'Review' ? 'review' : 'open')),
                'assignee_id'     => $assignee,
                'reporter_id'     => $ownerId,
                'due_date'        => $dueDate,
                'start_date'      => null,
                'estimated_hours' => rand(4, 40),
                'logged_hours'    => isset($td['completed']) ? rand(8, 30) : rand(0, 15),
                'position'        => $taskNum[$projId],
                'labels'          => json_encode(array_values($labelIds)),
                'sprint_id'       => $sprintsByProject[$projId][isset($td['completed']) ? 0 : 1],
                'story_points'    => $td['sp'],
                'completed_at'    => isset($td['completed']) ? $now : null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // Activity: created
            DB::table('pm_task_activities')->insert([
                'task_id'    => $taskId,
                'user_id'    => $ownerId,
                'action'     => 'created',
                'old_value'  => null,
                'new_value'  => $td['title'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Add a comment to some tasks
            if (rand(0, 2) === 0) {
                $comments = [
                    'This is progressing well. Let me know if you need help.',
                    'We should align this with the regulatory team before proceeding.',
                    'Updated the requirements doc. Please review the latest version.',
                    'Blocked by vendor delay. Escalating to management.',
                    'Testing looks good. Ready for UAT.',
                    'Nice work on this! Moving to review.',
                ];
                DB::table('pm_task_comments')->insert([
                    'task_id'    => $taskId,
                    'user_id'    => $users[array_rand($users)],
                    'body'       => $comments[array_rand($comments)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Time entries for some tasks
            if (rand(0, 1) === 0) {
                DB::table('pm_time_entries')->insert([
                    'task_id'     => $taskId,
                    'user_id'     => $assignee,
                    'hours'       => rand(1, 8),
                    'note'        => 'Worked on implementation',
                    'logged_date' => Carbon::now()->subDays(rand(1, 10))->toDateString(),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        $this->command->info('ProjectSeeder: 3 projects, 27 tasks, labels, sprints, comments & time entries created.');
    }
}
