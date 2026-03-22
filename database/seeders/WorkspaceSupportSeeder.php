<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkspaceSupportSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $now = Carbon::now();
        $t = $this->tenantId;

        // Clean up any previous seed data for this tenant
        DB::table('visitor_watchlist')->where('tenant_id', $t)->delete();
        // visitor_activities has no tenant_id — delete via visit_id
        $visitIds = DB::table('visitor_visits')->where('tenant_id', $t)->pluck('id');
        DB::table('visitor_activities')->whereIn('visit_id', $visitIds)->delete();
        DB::table('visitor_meeting_attendees')->whereIn('meeting_id', DB::table('visitor_meetings')->where('tenant_id', $t)->pluck('id'))->delete();
        DB::table('visitor_meetings')->where('tenant_id', $t)->delete();
        DB::table('visitor_visits')->where('tenant_id', $t)->delete();
        DB::table('visitor_meeting_rooms')->where('tenant_id', $t)->delete();
        DB::table('visitors')->where('tenant_id', $t)->delete();

        DB::table('support_kb_articles')->where('tenant_id', $t)->delete();
        $ticketIds = DB::table('support_tickets')->where('tenant_id', $t)->pluck('id');
        DB::table('support_ticket_replies')->whereIn('ticket_id', $ticketIds)->delete();
        DB::table('support_tickets')->where('tenant_id', $t)->delete();
        DB::table('support_categories')->where('tenant_id', $t)->delete();
        DB::table('support_sla_policies')->where('tenant_id', $t)->delete();
        DB::table('support_team_members')->whereIn('team_id', DB::table('support_teams')->where('tenant_id', $t)->pluck('id'))->delete();
        DB::table('support_teams')->where('tenant_id', $t)->delete();

        DB::table('crm_follow_ups')->where('tenant_id', $t)->delete();
        DB::table('crm_interactions')->where('tenant_id', $t)->delete();
        DB::table('crm_leads')->where('tenant_id', $t)->delete();
        DB::table('crm_pipeline_stages')->where('tenant_id', $t)->delete();

        DB::table('announcement_reads')->whereIn('announcement_id', DB::table('announcements')->where('tenant_id', $t)->pluck('id'))->delete();
        DB::table('announcements')->where('tenant_id', $t)->delete();

        // Delete chat data: attachments -> messages -> participants -> conversations
        $convIds = DB::table('chat_conversations')->where('tenant_id', $t)->pluck('id');
        $msgIds = DB::table('chat_messages')->whereIn('conversation_id', $convIds)->pluck('id');
        DB::table('chat_attachments')->whereIn('message_id', $msgIds)->delete();
        // Null out reply_to_id before deleting messages (self-referencing FK)
        DB::table('chat_messages')->whereIn('conversation_id', $convIds)->update(['reply_to_id' => null]);
        DB::table('chat_messages')->whereIn('conversation_id', $convIds)->delete();
        DB::table('chat_participants')->whereIn('conversation_id', $convIds)->delete();
        DB::table('chat_conversations')->where('tenant_id', $t)->delete();

        // Fetch existing user IDs
        $userIds = DB::table('users')->pluck('id')->toArray();
        // Fetch existing customer IDs
        $customerIds = DB::table('customers')->pluck('id')->toArray();
        // Fetch existing account numbers
        $accounts = DB::table('accounts')->select('id', 'account_number', 'customer_id')->limit(10)->get();
        // Fetch branch IDs
        $branchIds = DB::table('branches')->pluck('id')->toArray();

        // ─────────────────────────────────────────────────
        // 1. INTERNAL WORKSPACE — CHAT
        // ─────────────────────────────────────────────────

        // Group chat: Operations Team
        $convOps = Str::uuid()->toString();
        $convDirect = Str::uuid()->toString();
        $convLoan = Str::uuid()->toString();

        DB::table('chat_conversations')->insert([
            [
                'id'                   => $convOps,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'group',
                'name'                 => 'Operations Team',
                'description'          => 'Day-to-day operations coordination',
                'created_by'           => $userIds[0],
                'last_message_at'      => $now->copy()->subMinutes(12),
                'last_message_preview' => 'GL reconciliation is done for today.',
                'is_archived'          => false,
                'created_at'           => $now->copy()->subDays(45),
                'updated_at'           => $now->copy()->subMinutes(12),
            ],
            [
                'id'                   => $convDirect,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'direct',
                'name'                 => null,
                'description'          => null,
                'created_by'           => $userIds[2],
                'last_message_at'      => $now->copy()->subHours(2),
                'last_message_preview' => 'Can you review the new account opening docs?',
                'is_archived'          => false,
                'created_at'           => $now->copy()->subDays(30),
                'updated_at'           => $now->copy()->subHours(2),
            ],
            [
                'id'                   => $convLoan,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'group',
                'name'                 => 'Loan Committee',
                'description'          => 'Loan approvals discussion and documentation',
                'created_by'           => $userIds[1],
                'last_message_at'      => $now->copy()->subHours(5),
                'last_message_preview' => 'The SME facility for Eze Holdings has been approved.',
                'is_archived'          => false,
                'created_at'           => $now->copy()->subDays(60),
                'updated_at'           => $now->copy()->subHours(5),
            ],
        ]);

        // Chat participants
        $participants = [];
        // Ops team: 5 members
        foreach (array_slice($userIds, 0, 5) as $i => $uid) {
            $participants[] = [
                'conversation_id' => $convOps,
                'user_id'         => $uid,
                'role'            => $i === 0 ? 'admin' : 'member',
                'joined_at'      => $now->copy()->subDays(45),
                'last_read_at'   => $now->copy()->subMinutes(rand(5, 120)),
                'left_at'        => null,
                'created_at'     => $now->copy()->subDays(45),
                'updated_at'     => $now,
            ];
        }
        // Direct chat: 2 users
        foreach ([$userIds[2], $userIds[3]] as $uid) {
            $participants[] = [
                'conversation_id' => $convDirect,
                'user_id'         => $uid,
                'role'            => 'member',
                'joined_at'      => $now->copy()->subDays(30),
                'last_read_at'   => $now->copy()->subMinutes(rand(5, 60)),
                'left_at'        => null,
                'created_at'     => $now->copy()->subDays(30),
                'updated_at'     => $now,
            ];
        }
        // Loan committee: 4 members
        foreach (array_slice($userIds, 1, 4) as $i => $uid) {
            $participants[] = [
                'conversation_id' => $convLoan,
                'user_id'         => $uid,
                'role'            => $i === 0 ? 'admin' : 'member',
                'joined_at'      => $now->copy()->subDays(60),
                'last_read_at'   => $now->copy()->subMinutes(rand(30, 300)),
                'left_at'        => null,
                'created_at'     => $now->copy()->subDays(60),
                'updated_at'     => $now,
            ];
        }
        DB::table('chat_participants')->insert($participants);

        // Chat messages
        $opsMessages = [
            ['body' => 'Good morning team. Vault balance reconciled — all clear.', 'mins_ago' => 180, 'sender' => 0],
            ['body' => 'ATM at Ikeja Branch is showing a cash-low alert. Can someone arrange replenishment?', 'mins_ago' => 150, 'sender' => 1],
            ['body' => 'Already on it. Cash-in-transit is scheduled for 11 AM.', 'mins_ago' => 140, 'sender' => 2],
            ['body' => 'CBN returns file for yesterday has been uploaded to the NIBSS portal.', 'mins_ago' => 90, 'sender' => 3],
            ['body' => 'Please remember to complete the daily suspense account clearance before 3 PM.', 'mins_ago' => 60, 'sender' => 0],
            ['body' => 'GL reconciliation is done for today.', 'mins_ago' => 12, 'sender' => 4],
        ];
        $msgIds = [];
        foreach ($opsMessages as $msg) {
            $msgId = Str::uuid()->toString();
            $msgIds[] = $msgId;
            DB::table('chat_messages')->insert([
                'id'              => $msgId,
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $convOps,
                'sender_id'       => $userIds[$msg['sender']],
                'reply_to_id'     => null,
                'body'            => $msg['body'],
                'type'            => 'text',
                'is_edited'       => false,
                'edited_at'       => null,
                'is_deleted'      => false,
                'deleted_at'      => null,
                'created_at'      => $now->copy()->subMinutes($msg['mins_ago']),
                'updated_at'      => $now->copy()->subMinutes($msg['mins_ago']),
            ]);
        }

        // A reply referencing the ATM message
        DB::table('chat_messages')->insert([
            'id'              => Str::uuid()->toString(),
            'tenant_id'       => $this->tenantId,
            'conversation_id' => $convOps,
            'sender_id'       => $userIds[0],
            'reply_to_id'     => $msgIds[1], // replying to ATM alert
            'body'            => 'Thanks for the quick response on the ATM replenishment.',
            'type'            => 'text',
            'is_edited'       => false,
            'edited_at'       => null,
            'is_deleted'      => false,
            'deleted_at'      => null,
            'created_at'      => $now->copy()->subMinutes(130),
            'updated_at'      => $now->copy()->subMinutes(130),
        ]);

        // Direct messages
        $directMsgs = [
            ['body' => 'Hi, have you seen the new KYC documents for the Fasanya account?', 'mins_ago' => 180, 'sender' => $userIds[2]],
            ['body' => 'Yes, I reviewed them. The BVN verification passed but the utility bill is expired.', 'mins_ago' => 165, 'sender' => $userIds[3]],
            ['body' => 'I will ask the customer for an updated copy. Thanks!', 'mins_ago' => 155, 'sender' => $userIds[2]],
            ['body' => 'Can you review the new account opening docs?', 'mins_ago' => 120, 'sender' => $userIds[2]],
        ];
        foreach ($directMsgs as $msg) {
            DB::table('chat_messages')->insert([
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $convDirect,
                'sender_id'       => $msg['sender'],
                'reply_to_id'     => null,
                'body'            => $msg['body'],
                'type'            => 'text',
                'is_edited'       => false,
                'edited_at'       => null,
                'is_deleted'      => false,
                'deleted_at'      => null,
                'created_at'      => $now->copy()->subMinutes($msg['mins_ago']),
                'updated_at'      => $now->copy()->subMinutes($msg['mins_ago']),
            ]);
        }

        // Loan committee messages
        $loanMsgs = [
            ['body' => 'The credit report for Eze Holdings is attached. Revenue looks strong — NGN 85M last FY.', 'mins_ago' => 420, 'sender' => $userIds[1]],
            ['body' => 'Collateral valuation came in at NGN 120M for the Lekki property. Sufficient for the NGN 50M facility.', 'mins_ago' => 390, 'sender' => $userIds[3]],
            ['body' => 'I recommend approval with a 90-day moratorium. All committee members please vote.', 'mins_ago' => 360, 'sender' => $userIds[1]],
            ['body' => 'Approved. Good risk profile.', 'mins_ago' => 330, 'sender' => $userIds[2]],
            ['body' => 'The SME facility for Eze Holdings has been approved.', 'mins_ago' => 300, 'sender' => $userIds[1]],
        ];
        foreach ($loanMsgs as $msg) {
            DB::table('chat_messages')->insert([
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $convLoan,
                'sender_id'       => $msg['sender'],
                'reply_to_id'     => null,
                'body'            => $msg['body'],
                'type'            => 'text',
                'is_edited'       => false,
                'edited_at'       => null,
                'is_deleted'      => false,
                'deleted_at'      => null,
                'created_at'      => $now->copy()->subMinutes($msg['mins_ago']),
                'updated_at'      => $now->copy()->subMinutes($msg['mins_ago']),
            ]);
        }

        // ─────────────────────────────────────────────────
        // 2. ANNOUNCEMENTS
        // ─────────────────────────────────────────────────

        $announcements = [
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'created_by'     => $userIds[0],
                'title'          => 'System Maintenance — Core Banking Upgrade',
                'body'           => "Please be advised that the core banking system will undergo scheduled maintenance on Saturday, March 28th from 11:00 PM to 4:00 AM WAT.\n\nDuring this window:\n- Internet and mobile banking will be temporarily unavailable\n- ATM services will operate in stand-in mode\n- Branch operations will resume normally by Monday morning\n\nPlease ensure all end-of-day processes are completed before 10:00 PM on Saturday.",
                'priority'       => 'high',
                'audience'       => 'all',
                'audience_ref_id'=> null,
                'publish_at'     => $now->copy()->subDays(2),
                'expires_at'     => $now->copy()->addDays(5),
                'is_pinned'      => true,
                'is_published'   => true,
                'created_at'     => $now->copy()->subDays(2),
                'updated_at'     => $now->copy()->subDays(2),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'created_by'     => $userIds[2],
                'title'          => 'New Anti-Money Laundering Policy Update',
                'body'           => "The compliance department has issued updated AML guidelines effective immediately.\n\nKey changes:\n- Enhanced due diligence threshold lowered to NGN 5,000,000\n- All cash transactions above NGN 1,000,000 require additional documentation\n- PEP screening now mandatory for all new account openings\n\nPlease review the full policy document on the compliance portal and acknowledge within 48 hours.",
                'priority'       => 'urgent',
                'audience'       => 'all',
                'audience_ref_id'=> null,
                'publish_at'     => $now->copy()->subDays(1),
                'expires_at'     => null,
                'is_pinned'      => true,
                'is_published'   => true,
                'created_at'     => $now->copy()->subDays(1),
                'updated_at'     => $now->copy()->subDays(1),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'created_by'     => $userIds[4],
                'title'          => 'Staff Appreciation Day — Friday, April 3rd',
                'body'           => "We are excited to announce the quarterly Staff Appreciation Day!\n\nDate: Friday, April 3rd\nTime: 12:00 PM - 3:00 PM\nVenue: Head Office Rooftop Lounge\n\nActivities include lunch, team awards, and a surprise guest speaker. All branches will close at 12:00 PM. RSVP through the HR portal.",
                'priority'       => 'normal',
                'audience'       => 'all',
                'audience_ref_id'=> null,
                'publish_at'     => $now->copy()->subHours(6),
                'expires_at'     => $now->copy()->addDays(12),
                'is_pinned'      => false,
                'is_published'   => true,
                'created_at'     => $now->copy()->subHours(6),
                'updated_at'     => $now->copy()->subHours(6),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'created_by'     => $userIds[0],
                'title'          => 'Q1 2026 Performance Review Deadline',
                'body'           => "All line managers are reminded that Q1 performance reviews are due by March 31st.\n\nPlease ensure all direct reports have completed their self-assessments and that review meetings have been scheduled. Late submissions will affect departmental KPI scores.",
                'priority'       => 'normal',
                'audience'       => 'all',
                'audience_ref_id'=> null,
                'publish_at'     => $now->copy()->subDays(5),
                'expires_at'     => $now->copy()->addDays(9),
                'is_pinned'      => false,
                'is_published'   => true,
                'created_at'     => $now->copy()->subDays(5),
                'updated_at'     => $now->copy()->subDays(5),
            ],
            [
                'id'             => Str::uuid()->toString(),
                'tenant_id'      => $this->tenantId,
                'created_by'     => $userIds[2],
                'title'          => 'Ikeja Branch — Network Outage Resolved',
                'body'           => "The network connectivity issue at Ikeja Branch has been resolved. All systems are back online. Thank you for your patience.",
                'priority'       => 'low',
                'audience'       => 'branch',
                'audience_ref_id'=> $branchIds[1] ?? null,
                'publish_at'     => $now->copy()->subDays(3),
                'expires_at'     => $now->copy()->subDays(1),
                'is_pinned'      => false,
                'is_published'   => true,
                'created_at'     => $now->copy()->subDays(3),
                'updated_at'     => $now->copy()->subDays(3),
            ],
        ];
        DB::table('announcements')->insert($announcements);

        // Announcement reads
        $announcementIds = array_column($announcements, 'id');
        $reads = [];
        foreach ($announcementIds as $aId) {
            // Random subset of users read each announcement
            $readUsers = array_slice($userIds, 0, rand(4, min(10, count($userIds))));
            foreach ($readUsers as $uid) {
                $reads[] = [
                    'announcement_id' => $aId,
                    'user_id'         => $uid,
                    'read_at'         => $now->copy()->subMinutes(rand(30, 2880)),
                ];
            }
        }
        DB::table('announcement_reads')->insert($reads);

        // ─────────────────────────────────────────────────
        // 3. CRM & GROWTH
        // ─────────────────────────────────────────────────

        // Pipeline stages
        $stages = [
            ['id' => Str::uuid()->toString(), 'name' => 'Prospecting',    'color' => '#6b7280', 'position' => 1, 'is_closed_won' => false, 'is_closed_lost' => false, 'requires_approval' => false],
            ['id' => Str::uuid()->toString(), 'name' => 'Qualification',  'color' => '#3b82f6', 'position' => 2, 'is_closed_won' => false, 'is_closed_lost' => false, 'requires_approval' => false],
            ['id' => Str::uuid()->toString(), 'name' => 'Proposal',       'color' => '#f59e0b', 'position' => 3, 'is_closed_won' => false, 'is_closed_lost' => false, 'requires_approval' => false],
            ['id' => Str::uuid()->toString(), 'name' => 'Negotiation',    'color' => '#f97316', 'position' => 4, 'is_closed_won' => false, 'is_closed_lost' => false, 'requires_approval' => true],
            ['id' => Str::uuid()->toString(), 'name' => 'Closed Won',     'color' => '#22c55e', 'position' => 5, 'is_closed_won' => true,  'is_closed_lost' => false, 'requires_approval' => false],
            ['id' => Str::uuid()->toString(), 'name' => 'Closed Lost',    'color' => '#ef4444', 'position' => 6, 'is_closed_won' => false, 'is_closed_lost' => true,  'requires_approval' => false],
        ];
        foreach ($stages as &$s) {
            $s['tenant_id']  = $this->tenantId;
            $s['created_at'] = $now->copy()->subDays(90);
            $s['updated_at'] = $now->copy()->subDays(90);
        }
        unset($s);
        DB::table('crm_pipeline_stages')->insert($stages);

        // CRM Leads
        $leads = [
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Eze Holdings — SME Business Account',
                'contact_name'        => 'Chukwuma Eze',
                'contact_phone'       => '+234 803 456 7890',
                'contact_email'       => 'ceze@ezeholdings.com',
                'company'             => 'Eze Holdings Ltd',
                'source'              => 'referral',
                'product_interest'    => 'sme',
                'estimated_value'     => 15000000.00,
                'probability_pct'     => 85,
                'status'              => 'converted',
                'stage_idx'           => 4, // Closed Won
                'assigned_to'         => $userIds[3],
                'expected_close_date' => $now->copy()->subDays(5)->format('Y-m-d'),
                'closed_date'         => $now->copy()->subDays(3)->format('Y-m-d'),
                'notes'               => 'Converted to business current account. Client onboarded with POS terminal.',
                'days_ago'            => 30,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Adeyemo Farms — Agricultural Loan Facility',
                'contact_name'        => 'Olufemi Adeyemo',
                'contact_phone'       => '+234 706 123 4567',
                'contact_email'       => 'adeyemofarms@gmail.com',
                'company'             => 'Adeyemo Farms & Agro-Allied',
                'source'              => 'walk-in',
                'product_interest'    => 'loan',
                'estimated_value'     => 25000000.00,
                'probability_pct'     => 60,
                'status'              => 'in_progress',
                'stage_idx'           => 2, // Proposal
                'assigned_to'         => $userIds[1],
                'expected_close_date' => $now->copy()->addDays(14)->format('Y-m-d'),
                'closed_date'         => null,
                'notes'               => 'Client interested in NIRSAL-guaranteed agric loan. Collateral evaluation pending.',
                'days_ago'            => 15,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Kelechi Nnamdi — High-Value Savings Account',
                'contact_name'        => 'Kelechi Nnamdi',
                'contact_phone'       => '+234 812 345 6789',
                'contact_email'       => 'kelechi.n@outlook.com',
                'company'             => null,
                'source'              => 'social',
                'product_interest'    => 'savings',
                'estimated_value'     => 50000000.00,
                'probability_pct'     => 40,
                'status'              => 'in_progress',
                'stage_idx'           => 1, // Qualification
                'assigned_to'         => $userIds[4],
                'expected_close_date' => $now->copy()->addDays(21)->format('Y-m-d'),
                'closed_date'         => null,
                'notes'               => 'Diaspora client. Interested in domiciliary and high-yield savings. Needs BVN linkage.',
                'days_ago'            => 7,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'GreenTech Solutions — Payroll Account',
                'contact_name'        => 'Amara Obi',
                'contact_phone'       => '+234 909 876 5432',
                'contact_email'       => 'amara@greentechng.com',
                'company'             => 'GreenTech Solutions Ltd',
                'source'              => 'direct',
                'product_interest'    => 'sme',
                'estimated_value'     => 8000000.00,
                'probability_pct'     => 70,
                'status'              => 'in_progress',
                'stage_idx'           => 3, // Negotiation
                'assigned_to'         => $userIds[5],
                'expected_close_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                'closed_date'         => null,
                'notes'               => 'Tech startup with 45 employees. Wants corporate current + payroll. Comparing with GTBank offer.',
                'days_ago'            => 20,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Mrs. Abiodun — Fixed Deposit Rollover',
                'contact_name'        => 'Folake Abiodun',
                'contact_phone'       => '+234 803 111 2233',
                'contact_email'       => null,
                'company'             => null,
                'source'              => 'referral',
                'product_interest'    => 'savings',
                'estimated_value'     => 100000000.00,
                'probability_pct'     => 90,
                'status'              => 'in_progress',
                'stage_idx'           => 3, // Negotiation
                'assigned_to'         => $userIds[0],
                'expected_close_date' => $now->copy()->addDays(3)->format('Y-m-d'),
                'closed_date'         => null,
                'notes'               => 'HNI client rolling over NGN 100M FD from Zenith. Requesting 18% rate. MD approval needed.',
                'days_ago'            => 10,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Balogun Motors — Vehicle Asset Finance',
                'contact_name'        => 'Ibrahim Balogun',
                'contact_phone'       => '+234 708 555 6677',
                'contact_email'       => 'ibrahim@balogunmotors.ng',
                'company'             => 'Balogun Motors Nigeria',
                'source'              => 'walk-in',
                'product_interest'    => 'loan',
                'estimated_value'     => 35000000.00,
                'probability_pct'     => 20,
                'status'              => 'lost',
                'stage_idx'           => 5, // Closed Lost
                'assigned_to'         => $userIds[3],
                'expected_close_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                'closed_date'         => $now->copy()->subDays(8)->format('Y-m-d'),
                'notes'               => null,
                'days_ago'            => 40,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Pinnacle Schools — Education Sector Package',
                'contact_name'        => 'Dr. Ngozi Ike',
                'contact_phone'       => '+234 816 789 0123',
                'contact_email'       => 'ngozi.ike@pinnacleschools.edu.ng',
                'company'             => 'Pinnacle Schools Group',
                'source'              => 'referral',
                'product_interest'    => 'sme',
                'estimated_value'     => 20000000.00,
                'probability_pct'     => 30,
                'status'              => 'new',
                'stage_idx'           => 0, // Prospecting
                'assigned_to'         => $userIds[6],
                'expected_close_date' => $now->copy()->addDays(45)->format('Y-m-d'),
                'closed_date'         => null,
                'notes'               => 'Group of 3 schools. Interested in collections account + school fees platform integration.',
                'days_ago'            => 2,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'title'               => 'Chief Okoro — Private Banking Prospect',
                'contact_name'        => 'Chief Emeka Okoro',
                'contact_phone'       => '+234 803 999 8877',
                'contact_email'       => null,
                'company'             => 'Okoro & Sons Enterprises',
                'source'              => 'referral',
                'product_interest'    => 'savings',
                'estimated_value'     => 250000000.00,
                'probability_pct'     => 25,
                'status'              => 'on_hold',
                'stage_idx'           => 1, // Qualification
                'assigned_to'         => $userIds[0],
                'expected_close_date' => null,
                'closed_date'         => null,
                'notes'               => 'UHNWI prospect referred by board member. Currently on hold — client travelling until April.',
                'days_ago'            => 18,
            ],
        ];

        foreach ($leads as $lead) {
            $stageIdx = $lead['stage_idx'];
            $daysAgo  = $lead['days_ago'];
            unset($lead['stage_idx'], $lead['days_ago']);

            $lead['tenant_id']            = $this->tenantId;
            $lead['stage_id']             = $stages[$stageIdx]['id'];
            $lead['converted_account_id'] = null;
            $lead['lost_reason']          = $lead['status'] === 'lost' ? 'Client chose competitor offering lower interest rate on asset finance.' : null;
            $lead['created_by']           = $lead['assigned_to'];
            $lead['created_at']           = $now->copy()->subDays($daysAgo);
            $lead['updated_at']           = $now->copy()->subDays(max(0, $daysAgo - 2));

            DB::table('crm_leads')->insert($lead);
        }

        // CRM Interactions
        $interactions = [
            [
                'subject_type'    => 'lead',
                'subject_id'      => $leads[0]['id'],
                'lead_id'         => $leads[0]['id'],
                'interaction_type'=> 'meeting',
                'direction'       => 'outbound',
                'subject'         => 'Account opening meeting with Eze Holdings',
                'summary'         => 'Met with CFO at their Lekki office. Discussed business current account features, POS terminal placement, and transaction pricing. Client impressed with our SME package.',
                'outcome'         => 'Client agreed to proceed. Documents to be submitted within 3 days.',
                'next_action'     => 'Follow up on document submission',
                'next_action_date'=> $now->copy()->subDays(8)->format('Y-m-d'),
                'duration_mins'   => 90,
                'interacted_at'   => $now->copy()->subDays(12),
                'created_by'      => $userIds[3],
            ],
            [
                'subject_type'    => 'lead',
                'subject_id'      => $leads[1]['id'],
                'lead_id'         => $leads[1]['id'],
                'interaction_type'=> 'call',
                'direction'       => 'outbound',
                'subject'         => 'Loan terms discussion — Adeyemo Farms',
                'summary'         => 'Called to discuss NIRSAL-guaranteed agric loan terms. Client has 200 hectares in Ogun state. Needs NGN 25M for irrigation and mechanisation.',
                'outcome'         => 'Client to provide farm valuation report and 3-year financial projections.',
                'next_action'     => 'Schedule site visit to farm',
                'next_action_date'=> $now->copy()->addDays(5)->format('Y-m-d'),
                'duration_mins'   => 35,
                'interacted_at'   => $now->copy()->subDays(3),
                'created_by'      => $userIds[1],
            ],
            [
                'subject_type'    => 'lead',
                'subject_id'      => $leads[2]['id'],
                'lead_id'         => $leads[2]['id'],
                'interaction_type'=> 'whatsapp',
                'direction'       => 'inbound',
                'subject'         => 'Diaspora account enquiry',
                'summary'         => 'Client messaged via WhatsApp asking about domiciliary account opening from abroad. Shared requirements checklist and BVN enrollment process for diaspora.',
                'outcome'         => 'Client to visit Nigerian embassy for BVN enrollment.',
                'next_action'     => 'Follow up after BVN enrollment',
                'next_action_date'=> $now->copy()->addDays(14)->format('Y-m-d'),
                'duration_mins'   => 15,
                'interacted_at'   => $now->copy()->subDays(2),
                'created_by'      => $userIds[4],
            ],
            [
                'subject_type'    => 'lead',
                'subject_id'      => $leads[3]['id'],
                'lead_id'         => $leads[3]['id'],
                'interaction_type'=> 'meeting',
                'direction'       => 'outbound',
                'subject'         => 'Payroll proposal presentation — GreenTech',
                'summary'         => 'Presented corporate current account + payroll package to CFO and HR manager. Our pricing is competitive but GTBank offered zero COT for 6 months.',
                'outcome'         => 'Will counter-offer with 3 months zero COT plus free corporate internet banking.',
                'next_action'     => 'Send revised proposal',
                'next_action_date'=> $now->copy()->addDays(2)->format('Y-m-d'),
                'duration_mins'   => 60,
                'interacted_at'   => $now->copy()->subDays(1),
                'created_by'      => $userIds[5],
            ],
            [
                'subject_type'    => 'lead',
                'subject_id'      => $leads[4]['id'],
                'lead_id'         => $leads[4]['id'],
                'interaction_type'=> 'visit',
                'direction'       => 'inbound',
                'subject'         => 'Mrs. Abiodun branch visit for FD discussion',
                'summary'         => 'HNI client visited Head Office. Discussed rolling over NGN 100M FD from Zenith. Requesting 18% rate. Current market is 15-16%.',
                'outcome'         => 'Escalated rate request to MD. Will respond within 48 hours.',
                'next_action'     => 'Get MD approval on special rate',
                'next_action_date'=> $now->copy()->addDays(1)->format('Y-m-d'),
                'duration_mins'   => 45,
                'interacted_at'   => $now->copy()->subDays(1),
                'created_by'      => $userIds[0],
            ],
            [
                'subject_type'    => 'account',
                'subject_id'      => $accounts[0]->id ?? $leads[0]['id'],
                'lead_id'         => null,
                'account_id'      => $accounts[0]->id ?? null,
                'interaction_type'=> 'call',
                'direction'       => 'outbound',
                'subject'         => 'Account reactivation follow-up',
                'summary'         => 'Called customer regarding dormant account reactivation. Customer confirmed they want to resume using the account. Advised on documentation needed.',
                'outcome'         => 'Customer will visit branch with valid ID for reactivation.',
                'next_action'     => 'Process reactivation upon branch visit',
                'next_action_date'=> $now->copy()->addDays(3)->format('Y-m-d'),
                'duration_mins'   => 10,
                'interacted_at'   => $now->copy()->subHours(4),
                'created_by'      => $userIds[5],
            ],
        ];

        foreach ($interactions as $int) {
            if (!isset($int['account_id'])) {
                $int['account_id'] = null;
            }
            $int['id']         = Str::uuid()->toString();
            $int['tenant_id']  = $this->tenantId;
            $int['created_at'] = $int['interacted_at'];
            $int['updated_at'] = $int['interacted_at'];
            DB::table('crm_interactions')->insert($int);
        }

        // CRM Follow-ups
        $followUps = [
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[1]['id'],
                'title'        => 'Schedule farm site visit — Adeyemo Farms',
                'notes'        => 'Coordinate with credit risk team. Need agronomist for valuation.',
                'due_at'       => $now->copy()->addDays(5),
                'status'       => 'pending',
                'assigned_to'  => $userIds[1],
                'created_by'   => $userIds[1],
            ],
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[2]['id'],
                'title'        => 'Check BVN enrollment status — Kelechi Nnamdi',
                'notes'        => 'Client is enrolling BVN at London embassy. Follow up in 2 weeks.',
                'due_at'       => $now->copy()->addDays(14),
                'status'       => 'pending',
                'assigned_to'  => $userIds[4],
                'created_by'   => $userIds[4],
            ],
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[3]['id'],
                'title'        => 'Send revised payroll proposal to GreenTech',
                'notes'        => 'Include 3-month zero COT and free internet banking. Deadline before they sign with GTBank.',
                'due_at'       => $now->copy()->addDays(2),
                'status'       => 'pending',
                'assigned_to'  => $userIds[5],
                'created_by'   => $userIds[5],
            ],
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[4]['id'],
                'title'        => 'Get MD approval for 18% FD rate — Mrs. Abiodun',
                'notes'        => 'NGN 100M FD rollover. HNI client. Needs response within 48 hours.',
                'due_at'       => $now->copy()->addDays(1),
                'status'       => 'pending',
                'assigned_to'  => $userIds[0],
                'created_by'   => $userIds[0],
            ],
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[0]['id'],
                'title'        => 'Verify Eze Holdings POS terminal deployment',
                'notes'        => 'POS terminal was dispatched last week. Confirm installation and first transaction.',
                'due_at'       => $now->copy()->subDays(1),
                'status'       => 'completed',
                'assigned_to'  => $userIds[3],
                'created_by'   => $userIds[3],
            ],
            [
                'subject_type' => 'lead',
                'subject_id'   => $leads[6]['id'],
                'title'        => 'Initial call to Pinnacle Schools — introduce education package',
                'notes'        => 'Referred by Adebayo. Call Dr. Ngozi Ike to set up introductory meeting.',
                'due_at'       => $now->copy()->addDays(3),
                'status'       => 'pending',
                'assigned_to'  => $userIds[6],
                'created_by'   => $userIds[6],
            ],
        ];

        foreach ($followUps as $fu) {
            $fu['id']         = Str::uuid()->toString();
            $fu['tenant_id']  = $this->tenantId;
            $fu['created_at'] = $now->copy()->subDays(rand(1, 5));
            $fu['updated_at'] = $now;
            DB::table('crm_follow_ups')->insert($fu);
        }

        // ─────────────────────────────────────────────────
        // 4. SUPPORT MODULE
        // ─────────────────────────────────────────────────

        // Support Teams
        $teamIT = Str::uuid()->toString();
        $teamCS = Str::uuid()->toString();
        $teamCard = Str::uuid()->toString();
        $teamFraud = Str::uuid()->toString();

        DB::table('support_teams')->insert([
            [
                'id'          => $teamIT,
                'tenant_id'   => $this->tenantId,
                'name'        => 'IT Support',
                'code'        => 'IT',
                'division'    => 'Technology',
                'description' => 'Infrastructure, applications, and end-user computing support.',
                'email'       => 'it.support@bankos.ng',
                'team_lead_id'=> $userIds[5],
                'is_active'   => true,
                'working_hours'=> json_encode(['mon' => ['start' => '08:00', 'end' => '18:00'], 'tue' => ['start' => '08:00', 'end' => '18:00'], 'wed' => ['start' => '08:00', 'end' => '18:00'], 'thu' => ['start' => '08:00', 'end' => '18:00'], 'fri' => ['start' => '08:00', 'end' => '17:00']]),
                'created_at'  => $now->copy()->subDays(90),
                'updated_at'  => $now->copy()->subDays(90),
            ],
            [
                'id'          => $teamCS,
                'tenant_id'   => $this->tenantId,
                'name'        => 'Customer Service',
                'code'        => 'CS',
                'division'    => 'Operations',
                'description' => 'Front-line customer enquiries and complaint resolution.',
                'email'       => 'customer.service@bankos.ng',
                'team_lead_id'=> $userIds[4],
                'is_active'   => true,
                'working_hours'=> json_encode(['mon' => ['start' => '08:00', 'end' => '18:00'], 'tue' => ['start' => '08:00', 'end' => '18:00'], 'wed' => ['start' => '08:00', 'end' => '18:00'], 'thu' => ['start' => '08:00', 'end' => '18:00'], 'fri' => ['start' => '08:00', 'end' => '17:00']]),
                'created_at'  => $now->copy()->subDays(90),
                'updated_at'  => $now->copy()->subDays(90),
            ],
            [
                'id'          => $teamCard,
                'tenant_id'   => $this->tenantId,
                'name'        => 'Card Operations',
                'code'        => 'CARD',
                'division'    => 'Operations',
                'description' => 'Debit/credit card issuance, disputes, and POS issues.',
                'email'       => 'card.ops@bankos.ng',
                'team_lead_id'=> $userIds[6],
                'is_active'   => true,
                'working_hours'=> null,
                'created_at'  => $now->copy()->subDays(90),
                'updated_at'  => $now->copy()->subDays(90),
            ],
            [
                'id'          => $teamFraud,
                'tenant_id'   => $this->tenantId,
                'name'        => 'Fraud & Disputes',
                'code'        => 'FRAUD',
                'division'    => 'Risk Management',
                'description' => 'Fraud investigation, chargebacks, and dispute resolution.',
                'email'       => 'fraud@bankos.ng',
                'team_lead_id'=> $userIds[2],
                'is_active'   => true,
                'working_hours'=> null,
                'created_at'  => $now->copy()->subDays(90),
                'updated_at'  => $now->copy()->subDays(90),
            ],
        ]);

        // Support team members
        $teamMembers = [
            ['team_id' => $teamIT,    'user_id' => $userIds[5],  'role' => 'team_lead'],
            ['team_id' => $teamIT,    'user_id' => $userIds[9],  'role' => 'agent'],
            ['team_id' => $teamIT,    'user_id' => $userIds[10], 'role' => 'agent'],
            ['team_id' => $teamCS,    'user_id' => $userIds[4],  'role' => 'team_lead'],
            ['team_id' => $teamCS,    'user_id' => $userIds[7],  'role' => 'agent'],
            ['team_id' => $teamCS,    'user_id' => $userIds[8],  'role' => 'agent'],
            ['team_id' => $teamCS,    'user_id' => $userIds[11], 'role' => 'agent'],
            ['team_id' => $teamCard,  'user_id' => $userIds[6],  'role' => 'team_lead'],
            ['team_id' => $teamCard,  'user_id' => $userIds[12], 'role' => 'agent'],
            ['team_id' => $teamFraud, 'user_id' => $userIds[2],  'role' => 'team_lead'],
            ['team_id' => $teamFraud, 'user_id' => $userIds[13], 'role' => 'agent'],
            ['team_id' => $teamFraud, 'user_id' => $userIds[14], 'role' => 'supervisor'],
        ];
        foreach ($teamMembers as &$tm) {
            $tm['is_active']  = true;
            $tm['created_at'] = $now->copy()->subDays(90);
            $tm['updated_at'] = $now->copy()->subDays(90);
        }
        unset($tm);
        DB::table('support_team_members')->insert($teamMembers);

        // SLA Policies
        $slaLow = Str::uuid()->toString();
        $slaMed = Str::uuid()->toString();
        $slaHigh = Str::uuid()->toString();
        $slaCrit = Str::uuid()->toString();

        DB::table('support_sla_policies')->insert([
            ['id' => $slaLow,  'tenant_id' => $this->tenantId, 'name' => 'Low Priority SLA',      'priority' => 'low',      'response_minutes' => 480, 'resolution_minutes' => 2880, 'business_hours_only' => true,  'is_default' => false, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $slaMed,  'tenant_id' => $this->tenantId, 'name' => 'Medium Priority SLA',    'priority' => 'medium',   'response_minutes' => 240, 'resolution_minutes' => 1440, 'business_hours_only' => true,  'is_default' => true,  'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $slaHigh, 'tenant_id' => $this->tenantId, 'name' => 'High Priority SLA',      'priority' => 'high',     'response_minutes' => 60,  'resolution_minutes' => 480,  'business_hours_only' => true,  'is_default' => false, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $slaCrit, 'tenant_id' => $this->tenantId, 'name' => 'Critical Priority SLA',  'priority' => 'critical', 'response_minutes' => 15,  'resolution_minutes' => 120,  'business_hours_only' => false, 'is_default' => false, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
        ]);

        // Support Categories
        $catAccount = Str::uuid()->toString();
        $catCard    = Str::uuid()->toString();
        $catLoan    = Str::uuid()->toString();
        $catIT      = Str::uuid()->toString();
        $catFraud   = Str::uuid()->toString();
        $catGeneral = Str::uuid()->toString();

        DB::table('support_categories')->insert([
            ['id' => $catAccount, 'tenant_id' => $this->tenantId, 'team_id' => $teamCS,    'name' => 'Account Issues',    'icon' => 'bank',          'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $catCard,    'tenant_id' => $this->tenantId, 'team_id' => $teamCard,  'name' => 'Card Services',     'icon' => 'credit-card',   'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $catLoan,    'tenant_id' => $this->tenantId, 'team_id' => $teamCS,    'name' => 'Loans & Credit',    'icon' => 'banknotes',     'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $catIT,      'tenant_id' => $this->tenantId, 'team_id' => $teamIT,    'name' => 'IT / Technical',    'icon' => 'computer',      'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $catFraud,   'tenant_id' => $this->tenantId, 'team_id' => $teamFraud, 'name' => 'Fraud & Security',  'icon' => 'shield-alert',  'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
            ['id' => $catGeneral, 'tenant_id' => $this->tenantId, 'team_id' => $teamCS,    'name' => 'General Enquiry',   'icon' => 'help-circle',   'is_active' => true, 'created_at' => $now->copy()->subDays(90), 'updated_at' => $now->copy()->subDays(90)],
        ]);

        // Support Tickets
        $tickets = [
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00001',
                'subject'               => 'Unable to access internet banking after password reset',
                'description'           => 'Customer reports that after resetting password via mobile app, the internet banking portal shows "Account Locked". Customer has tried clearing browser cache and using incognito mode without success.',
                'channel'               => 'phone',
                'priority'              => 'high',
                'status'                => 'resolved',
                'category_id'           => $catIT,
                'team_id'               => $teamIT,
                'assigned_to'           => $userIds[9],
                'created_by'            => $userIds[7],
                'requester_type'        => 'customer',
                'requester_name'        => 'Yetunde Nwosu',
                'requester_email'       => 'yetunde.n@gmail.com',
                'requester_phone'       => '+234 803 111 2233',
                'customer_id'           => $customerIds[0] ?? null,
                'account_number'        => $accounts[0]->account_number ?? null,
                'sla_policy_id'         => $slaHigh,
                'sla_response_due_at'   => $now->copy()->subDays(5)->addMinutes(60),
                'sla_resolution_due_at' => $now->copy()->subDays(5)->addMinutes(480),
                'first_responded_at'    => $now->copy()->subDays(5)->addMinutes(22),
                'resolved_at'           => $now->copy()->subDays(5)->addMinutes(195),
                'closed_at'             => $now->copy()->subDays(4),
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => 5,
                'satisfaction_comment'  => 'Very quick resolution. Thank you!',
                'created_at'            => $now->copy()->subDays(5),
                'updated_at'            => $now->copy()->subDays(4),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00002',
                'subject'               => 'Debit card not working at POS terminals',
                'description'           => 'Customer\'s Verve debit card is being declined at POS terminals across multiple merchants. Card works at ATM. No fraud alerts on the account. Card was issued 2 months ago.',
                'channel'               => 'walk_in',
                'priority'              => 'medium',
                'status'                => 'in_progress',
                'category_id'           => $catCard,
                'team_id'               => $teamCard,
                'assigned_to'           => $userIds[12],
                'created_by'            => $userIds[8],
                'requester_type'        => 'customer',
                'requester_name'        => 'Emeka Adeleke',
                'requester_email'       => 'emeka.adeleke@yahoo.com',
                'requester_phone'       => '+234 706 222 3344',
                'customer_id'           => $customerIds[1] ?? null,
                'account_number'        => $accounts[1]->account_number ?? null,
                'sla_policy_id'         => $slaMed,
                'sla_response_due_at'   => $now->copy()->subDays(1)->addMinutes(240),
                'sla_resolution_due_at' => $now->copy()->subDays(1)->addMinutes(1440),
                'first_responded_at'    => $now->copy()->subDays(1)->addMinutes(45),
                'resolved_at'           => null,
                'closed_at'             => null,
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => null,
                'satisfaction_comment'  => null,
                'created_at'            => $now->copy()->subDays(1),
                'updated_at'            => $now->copy()->subHours(3),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00003',
                'subject'               => 'Unauthorised transaction of NGN 150,000',
                'description'           => 'Customer noticed an unauthorised debit of NGN 150,000 via web payment on 2026-03-20 at 02:14 AM. Customer was asleep and did not authorise. Requesting immediate investigation and reversal.',
                'channel'               => 'phone',
                'priority'              => 'critical',
                'status'                => 'in_progress',
                'category_id'           => $catFraud,
                'team_id'               => $teamFraud,
                'assigned_to'           => $userIds[13],
                'created_by'            => $userIds[7],
                'requester_type'        => 'customer',
                'requester_name'        => 'Obiageli Nwachukwu',
                'requester_email'       => 'obiageli.nw@gmail.com',
                'requester_phone'       => '+234 812 555 6677',
                'customer_id'           => $customerIds[2] ?? null,
                'account_number'        => $accounts[2]->account_number ?? null,
                'sla_policy_id'         => $slaCrit,
                'sla_response_due_at'   => $now->copy()->subDays(2)->addMinutes(15),
                'sla_resolution_due_at' => $now->copy()->subDays(2)->addMinutes(120),
                'first_responded_at'    => $now->copy()->subDays(2)->addMinutes(8),
                'resolved_at'           => null,
                'closed_at'             => null,
                'sla_breached'          => true,
                'escalation_level'      => 1,
                'escalated_at'          => $now->copy()->subDays(1),
                'escalated_to'          => $userIds[2],
                'satisfaction_rating'   => null,
                'satisfaction_comment'  => null,
                'created_at'            => $now->copy()->subDays(2),
                'updated_at'            => $now->copy()->subHours(6),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00004',
                'subject'               => 'Request for account statement — last 6 months',
                'description'           => 'Customer needs certified account statement for the last 6 months for visa application to the UK embassy. Needs it stamped and signed by a bank official.',
                'channel'               => 'email',
                'priority'              => 'low',
                'status'                => 'resolved',
                'category_id'           => $catAccount,
                'team_id'               => $teamCS,
                'assigned_to'           => $userIds[11],
                'created_by'            => $userIds[8],
                'requester_type'        => 'customer',
                'requester_name'        => 'Suleiman Afolabi',
                'requester_email'       => 'suleiman.afolabi@live.com',
                'requester_phone'       => '+234 803 333 4455',
                'customer_id'           => $customerIds[3] ?? null,
                'account_number'        => $accounts[3]->account_number ?? null,
                'sla_policy_id'         => $slaLow,
                'sla_response_due_at'   => $now->copy()->subDays(7)->addMinutes(480),
                'sla_resolution_due_at' => $now->copy()->subDays(7)->addMinutes(2880),
                'first_responded_at'    => $now->copy()->subDays(7)->addMinutes(120),
                'resolved_at'           => $now->copy()->subDays(6),
                'closed_at'             => $now->copy()->subDays(6),
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => 4,
                'satisfaction_comment'  => 'Statement was ready on time. Thank you.',
                'created_at'            => $now->copy()->subDays(7),
                'updated_at'            => $now->copy()->subDays(6),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00005',
                'subject'               => 'Loan repayment not reflecting on account',
                'description'           => 'Customer made a loan repayment of NGN 500,000 via bank transfer 3 days ago. The loan balance has not been updated and the customer is receiving overdue SMS notifications.',
                'channel'               => 'portal',
                'priority'              => 'high',
                'status'                => 'open',
                'category_id'           => $catLoan,
                'team_id'               => $teamCS,
                'assigned_to'           => $userIds[4],
                'created_by'            => $userIds[4],
                'requester_type'        => 'customer',
                'requester_name'        => 'Taiwo Fasanya',
                'requester_email'       => 'taiwo.fasanya@gmail.com',
                'requester_phone'       => '+234 909 444 5566',
                'customer_id'           => $customerIds[4] ?? null,
                'account_number'        => $accounts[4]->account_number ?? null,
                'sla_policy_id'         => $slaHigh,
                'sla_response_due_at'   => $now->copy()->subHours(4)->addMinutes(60),
                'sla_resolution_due_at' => $now->copy()->subHours(4)->addMinutes(480),
                'first_responded_at'    => null,
                'resolved_at'           => null,
                'closed_at'             => null,
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => null,
                'satisfaction_comment'  => null,
                'created_at'            => $now->copy()->subHours(4),
                'updated_at'            => $now->copy()->subHours(4),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00006',
                'subject'               => 'Branch printer not working — Surulere',
                'description'           => 'The receipt printer at Teller Window 2 in Surulere Branch is jammed. Cannot print transaction receipts for customers. Affects customer service speed.',
                'channel'               => 'web',
                'priority'              => 'medium',
                'status'                => 'open',
                'category_id'           => $catIT,
                'team_id'               => $teamIT,
                'assigned_to'           => $userIds[10],
                'created_by'            => $userIds[8],
                'requester_type'        => 'staff',
                'requester_name'        => 'Hauwa Suleiman Musa',
                'requester_email'       => null,
                'requester_phone'       => null,
                'customer_id'           => null,
                'account_number'        => null,
                'sla_policy_id'         => $slaMed,
                'sla_response_due_at'   => $now->copy()->subHours(2)->addMinutes(240),
                'sla_resolution_due_at' => $now->copy()->subHours(2)->addMinutes(1440),
                'first_responded_at'    => $now->copy()->subHours(2)->addMinutes(30),
                'resolved_at'           => null,
                'closed_at'             => null,
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => null,
                'satisfaction_comment'  => null,
                'created_at'            => $now->copy()->subHours(2),
                'updated_at'            => $now->copy()->subHours(1),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00007',
                'subject'               => 'BVN update request — name correction',
                'description'           => 'Customer needs BVN name corrected from "Kayode Lawl" to "Kayode Lawal". Has court affidavit and valid ID. Requesting BVN amendment through NIBSS.',
                'channel'               => 'walk_in',
                'priority'              => 'low',
                'status'                => 'pending',
                'category_id'           => $catAccount,
                'team_id'               => $teamCS,
                'assigned_to'           => $userIds[7],
                'created_by'            => $userIds[7],
                'requester_type'        => 'customer',
                'requester_name'        => 'Kayode Lawal',
                'requester_email'       => null,
                'requester_phone'       => '+234 708 666 7788',
                'customer_id'           => $customerIds[5] ?? null,
                'account_number'        => $accounts[5]->account_number ?? null,
                'sla_policy_id'         => $slaLow,
                'sla_response_due_at'   => $now->copy()->subDays(3)->addMinutes(480),
                'sla_resolution_due_at' => $now->copy()->subDays(3)->addMinutes(2880),
                'first_responded_at'    => $now->copy()->subDays(3)->addMinutes(60),
                'resolved_at'           => null,
                'closed_at'             => null,
                'sla_breached'          => false,
                'escalation_level'      => 0,
                'escalated_at'          => null,
                'escalated_to'          => null,
                'satisfaction_rating'   => null,
                'satisfaction_comment'  => null,
                'created_at'            => $now->copy()->subDays(3),
                'updated_at'            => $now->copy()->subDays(2),
            ],
            [
                'id'                    => Str::uuid()->toString(),
                'ticket_number'         => 'TKT-2026-00008',
                'subject'               => 'Mobile app crashing on Android after update',
                'description'           => 'Multiple customers reporting that the bankOS mobile app crashes immediately after the v2.4.1 update on Android devices (Samsung, Infinix, Tecno). iOS appears unaffected.',
                'channel'               => 'phone',
                'priority'              => 'critical',
                'status'                => 'closed',
                'category_id'           => $catIT,
                'team_id'               => $teamIT,
                'assigned_to'           => $userIds[5],
                'created_by'            => $userIds[7],
                'requester_type'        => 'customer',
                'requester_name'        => 'Multiple Customers',
                'requester_email'       => null,
                'requester_phone'       => null,
                'customer_id'           => null,
                'account_number'        => null,
                'sla_policy_id'         => $slaCrit,
                'sla_response_due_at'   => $now->copy()->subDays(10)->addMinutes(15),
                'sla_resolution_due_at' => $now->copy()->subDays(10)->addMinutes(120),
                'first_responded_at'    => $now->copy()->subDays(10)->addMinutes(5),
                'resolved_at'           => $now->copy()->subDays(9)->addMinutes(300),
                'closed_at'             => $now->copy()->subDays(8),
                'sla_breached'          => true,
                'escalation_level'      => 2,
                'escalated_at'          => $now->copy()->subDays(10)->addMinutes(90),
                'escalated_to'          => $userIds[0],
                'satisfaction_rating'   => 3,
                'satisfaction_comment'  => 'Took too long to fix but the app works now.',
                'created_at'            => $now->copy()->subDays(10),
                'updated_at'            => $now->copy()->subDays(8),
            ],
        ];

        foreach ($tickets as &$t) {
            $t['tenant_id'] = $this->tenantId;
        }
        unset($t);
        DB::table('support_tickets')->insert($tickets);

        // Ticket replies
        $ticketReplies = [
            // Ticket 1 replies (internet banking locked)
            ['ticket_id' => $tickets[0]['id'], 'author_id' => $userIds[9], 'body' => 'I can see the account lock flag in the system. This happens when the mobile app password reset triggers a security hold on the internet banking channel. Clearing the flag now.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 22],
            ['ticket_id' => $tickets[0]['id'], 'author_id' => $userIds[9], 'body' => 'Lock cleared. Also reset the internet banking token. Customer should try logging in again.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 35],
            ['ticket_id' => $tickets[0]['id'], 'author_id' => $userIds[7], 'body' => 'Customer confirmed they can log in now. Closing ticket.', 'type' => 'status_change', 'is_internal' => false, 'mins_after' => 195],

            // Ticket 2 replies (card POS decline)
            ['ticket_id' => $tickets[1]['id'], 'author_id' => $userIds[12], 'body' => 'Checked the card profile on the switch. POS channel is enabled. Running a test transaction on our internal POS terminal.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 45],
            ['ticket_id' => $tickets[1]['id'], 'author_id' => $userIds[12], 'body' => 'Internal note: Test transaction failed with response code 91 (issuer inoperative). Escalating to NIBSS for Verve card routing issue.', 'type' => 'internal_note', 'is_internal' => true, 'mins_after' => 90],
            ['ticket_id' => $tickets[1]['id'], 'author_id' => $userIds[6], 'body' => 'NIBSS confirmed a routing issue affecting some Verve cards issued in batch 2026-02. They are working on a fix. ETA 24 hours.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 360],

            // Ticket 3 replies (fraud)
            ['ticket_id' => $tickets[2]['id'], 'author_id' => $userIds[13], 'body' => 'Account has been placed on PND (Post No Debit) as a precaution. Initiating transaction trace for the NGN 150,000 web payment.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 8],
            ['ticket_id' => $tickets[2]['id'], 'author_id' => $userIds[13], 'body' => 'Internal note: Transaction routed through Paystack to a merchant "QuickBuy NG". IP address traces to Lagos (not matching customer profile in Enugu). Requesting chargeback.', 'type' => 'internal_note', 'is_internal' => true, 'mins_after' => 120],
            ['ticket_id' => $tickets[2]['id'], 'author_id' => $userIds[2], 'body' => 'Chargeback initiated with Paystack. Also filed SAR with NFIU. Customer to provide sworn affidavit of non-authorisation. Expected resolution: 5-10 business days.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 1440],

            // Ticket 5 replies (loan repayment)
            ['ticket_id' => $tickets[4]['id'], 'author_id' => $userIds[4], 'body' => 'Looking into this now. Can see the transfer credit on the current account but the loan module has not picked it up. Likely a posting issue between modules.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 60],

            // Ticket 6 replies (printer)
            ['ticket_id' => $tickets[5]['id'], 'author_id' => $userIds[10], 'body' => 'Acknowledged. Will dispatch a technician to Surulere Branch this afternoon. In the meantime, please use the printer at Teller Window 3.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 30],

            // Ticket 8 replies (mobile app crash)
            ['ticket_id' => $tickets[7]['id'], 'author_id' => $userIds[5], 'body' => 'Confirmed the crash on Samsung A54 and Infinix Note 30. Root cause identified: v2.4.1 uses API level 34 features not available on older Android versions. Rolling back to v2.4.0 on Play Store.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 60],
            ['ticket_id' => $tickets[7]['id'], 'author_id' => $userIds[5], 'body' => 'v2.4.0 has been republished on Play Store. Hotfix v2.4.2 with backward compatibility is in QA. Expected release tomorrow.', 'type' => 'reply', 'is_internal' => false, 'mins_after' => 300],
            ['ticket_id' => $tickets[7]['id'], 'author_id' => $userIds[5], 'body' => 'v2.4.2 released and verified on all affected devices. Marking as resolved.', 'type' => 'status_change', 'is_internal' => false, 'mins_after' => 1440],
        ];

        foreach ($ticketReplies as $reply) {
            $minsAfter = $reply['mins_after'];
            unset($reply['mins_after']);

            // Find the ticket's created_at
            $ticketCreated = collect($tickets)->firstWhere('id', $reply['ticket_id'])['created_at'];
            $reply['id']             = Str::uuid()->toString();
            $reply['attachment_path']= null;
            $reply['created_at']     = $ticketCreated->copy()->addMinutes($minsAfter);
            $reply['updated_at']     = $ticketCreated->copy()->addMinutes($minsAfter);
            DB::table('support_ticket_replies')->insert($reply);
        }

        // Knowledge Base Articles
        DB::table('support_kb_articles')->insert([
            [
                'id'            => Str::uuid()->toString(),
                'tenant_id'     => $this->tenantId,
                'created_by'    => $userIds[5],
                'title'         => 'How to Reset Internet Banking Password',
                'body'          => "## Steps to Reset Internet Banking Password\n\n1. Visit the internet banking portal at https://ibank.bankos.ng\n2. Click \"Forgot Password\"\n3. Enter your registered email address or username\n4. An OTP will be sent to your registered phone number\n5. Enter the OTP and create a new password\n6. Password must be at least 8 characters with uppercase, lowercase, number, and special character\n\n**Note:** If your account gets locked after 3 failed attempts, contact Customer Service at 0700-BANKOS-1.",
                'category'      => 'Account Issues',
                'status'        => 'published',
                'view_count'    => 342,
                'helpful_count' => 89,
                'created_at'    => $now->copy()->subDays(60),
                'updated_at'    => $now->copy()->subDays(10),
            ],
            [
                'id'            => Str::uuid()->toString(),
                'tenant_id'     => $this->tenantId,
                'created_by'    => $userIds[6],
                'title'         => 'Debit Card Dispute Process — Staff Guide',
                'body'          => "## Card Dispute Handling Procedure\n\n### Step 1: Log the Complaint\n- Create a support ticket under \"Card Services\" category\n- Set priority based on amount: < NGN 50K = Medium, >= NGN 50K = High, >= NGN 500K = Critical\n\n### Step 2: Place PND on Card\n- Immediately block the card via the card management module\n- Issue a temporary card if requested\n\n### Step 3: Initiate Chargeback\n- For Verve: Log via NIBSS dispute portal within 72 hours\n- For Visa/Mastercard: Log via respective network portal within 120 days\n\n### Step 4: Follow Up\n- Track chargeback status weekly\n- Update customer every 3 business days",
                'category'      => 'Cards',
                'status'        => 'published',
                'view_count'    => 156,
                'helpful_count' => 45,
                'created_at'    => $now->copy()->subDays(45),
                'updated_at'    => $now->copy()->subDays(5),
            ],
            [
                'id'            => Str::uuid()->toString(),
                'tenant_id'     => $this->tenantId,
                'created_by'    => $userIds[4],
                'title'         => 'Customer Onboarding Checklist — Individual Account',
                'body'          => "## Required Documents\n\n- [x] Completed account opening form\n- [x] Valid government-issued ID (NIN slip, international passport, driver's licence, voter's card)\n- [x] BVN (Bank Verification Number)\n- [x] Proof of address (utility bill not older than 3 months)\n- [x] Passport photograph (taken within last 6 months)\n- [x] Reference from existing account holder (for savings accounts)\n\n## KYC Verification Steps\n\n1. Verify BVN via NIBSS BVN validation portal\n2. Run name screening against sanctions and PEP lists\n3. Verify address through utility bill or bank statement\n4. Capture biometrics (fingerprint + photo)\n5. Obtain customer signature specimen",
                'category'      => 'Account Issues',
                'status'        => 'published',
                'view_count'    => 523,
                'helpful_count' => 134,
                'created_at'    => $now->copy()->subDays(75),
                'updated_at'    => $now->copy()->subDays(15),
            ],
            [
                'id'            => Str::uuid()->toString(),
                'tenant_id'     => $this->tenantId,
                'created_by'    => $userIds[2],
                'title'         => 'Fraud Red Flags — What to Look For',
                'body'          => "## Common Fraud Indicators\n\n### Transaction Patterns\n- Multiple failed login attempts followed by a successful one from a new device/IP\n- Transactions at unusual hours (1 AM - 5 AM) for the customer's profile\n- Sudden high-value transactions on previously dormant accounts\n- Multiple transfers to different beneficiaries in rapid succession\n\n### Account Behaviour\n- Customer urgently requesting removal of transaction limits\n- Third party attempting to operate the account\n- Mismatched signatures on withdrawal slips\n\n### Reporting\n- File Suspicious Transaction Report (STR) via the compliance module within 24 hours\n- Escalate to Fraud team immediately for amounts above NGN 1,000,000",
                'category'      => 'Fraud & Security',
                'status'        => 'published',
                'view_count'    => 278,
                'helpful_count' => 98,
                'created_at'    => $now->copy()->subDays(50),
                'updated_at'    => $now->copy()->subDays(3),
            ],
        ]);

        // ─────────────────────────────────────────────────
        // 5. VISITOR MANAGEMENT
        // ─────────────────────────────────────────────────

        // Visitors (people registry)
        $visitors = [
            ['id' => Str::uuid()->toString(), 'full_name' => 'Alhaji Musa Danjuma',    'id_type' => 'national_id',      'id_number' => 'NIN-12345678901', 'phone' => '+234 803 111 0001', 'email' => 'musa.danjuma@gmail.com', 'company' => 'Danjuma Group',            'notes' => 'Board member. VIP access.',          'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Sarah Ogundimu',          'id_type' => 'drivers_license',  'id_number' => 'DL-LAG-99887766', 'phone' => '+234 706 222 0002', 'email' => 'sarah.o@techcorp.ng',    'company' => 'TechCorp Nigeria',         'notes' => null,                                  'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Mr. Femi Akindele',       'id_type' => 'passport',         'id_number' => 'A09876543',       'phone' => '+234 812 333 0003', 'email' => 'femi@akindeleconsult.com','company' => 'Akindele Consulting',      'notes' => 'External auditor — annual audit.',    'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Grace Emenike',            'id_type' => 'voters_card',      'id_number' => 'VC-FCT-11223344', 'phone' => '+234 909 444 0004', 'email' => null,                     'company' => null,                       'notes' => 'Walk-in customer from Abuja.',        'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Chinedu Okafor',           'id_type' => 'national_id',      'id_number' => 'NIN-98765432101', 'phone' => '+234 803 555 0005', 'email' => 'chinedu@smartprint.ng',  'company' => 'SmartPrint Solutions',     'notes' => 'IT vendor — printer maintenance.',    'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Binta Abdullahi',          'id_type' => 'national_id',      'id_number' => 'NIN-55566677788', 'phone' => '+234 708 666 0006', 'email' => null,                     'company' => null,                       'notes' => 'Relative of customer. Frequent visitor.', 'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'John Obi Nwankwo',         'id_type' => 'passport',         'id_number' => 'B01234567',       'phone' => '+234 816 777 0007', 'email' => 'john.nwankwo@cbnconsult.gov.ng', 'company' => 'CBN',               'notes' => 'Regulatory examiner.',                'is_blacklisted' => false],
            ['id' => Str::uuid()->toString(), 'full_name' => 'Abdul Rasheed',            'id_type' => 'national_id',      'id_number' => 'NIN-44433322211', 'phone' => '+234 803 888 0008', 'email' => null,                     'company' => null,                       'notes' => null,                                  'is_blacklisted' => true],
        ];

        foreach ($visitors as &$v) {
            $v['tenant_id']        = $this->tenantId;
            $v['photo_path']       = null;
            $v['blacklist_reason'] = $v['is_blacklisted'] ? 'Attempted to impersonate account holder during previous visit. Security incident logged 2026-01-15.' : null;
            $v['created_at']       = $now->copy()->subDays(rand(30, 180));
            $v['updated_at']       = $now->copy()->subDays(rand(0, 10));
        }
        unset($v);
        DB::table('visitors')->insert($visitors);

        // Meeting rooms
        $rooms = [
            ['id' => Str::uuid()->toString(), 'name' => 'Boardroom',      'location' => '5th Floor, Head Office', 'capacity' => 20, 'is_available' => true],
            ['id' => Str::uuid()->toString(), 'name' => 'Conference A',   'location' => '3rd Floor, Head Office', 'capacity' => 10, 'is_available' => true],
            ['id' => Str::uuid()->toString(), 'name' => 'Conference B',   'location' => '3rd Floor, Head Office', 'capacity' => 8,  'is_available' => true],
            ['id' => Str::uuid()->toString(), 'name' => 'MD\'s Office',   'location' => '6th Floor, Head Office', 'capacity' => 6,  'is_available' => true],
            ['id' => Str::uuid()->toString(), 'name' => 'Interview Room', 'location' => '2nd Floor, Head Office', 'capacity' => 4,  'is_available' => true],
        ];

        foreach ($rooms as &$r) {
            $r['tenant_id']  = $this->tenantId;
            $r['created_at'] = $now->copy()->subDays(90);
            $r['updated_at'] = $now->copy()->subDays(90);
        }
        unset($r);
        DB::table('visitor_meeting_rooms')->insert($rooms);

        // Visitor visits
        $visits = [
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[0]['id'],
                'host_user_id'    => $userIds[0], // Super Admin / MD
                'purpose'         => 'meeting',
                'badge_number'    => 'VB-001',
                'vehicle_plate'   => 'ABJ-234-KL',
                'items_brought'   => null,
                'items_left'      => null,
                'branch_id'       => $branchIds[0] ?? null,
                'status'          => 'checked_out',
                'notes'           => 'Board pre-meeting discussion.',
                'denial_reason'   => null,
                'expected_at'     => $now->copy()->subDays(3)->setTime(10, 0),
                'checked_in_at'   => $now->copy()->subDays(3)->setTime(9, 55),
                'checked_out_at'  => $now->copy()->subDays(3)->setTime(12, 30),
                'checked_in_by'   => $userIds[7],
                'checked_out_by'  => $userIds[7],
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[1]['id'],
                'host_user_id'    => $userIds[5], // IT lead
                'purpose'         => 'meeting',
                'badge_number'    => 'VB-002',
                'vehicle_plate'   => null,
                'items_brought'   => 'Laptop, projector',
                'items_left'      => null,
                'branch_id'       => $branchIds[0] ?? null,
                'status'          => 'checked_out',
                'notes'           => 'Product demo for new core banking module.',
                'denial_reason'   => null,
                'expected_at'     => $now->copy()->subDays(2)->setTime(14, 0),
                'checked_in_at'   => $now->copy()->subDays(2)->setTime(13, 50),
                'checked_out_at'  => $now->copy()->subDays(2)->setTime(16, 15),
                'checked_in_by'   => $userIds[7],
                'checked_out_by'  => $userIds[8],
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[2]['id'],
                'host_user_id'    => $userIds[2], // Compliance
                'purpose'         => 'meeting',
                'badge_number'    => 'VB-003',
                'vehicle_plate'   => 'LG-567-AB',
                'items_brought'   => 'Audit files, laptop',
                'items_left'      => null,
                'branch_id'       => $branchIds[0] ?? null,
                'status'          => 'checked_in',
                'notes'           => 'Annual external audit — Day 1. Will be on-site for 2 weeks.',
                'denial_reason'   => null,
                'expected_at'     => $now->copy()->setTime(9, 0),
                'checked_in_at'   => $now->copy()->setTime(8, 45),
                'checked_out_at'  => null,
                'checked_in_by'   => $userIds[7],
                'checked_out_by'  => null,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[3]['id'],
                'host_user_id'    => $userIds[4], // Customer service
                'purpose'         => 'banking',
                'badge_number'    => 'VB-004',
                'vehicle_plate'   => null,
                'items_brought'   => null,
                'items_left'      => null,
                'branch_id'       => $branchIds[3] ?? $branchIds[0],
                'status'          => 'checked_in',
                'notes'           => 'Walk-in customer requesting account opening.',
                'denial_reason'   => null,
                'expected_at'     => null,
                'checked_in_at'   => $now->copy()->setTime(10, 20),
                'checked_out_at'  => null,
                'checked_in_by'   => $userIds[8],
                'checked_out_by'  => null,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[4]['id'],
                'host_user_id'    => $userIds[10], // IT agent
                'purpose'         => 'maintenance',
                'badge_number'    => 'VB-005',
                'vehicle_plate'   => null,
                'items_brought'   => 'Printer parts, toolbox',
                'items_left'      => null,
                'branch_id'       => $branchIds[2] ?? $branchIds[0],
                'status'          => 'checked_in',
                'notes'           => 'Printer repair for Surulere Branch — linked to ticket TKT-2026-00006.',
                'denial_reason'   => null,
                'expected_at'     => $now->copy()->setTime(13, 0),
                'checked_in_at'   => $now->copy()->setTime(13, 10),
                'checked_out_at'  => null,
                'checked_in_by'   => $userIds[8],
                'checked_out_by'  => null,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[5]['id'],
                'host_user_id'    => $userIds[4],
                'purpose'         => 'banking',
                'badge_number'    => 'VB-006',
                'vehicle_plate'   => null,
                'items_brought'   => null,
                'items_left'      => null,
                'branch_id'       => $branchIds[1] ?? $branchIds[0],
                'status'          => 'checked_out',
                'notes'           => 'Came to collect cheque book on behalf of customer.',
                'denial_reason'   => null,
                'expected_at'     => null,
                'checked_in_at'   => $now->copy()->subDays(1)->setTime(11, 0),
                'checked_out_at'  => $now->copy()->subDays(1)->setTime(11, 35),
                'checked_in_by'   => $userIds[7],
                'checked_out_by'  => $userIds[7],
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[6]['id'],
                'host_user_id'    => $userIds[2], // Compliance
                'purpose'         => 'meeting',
                'badge_number'    => 'VB-007',
                'vehicle_plate'   => 'ABJ-CBN-001',
                'items_brought'   => 'Examination toolkit, laptop',
                'items_left'      => null,
                'branch_id'       => $branchIds[0] ?? null,
                'status'          => 'expected',
                'notes'           => 'CBN routine examination scheduled for next week.',
                'denial_reason'   => null,
                'expected_at'     => $now->copy()->addDays(5)->setTime(9, 0),
                'checked_in_at'   => null,
                'checked_out_at'  => null,
                'checked_in_by'   => null,
                'checked_out_by'  => null,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'visitor_id'      => $visitors[7]['id'],
                'host_user_id'    => $userIds[7],
                'purpose'         => 'banking',
                'badge_number'    => null,
                'vehicle_plate'   => null,
                'items_brought'   => null,
                'items_left'      => null,
                'branch_id'       => $branchIds[1] ?? $branchIds[0],
                'status'          => 'denied',
                'notes'           => null,
                'denial_reason'   => 'Visitor is on the blacklist. Attempted to impersonate account holder. Security alerted.',
                'expected_at'     => null,
                'checked_in_at'   => null,
                'checked_out_at'  => null,
                'checked_in_by'   => null,
                'checked_out_by'  => null,
            ],
        ];

        foreach ($visits as &$visit) {
            $visit['tenant_id']  = $this->tenantId;
            $visit['created_at'] = $visit['checked_in_at'] ?? $visit['expected_at'] ?? $now;
            $visit['updated_at'] = $visit['checked_out_at'] ?? $visit['checked_in_at'] ?? $now;
        }
        unset($visit);
        DB::table('visitor_visits')->insert($visits);

        // Visitor meetings
        $meetings = [
            [
                'id'               => Str::uuid()->toString(),
                'visit_id'         => $visits[0]['id'],
                'room_id'          => $rooms[0]['id'], // Boardroom
                'organiser_id'     => $userIds[0],
                'title'            => 'Board Pre-Meeting — Q1 Financial Review',
                'agenda'           => "1. Review Q1 2026 financial performance\n2. Discuss branch expansion plan\n3. Approve revised budget for IT infrastructure\n4. AOB",
                'minutes'          => "Attendees discussed Q1 results showing 12% growth in deposits. Branch expansion approved for Abeokuta and Benin. IT budget increased by NGN 50M for core banking upgrade.",
                'status'           => 'completed',
                'scheduled_at'     => $now->copy()->subDays(3)->setTime(10, 0),
                'duration_minutes' => 120,
                'started_at'       => $now->copy()->subDays(3)->setTime(10, 5),
                'ended_at'         => $now->copy()->subDays(3)->setTime(12, 15),
            ],
            [
                'id'               => Str::uuid()->toString(),
                'visit_id'         => $visits[1]['id'],
                'room_id'          => $rooms[1]['id'], // Conference A
                'organiser_id'     => $userIds[5],
                'title'            => 'TechCorp — Core Banking Module Demo',
                'agenda'           => "1. Overview of new module features\n2. Live demo: customer onboarding flow\n3. Integration requirements with existing infrastructure\n4. Pricing and timeline discussion",
                'minutes'          => "TechCorp demonstrated the new customer onboarding module. UI was impressive but API integration needs further evaluation. Follow-up technical session to be scheduled.",
                'status'           => 'completed',
                'scheduled_at'     => $now->copy()->subDays(2)->setTime(14, 0),
                'duration_minutes' => 90,
                'started_at'       => $now->copy()->subDays(2)->setTime(14, 0),
                'ended_at'         => $now->copy()->subDays(2)->setTime(15, 45),
            ],
            [
                'id'               => Str::uuid()->toString(),
                'visit_id'         => $visits[2]['id'],
                'room_id'          => $rooms[2]['id'], // Conference B
                'organiser_id'     => $userIds[2],
                'title'            => 'External Audit Kick-off — FY 2025',
                'agenda'           => "1. Scope of audit and timeline\n2. Document requests and access requirements\n3. Key contact persons per department\n4. Logistics — office space, Wi-Fi, printing",
                'minutes'          => null,
                'status'           => 'in_progress',
                'scheduled_at'     => $now->copy()->setTime(9, 0),
                'duration_minutes' => 60,
                'started_at'       => $now->copy()->setTime(9, 0),
                'ended_at'         => null,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'visit_id'         => null,
                'room_id'          => $rooms[3]['id'], // MD's Office
                'organiser_id'     => $userIds[0],
                'title'            => 'CBN Regulatory Examination — Pre-Meeting',
                'agenda'           => "1. Review readiness for CBN examination\n2. Assign departmental liaisons\n3. Document preparation checklist\n4. Risk areas to address proactively",
                'minutes'          => null,
                'status'           => 'scheduled',
                'scheduled_at'     => $now->copy()->addDays(4)->setTime(10, 0),
                'duration_minutes' => 90,
                'started_at'       => null,
                'ended_at'         => null,
            ],
            [
                'id'               => Str::uuid()->toString(),
                'visit_id'         => null,
                'room_id'          => $rooms[4]['id'], // Interview Room
                'organiser_id'     => $userIds[4],
                'title'            => 'Customer Relationship Manager — Interview',
                'agenda'           => "1. Technical assessment review\n2. Behavioural interview\n3. Role-specific scenario questions\n4. Salary discussion",
                'minutes'          => null,
                'status'           => 'scheduled',
                'scheduled_at'     => $now->copy()->addDays(1)->setTime(11, 0),
                'duration_minutes' => 45,
                'started_at'       => null,
                'ended_at'         => null,
            ],
        ];

        foreach ($meetings as &$m) {
            $m['tenant_id']  = $this->tenantId;
            $m['created_at'] = $m['scheduled_at'];
            $m['updated_at'] = $m['ended_at'] ?? $m['started_at'] ?? $m['scheduled_at'];
        }
        unset($m);
        DB::table('visitor_meetings')->insert($meetings);

        // Meeting attendees
        $attendees = [
            // Board meeting
            ['meeting_id' => $meetings[0]['id'], 'visitor_id' => $visitors[0]['id'], 'user_id' => null,        'type' => 'visitor', 'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[0]['id'], 'visitor_id' => null,               'user_id' => $userIds[0], 'type' => 'staff',   'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[0]['id'], 'visitor_id' => null,               'user_id' => $userIds[2], 'type' => 'staff',   'attendance_status' => 'attended'],
            // TechCorp demo
            ['meeting_id' => $meetings[1]['id'], 'visitor_id' => $visitors[1]['id'], 'user_id' => null,        'type' => 'visitor', 'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[1]['id'], 'visitor_id' => null,               'user_id' => $userIds[5], 'type' => 'staff',   'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[1]['id'], 'visitor_id' => null,               'user_id' => $userIds[9], 'type' => 'staff',   'attendance_status' => 'attended'],
            // Audit kick-off
            ['meeting_id' => $meetings[2]['id'], 'visitor_id' => $visitors[2]['id'], 'user_id' => null,        'type' => 'visitor', 'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[2]['id'], 'visitor_id' => null,               'user_id' => $userIds[2], 'type' => 'staff',   'attendance_status' => 'attended'],
            ['meeting_id' => $meetings[2]['id'], 'visitor_id' => null,               'user_id' => $userIds[0], 'type' => 'staff',   'attendance_status' => 'attended'],
            // CBN pre-meeting (scheduled)
            ['meeting_id' => $meetings[3]['id'], 'visitor_id' => null,               'user_id' => $userIds[0], 'type' => 'staff',   'attendance_status' => 'confirmed'],
            ['meeting_id' => $meetings[3]['id'], 'visitor_id' => null,               'user_id' => $userIds[2], 'type' => 'staff',   'attendance_status' => 'confirmed'],
            ['meeting_id' => $meetings[3]['id'], 'visitor_id' => null,               'user_id' => $userIds[1], 'type' => 'staff',   'attendance_status' => 'invited'],
            // Interview
            ['meeting_id' => $meetings[4]['id'], 'visitor_id' => null,               'user_id' => $userIds[4], 'type' => 'staff',   'attendance_status' => 'confirmed'],
        ];

        foreach ($attendees as &$a) {
            $a['created_at'] = $now->copy()->subDays(rand(0, 5));
            $a['updated_at'] = $now;
        }
        unset($a);
        DB::table('visitor_meeting_attendees')->insert($attendees);

        // Visitor activities
        $activities = [
            ['visit_id' => $visits[0]['id'], 'logged_by' => $userIds[7], 'activity_type' => 'meeting',         'description' => 'Attended board pre-meeting in the Boardroom.',                  'area_accessed' => '5th Floor Boardroom',   'occurred_at' => $now->copy()->subDays(3)->setTime(10, 5)],
            ['visit_id' => $visits[0]['id'], 'logged_by' => $userIds[7], 'activity_type' => 'document_signed', 'description' => 'Signed NDA for confidential financial review documents.',       'area_accessed' => '5th Floor Boardroom',   'occurred_at' => $now->copy()->subDays(3)->setTime(10, 15)],
            ['visit_id' => $visits[1]['id'], 'logged_by' => $userIds[5], 'activity_type' => 'meeting',         'description' => 'Conducted product demo in Conference A. Projector connected.', 'area_accessed' => '3rd Floor Conference A','occurred_at' => $now->copy()->subDays(2)->setTime(14, 0)],
            ['visit_id' => $visits[1]['id'], 'logged_by' => $userIds[5], 'activity_type' => 'area_access',     'description' => 'Escorted to server room for integration assessment.',          'area_accessed' => '1st Floor Server Room', 'occurred_at' => $now->copy()->subDays(2)->setTime(15, 30)],
            ['visit_id' => $visits[2]['id'], 'logged_by' => $userIds[2], 'activity_type' => 'meeting',         'description' => 'Audit kick-off meeting started. Document access granted.',     'area_accessed' => '3rd Floor Conference B','occurred_at' => $now->copy()->setTime(9, 0)],
            ['visit_id' => $visits[3]['id'], 'logged_by' => $userIds[8], 'activity_type' => 'area_access',     'description' => 'Walk-in customer directed to account opening desk.',           'area_accessed' => 'Ground Floor Banking Hall', 'occurred_at' => $now->copy()->setTime(10, 25)],
            ['visit_id' => $visits[4]['id'], 'logged_by' => $userIds[10],'activity_type' => 'maintenance',     'description' => 'Vendor began printer repair at Teller Window 2.',             'area_accessed' => 'Ground Floor Teller Area',  'occurred_at' => $now->copy()->setTime(13, 15)],
            ['visit_id' => $visits[5]['id'], 'logged_by' => $userIds[7], 'activity_type' => 'delivery',        'description' => 'Collected cheque book on behalf of account holder with authorisation letter.', 'area_accessed' => 'Ground Floor Customer Service', 'occurred_at' => $now->copy()->subDays(1)->setTime(11, 10)],
        ];

        foreach ($activities as $act) {
            $act['id']         = Str::uuid()->toString();
            $act['created_at'] = $act['occurred_at'];
            $act['updated_at'] = $act['occurred_at'];
            DB::table('visitor_activities')->insert($act);
        }

        // Visitor watchlist
        DB::table('visitor_watchlist')->insert([
            [
                'id'         => Str::uuid()->toString(),
                'tenant_id'  => $this->tenantId,
                'visitor_id' => $visitors[0]['id'],
                'status'     => 'vip',
                'reason'     => 'Board member. Grant VIP access to executive floor without escort.',
                'added_by'   => $userIds[0],
                'expires_at' => null,
                'created_at' => $now->copy()->subDays(90),
                'updated_at' => $now->copy()->subDays(90),
            ],
            [
                'id'         => Str::uuid()->toString(),
                'tenant_id'  => $this->tenantId,
                'visitor_id' => $visitors[2]['id'],
                'status'     => 'pre_approved',
                'reason'     => 'External auditor — pre-approved for 2-week on-site engagement. Access to compliance and finance floors.',
                'added_by'   => $userIds[2],
                'expires_at' => $now->copy()->addDays(14),
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(7),
            ],
            [
                'id'         => Str::uuid()->toString(),
                'tenant_id'  => $this->tenantId,
                'visitor_id' => $visitors[7]['id'],
                'status'     => 'blacklisted',
                'reason'     => 'Attempted to impersonate account holder on 2026-01-15. Security incident report filed. Do not grant access.',
                'added_by'   => $userIds[7],
                'expires_at' => null,
                'created_at' => $now->copy()->subDays(66),
                'updated_at' => $now->copy()->subDays(66),
            ],
            [
                'id'         => Str::uuid()->toString(),
                'tenant_id'  => $this->tenantId,
                'visitor_id' => $visitors[6]['id'],
                'status'     => 'pre_approved',
                'reason'     => 'CBN regulatory examiner. Pre-approved for scheduled examination visit.',
                'added_by'   => $userIds[2],
                'expires_at' => $now->copy()->addDays(10),
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],
        ]);

        $this->command->info('WorkspaceSupportSeeder completed successfully.');
        $this->command->info('  - 3 chat conversations with messages');
        $this->command->info('  - 5 announcements');
        $this->command->info('  - 6 CRM pipeline stages, 8 leads, 6 interactions, 6 follow-ups');
        $this->command->info('  - 4 support teams, 4 SLA policies, 6 categories, 8 tickets with replies, 4 KB articles');
        $this->command->info('  - 8 visitors, 5 meeting rooms, 8 visits, 5 meetings, 4 watchlist entries');
    }
}
