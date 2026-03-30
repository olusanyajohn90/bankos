<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalendarSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    // Pinned calendar UUIDs for cross-referencing
    private string $myCalendarId       = 'ca1e0001-0000-4000-a000-000000000001';
    private string $teamCalendarId     = 'ca1e0001-0000-4000-a000-000000000002';
    private string $branchMeetingsId   = 'ca1e0001-0000-4000-a000-000000000003';
    private string $complianceCalId    = 'ca1e0001-0000-4000-a000-000000000004';
    private string $holidaysCalId      = 'ca1e0001-0000-4000-a000-000000000005';

    public function run(): void
    {
        $now = Carbon::now();

        $this->seedCalendars($now);
        $this->seedEvents($now);
    }

    // ── Calendars ───────────────────────────────────────────────────────
    private function seedCalendars(Carbon $now): void
    {
        $calendars = [
            [
                'id'         => $this->myCalendarId,
                'tenant_id'  => $this->tenantId,
                'name'       => 'My Calendar',
                'color'      => '#3B82F6',
                'type'       => 'personal',
                'owner_id'   => 3,
                'is_default' => true,
                'created_at' => $now->copy()->subMonths(3)->toDateTimeString(),
                'updated_at' => $now->copy()->subMonths(3)->toDateTimeString(),
            ],
            [
                'id'         => $this->teamCalendarId,
                'tenant_id'  => $this->tenantId,
                'name'       => 'Team Calendar',
                'color'      => '#8B5CF6',
                'type'       => 'shared',
                'owner_id'   => 3,
                'is_default' => false,
                'created_at' => $now->copy()->subMonths(3)->toDateTimeString(),
                'updated_at' => $now->copy()->subMonths(3)->toDateTimeString(),
            ],
            [
                'id'         => $this->branchMeetingsId,
                'tenant_id'  => $this->tenantId,
                'name'       => 'Branch Meetings',
                'color'      => '#10B981',
                'type'       => 'shared',
                'owner_id'   => 5,
                'is_default' => false,
                'created_at' => $now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at' => $now->copy()->subMonths(2)->toDateTimeString(),
            ],
            [
                'id'         => $this->complianceCalId,
                'tenant_id'  => $this->tenantId,
                'name'       => 'Compliance Calendar',
                'color'      => '#EF4444',
                'type'       => 'shared',
                'owner_id'   => 2,
                'is_default' => false,
                'created_at' => $now->copy()->subMonths(3)->toDateTimeString(),
                'updated_at' => $now->copy()->subMonths(3)->toDateTimeString(),
            ],
            [
                'id'         => $this->holidaysCalId,
                'tenant_id'  => $this->tenantId,
                'name'       => 'Holidays & Leave',
                'color'      => '#F59E0B',
                'type'       => 'system',
                'owner_id'   => null,
                'is_default' => false,
                'created_at' => $now->copy()->subMonths(6)->toDateTimeString(),
                'updated_at' => $now->copy()->subMonths(6)->toDateTimeString(),
            ],
        ];

        foreach ($calendars as $cal) {
            DB::table('calendars')->insertOrIgnore($cal);
        }
    }

    // ── Events, Attendees & Reminders ───────────────────────────────────
    private function seedEvents(Carbon $now): void
    {
        // Look up chat_task IDs for synced events
        $reviewQ1Task = DB::table('chat_tasks')
            ->where('tenant_id', $this->tenantId)
            ->where('title', 'Review Q1 loan applications')
            ->first();

        $ndicTask = DB::table('chat_tasks')
            ->where('tenant_id', $this->tenantId)
            ->where('title', 'Prepare NDIC monthly report')
            ->first();

        // ── Manual events ───────────────────────────────────────────────

        $evt1 = Str::uuid()->toString(); // Board of Directors Meeting
        $evt2 = Str::uuid()->toString(); // Weekly Management Standup
        $evt3 = Str::uuid()->toString(); // CBN Quarterly Report Due
        $evt4 = Str::uuid()->toString(); // Ikeja Branch Audit
        $evt5 = Str::uuid()->toString(); // Staff Training: AML Procedures
        $evt6 = Str::uuid()->toString(); // New Product Launch Planning
        $evt7 = Str::uuid()->toString(); // Shareholder AGM
        $evt8 = Str::uuid()->toString(); // IT Systems Maintenance Window
        $evt9 = Str::uuid()->toString(); // Customer Appreciation Day
        $evt10 = Str::uuid()->toString(); // NDIC Returns Deadline

        // ── Synced / holiday events
        $evt11 = Str::uuid()->toString(); // Review Q1 loan applications (task)
        $evt12 = Str::uuid()->toString(); // Prepare NDIC monthly report (task)
        $evt13 = Str::uuid()->toString(); // Easter Monday
        $evt14 = Str::uuid()->toString(); // Workers' Day
        $evt15 = Str::uuid()->toString(); // Eid-el-Fitr

        $events = [
            // 1. Board of Directors Meeting
            [
                'id'              => $evt1,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'Board of Directors Meeting',
                'description'     => 'Monthly board meeting to review financial performance, risk management updates, and strategic initiatives.',
                'type'            => 'meeting',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-04-01 10:00:00',
                'end_at'          => '2026-04-01 12:00:00',
                'location'        => 'Board Room',
                'is_recurring'    => true,
                'recurrence_rule' => 'FREQ=MONTHLY;BYDAY=1WE',
                'recurrence_end'  => '2026-12-31 00:00:00',
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(4)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(4)->toDateTimeString(),
            ],
            // 2. Weekly Management Standup
            [
                'id'              => $evt2,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'Weekly Management Standup',
                'description'     => 'Quick sync on weekly priorities, blockers, and key metrics across all departments.',
                'type'            => 'meeting',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-03-30 09:00:00',
                'end_at'          => '2026-03-30 09:30:00',
                'location'        => null,
                'is_recurring'    => true,
                'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO',
                'recurrence_end'  => '2026-12-31 00:00:00',
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at'      => $now->copy()->subMonths(2)->toDateTimeString(),
            ],
            // 3. CBN Quarterly Report Due
            [
                'id'              => $evt3,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->complianceCalId,
                'title'           => 'CBN Quarterly Report Due',
                'description'     => 'Deadline for submission of Q1 2026 prudential returns to the Central Bank of Nigeria.',
                'type'            => 'reminder',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => true,
                'start_at'        => '2026-04-15 00:00:00',
                'end_at'          => '2026-04-15 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 2,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(2)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(2)->toDateTimeString(),
            ],
            // 4. Ikeja Branch Audit
            [
                'id'              => $evt4,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->branchMeetingsId,
                'title'           => 'Ikeja Branch Audit',
                'description'     => 'Scheduled internal audit of Ikeja branch operations, cash handling, and compliance procedures.',
                'type'            => 'appointment',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-04-03 14:00:00',
                'end_at'          => '2026-04-03 17:00:00',
                'location'        => 'Ikeja Branch',
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(1)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(1)->toDateTimeString(),
            ],
            // 5. Staff Training: AML Procedures
            [
                'id'              => $evt5,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'Staff Training: AML Procedures',
                'description'     => 'Mandatory anti-money laundering training session for all loan officers and customer-facing staff.',
                'type'            => 'meeting',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-04-07 09:00:00',
                'end_at'          => '2026-04-07 12:00:00',
                'location'        => 'Training Room',
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subDays(5)->toDateTimeString(),
                'updated_at'      => $now->copy()->subDays(5)->toDateTimeString(),
            ],
            // 6. New Product Launch Planning
            [
                'id'              => $evt6,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->myCalendarId,
                'title'           => 'New Product Launch Planning',
                'description'     => 'Strategy session for the upcoming savings product launch targeting SME customers.',
                'type'            => 'meeting',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-03-31 15:00:00',
                'end_at'          => '2026-03-31 16:30:00',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'private',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subDays(3)->toDateTimeString(),
                'updated_at'      => $now->copy()->subDays(3)->toDateTimeString(),
            ],
            // 7. Shareholder AGM
            [
                'id'              => $evt7,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'Shareholder AGM',
                'description'     => 'Annual General Meeting of shareholders. Presentation of 2025 financials and 2026 strategy.',
                'type'            => 'meeting',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => false,
                'start_at'        => '2026-04-25 10:00:00',
                'end_at'          => '2026-04-25 13:00:00',
                'location'        => 'Conference Hall',
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(3)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(3)->toDateTimeString(),
            ],
            // 8. IT Systems Maintenance Window
            [
                'id'              => $evt8,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'IT Systems Maintenance Window',
                'description'     => 'Scheduled downtime for core banking system upgrades and security patches. All branches affected.',
                'type'            => 'custom',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => '#F97316',
                'all_day'         => false,
                'start_at'        => '2026-04-05 22:00:00',
                'end_at'          => '2026-04-06 02:00:00',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subDays(2)->toDateTimeString(),
                'updated_at'      => $now->copy()->subDays(2)->toDateTimeString(),
            ],
            // 9. Customer Appreciation Day
            [
                'id'              => $evt9,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->teamCalendarId,
                'title'           => 'Customer Appreciation Day',
                'description'     => 'Bank-wide customer appreciation event across all branches with gifts and refreshments.',
                'type'            => 'custom',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => '#EC4899',
                'all_day'         => true,
                'start_at'        => '2026-04-10 00:00:00',
                'end_at'          => '2026-04-10 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(1)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(1)->toDateTimeString(),
            ],
            // 10. NDIC Returns Deadline
            [
                'id'              => $evt10,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->complianceCalId,
                'title'           => 'NDIC Returns Deadline',
                'description'     => 'Deadline for monthly NDIC returns submission including deposit insurance premiums and risk data.',
                'type'            => 'reminder',
                'source'          => 'manual',
                'source_id'       => null,
                'color'           => null,
                'all_day'         => true,
                'start_at'        => '2026-04-20 00:00:00',
                'end_at'          => '2026-04-20 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 2,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subWeeks(2)->toDateTimeString(),
                'updated_at'      => $now->copy()->subWeeks(2)->toDateTimeString(),
            ],

            // ── Synced from chat_tasks ──────────────────────────────────

            // 11. Review Q1 loan applications (from chat_task)
            [
                'id'              => $evt11,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->myCalendarId,
                'title'           => 'Review Q1 loan applications',
                'description'     => 'Go through all pending Q1 loan applications and prepare summary report with recommendations for the credit committee.',
                'type'            => 'task',
                'source'          => 'chat_task',
                'source_id'       => $reviewQ1Task?->id,
                'color'           => null,
                'all_day'         => true,
                'start_at'        => $reviewQ1Task
                    ? Carbon::parse($reviewQ1Task->due_date)->startOfDay()->toDateTimeString()
                    : $now->copy()->addDay()->startOfDay()->toDateTimeString(),
                'end_at'          => $reviewQ1Task
                    ? Carbon::parse($reviewQ1Task->due_date)->endOfDay()->toDateTimeString()
                    : $now->copy()->addDay()->endOfDay()->toDateTimeString(),
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'private',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subDays(4)->toDateTimeString(),
                'updated_at'      => $now->copy()->subDays(4)->toDateTimeString(),
            ],
            // 12. Prepare NDIC monthly report (from chat_task)
            [
                'id'              => $evt12,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->complianceCalId,
                'title'           => 'Prepare NDIC monthly report',
                'description'     => 'Compile monthly returns for NDIC submission including deposit insurance premiums and risk assessment data.',
                'type'            => 'task',
                'source'          => 'chat_task',
                'source_id'       => $ndicTask?->id,
                'color'           => null,
                'all_day'         => true,
                'start_at'        => $ndicTask
                    ? Carbon::parse($ndicTask->due_date)->startOfDay()->toDateTimeString()
                    : $now->copy()->endOfWeek()->startOfDay()->toDateTimeString(),
                'end_at'          => $ndicTask
                    ? Carbon::parse($ndicTask->due_date)->endOfDay()->toDateTimeString()
                    : $now->copy()->endOfWeek()->endOfDay()->toDateTimeString(),
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subDays(3)->toDateTimeString(),
                'updated_at'      => $now->copy()->subDays(3)->toDateTimeString(),
            ],

            // ── Holidays ────────────────────────────────────────────────

            // 13. Easter Monday
            [
                'id'              => $evt13,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->holidaysCalId,
                'title'           => 'Easter Monday',
                'description'     => 'Public holiday — all branches closed.',
                'type'            => 'holiday',
                'source'          => 'holiday',
                'source_id'       => null,
                'color'           => '#F59E0B',
                'all_day'         => true,
                'start_at'        => '2026-04-06 00:00:00',
                'end_at'          => '2026-04-06 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
                'updated_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
            ],
            // 14. Workers' Day
            [
                'id'              => $evt14,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->holidaysCalId,
                'title'           => "Workers' Day",
                'description'     => 'International Workers\' Day — public holiday, all branches closed.',
                'type'            => 'holiday',
                'source'          => 'holiday',
                'source_id'       => null,
                'color'           => '#F59E0B',
                'all_day'         => true,
                'start_at'        => '2026-05-01 00:00:00',
                'end_at'          => '2026-05-01 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
                'updated_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
            ],
            // 15. Eid-el-Fitr
            [
                'id'              => $evt15,
                'tenant_id'       => $this->tenantId,
                'calendar_id'     => $this->holidaysCalId,
                'title'           => 'Eid-el-Fitr',
                'description'     => 'Eid-el-Fitr public holiday — all branches closed.',
                'type'            => 'holiday',
                'source'          => 'holiday',
                'source_id'       => null,
                'color'           => '#F59E0B',
                'all_day'         => true,
                'start_at'        => '2026-04-01 00:00:00',
                'end_at'          => '2026-04-01 23:59:59',
                'location'        => null,
                'is_recurring'    => false,
                'recurrence_rule' => null,
                'recurrence_end'  => null,
                'created_by'      => 3,
                'visibility'      => 'public',
                'status'          => 'confirmed',
                'created_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
                'updated_at'      => $now->copy()->subMonths(6)->toDateTimeString(),
            ],
        ];

        foreach ($events as $event) {
            DB::table('calendar_events')->insertOrIgnore($event);
        }

        // ── Attendees ───────────────────────────────────────────────────
        $attendees = [
            // 1. Board of Directors Meeting — admin, compliance, branch manager
            ['event_id' => $evt1, 'user_id' => 3, 'status' => 'accepted'],
            ['event_id' => $evt1, 'user_id' => 2, 'status' => 'accepted'],
            ['event_id' => $evt1, 'user_id' => 5, 'status' => 'tentative'],

            // 2. Weekly Management Standup
            ['event_id' => $evt2, 'user_id' => 3, 'status' => 'accepted'],
            ['event_id' => $evt2, 'user_id' => 2, 'status' => 'accepted'],
            ['event_id' => $evt2, 'user_id' => 4, 'status' => 'accepted'],
            ['event_id' => $evt2, 'user_id' => 5, 'status' => 'accepted'],
            ['event_id' => $evt2, 'user_id' => 6, 'status' => 'pending'],
            ['event_id' => $evt2, 'user_id' => 7, 'status' => 'accepted'],

            // 4. Ikeja Branch Audit
            ['event_id' => $evt4, 'user_id' => 3, 'status' => 'accepted'],
            ['event_id' => $evt4, 'user_id' => 6, 'status' => 'accepted'],

            // 5. Staff Training: AML Procedures
            ['event_id' => $evt5, 'user_id' => 4,  'status' => 'accepted'],
            ['event_id' => $evt5, 'user_id' => 12, 'status' => 'pending'],
            ['event_id' => $evt5, 'user_id' => 14, 'status' => 'tentative'],
            ['event_id' => $evt5, 'user_id' => 16, 'status' => 'pending'],

            // 6. New Product Launch Planning
            ['event_id' => $evt6, 'user_id' => 3, 'status' => 'accepted'],
            ['event_id' => $evt6, 'user_id' => 4, 'status' => 'accepted'],

            // 7. Shareholder AGM
            ['event_id' => $evt7, 'user_id' => 3, 'status' => 'accepted'],
            ['event_id' => $evt7, 'user_id' => 2, 'status' => 'accepted'],
            ['event_id' => $evt7, 'user_id' => 5, 'status' => 'pending'],
        ];

        foreach ($attendees as $att) {
            $notified = $att['status'] !== 'pending'
                ? $now->copy()->subDays(rand(2, 7))->toDateTimeString()
                : null;
            $responded = $att['status'] === 'accepted' || $att['status'] === 'tentative'
                ? $now->copy()->subDays(rand(1, 5))->toDateTimeString()
                : null;

            DB::table('calendar_event_attendees')->insertOrIgnore([
                'event_id'     => $att['event_id'],
                'user_id'      => $att['user_id'],
                'status'       => $att['status'],
                'notified_at'  => $notified,
                'responded_at' => $responded,
            ]);
        }

        // ── Reminders (15 min + 60 min for meetings) ───────────────────
        $meetingEventIds = [$evt1, $evt2, $evt5, $evt6, $evt7];

        foreach ($meetingEventIds as $eventId) {
            DB::table('calendar_event_reminders')->insertOrIgnore([
                'event_id'       => $eventId,
                'minutes_before' => 15,
                'is_sent'        => false,
            ]);
            DB::table('calendar_event_reminders')->insertOrIgnore([
                'event_id'       => $eventId,
                'minutes_before' => 60,
                'is_sent'        => false,
            ]);
        }
    }
}
