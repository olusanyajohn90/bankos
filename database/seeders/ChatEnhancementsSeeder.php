<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatEnhancementsSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';
    private Carbon $now;

    // Existing conversation IDs
    private string $mgmtTeamConvId   = '06ce0239-15f5-435e-a8ed-cda01c15eef4';
    private string $opsTeamConvId    = '2364c82c-0016-4402-95e0-37cfb9d2d89a';
    private string $loanCommConvId   = 'e03024d9-50f2-4486-a04d-118f40c5be9d';

    // Existing message IDs (for threads, reactions, pins, stars)
    private array $existingMsgIds;

    public function run(): void
    {
        $this->now = Carbon::now();

        // Collect existing message IDs for reference
        $this->existingMsgIds = DB::table('chat_messages')
            ->where('tenant_id', $this->tenantId)
            ->pluck('id')
            ->toArray();

        $this->seedChannels();
        $this->seedThreads();
        $this->seedReactions();
        $this->seedPinnedMessages();
        $this->seedStarredMessages();
        $this->seedPolls();
        $this->seedTasks();
        $this->seedUserGroups();
        $this->seedBookmarks();
        $this->seedReminders();
        $this->seedUserStatus();
        $this->seedCanvas();
        $this->seedWorkflows();
        $this->seedMentions();
        $this->seedCallHistory();
        $this->seedReadReceipts();
        $this->seedPresence();
    }

    // ── 1. Channels ─────────────────────────────────────────────────────
    private function seedChannels(): void
    {
        $generalId     = 'c1a00001-0000-4000-a000-000000000001';
        $loansDiscId   = 'c1a00001-0000-4000-a000-000000000002';
        $complianceId  = 'c1a00001-0000-4000-a000-000000000003';

        // Channel conversations
        DB::table('chat_conversations')->insertOrIgnore([
            [
                'id'                   => $generalId,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'channel',
                'name'                 => '#general',
                'description'          => 'Company-wide announcements and water-cooler chat',
                'topic'                => 'Company-wide announcements and discussions',
                'created_by'           => 3,
                'last_message_at'      => $this->now->copy()->subMinutes(15)->toDateTimeString(),
                'last_message_preview' => 'Reminder: Monthly town hall is this Friday at 3 PM.',
                'is_archived'          => false,
                'is_private'           => false,
                'invite_code'          => 'GEN' . strtoupper(Str::random(7)),
                'disappear_minutes'    => null,
                'created_at'           => $this->now->copy()->subMonths(3)->toDateTimeString(),
                'updated_at'           => $this->now->copy()->subMinutes(15)->toDateTimeString(),
            ],
            [
                'id'                   => $loansDiscId,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'channel',
                'name'                 => '#loans-discussion',
                'description'          => 'Discuss loan applications, policies and portfolio performance',
                'topic'                => 'Discuss loan applications and policies',
                'created_by'           => 4,
                'last_message_at'      => $this->now->copy()->subMinutes(30)->toDateTimeString(),
                'last_message_preview' => 'PAR ratio improved to 3.8% this week.',
                'is_archived'          => false,
                'is_private'           => false,
                'invite_code'          => 'LNS' . strtoupper(Str::random(7)),
                'disappear_minutes'    => null,
                'created_at'           => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at'           => $this->now->copy()->subMinutes(30)->toDateTimeString(),
            ],
            [
                'id'                   => $complianceId,
                'tenant_id'            => $this->tenantId,
                'type'                 => 'channel',
                'name'                 => '#compliance-alerts',
                'description'          => 'Regulatory updates, compliance notices and audit findings',
                'topic'                => 'Regulatory updates and compliance notices',
                'created_by'           => 2,
                'last_message_at'      => $this->now->copy()->subHour()->toDateTimeString(),
                'last_message_preview' => 'New CBN circular on PEP screening released today.',
                'is_archived'          => false,
                'is_private'           => true,
                'invite_code'          => null,
                'disappear_minutes'    => null,
                'created_at'           => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at'           => $this->now->copy()->subHour()->toDateTimeString(),
            ],
        ]);

        // Participants — #general: all users (2-23)
        $allUsers = range(2, 23);
        $generalParticipants = [];
        foreach ($allUsers as $uid) {
            $generalParticipants[] = [
                'conversation_id' => $generalId,
                'user_id'         => $uid,
                'role'            => $uid === 3 ? 'admin' : 'member',
                'joined_at'       => $this->now->copy()->subMonths(3)->toDateTimeString(),
                'last_read_at'    => $this->now->copy()->subMinutes(rand(5, 120))->toDateTimeString(),
                'left_at'         => null,
                'is_muted'        => false,
                'muted_until'     => null,
                'notify_level'    => 'all',
            ];
        }
        DB::table('chat_participants')->insertOrIgnore($generalParticipants);

        // #loans-discussion: loan officers + managers (4, 5-11, 12, 14, 16, 18, 20, 22)
        $loanUsers = array_merge([3, 4], range(5, 11), [12, 14, 16, 18, 20, 22]);
        $loanParticipants = [];
        foreach ($loanUsers as $uid) {
            $loanParticipants[] = [
                'conversation_id' => $loansDiscId,
                'user_id'         => $uid,
                'role'            => $uid === 4 ? 'admin' : 'member',
                'joined_at'       => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'last_read_at'    => $this->now->copy()->subMinutes(rand(10, 180))->toDateTimeString(),
                'left_at'         => null,
                'is_muted'        => false,
                'muted_until'     => null,
                'notify_level'    => 'all',
            ];
        }
        DB::table('chat_participants')->insertOrIgnore($loanParticipants);

        // #compliance-alerts: compliance (2) + admin (3) + managers (5-11)
        $complianceUsers = array_merge([2, 3], range(5, 11));
        $complianceParticipants = [];
        foreach ($complianceUsers as $uid) {
            $complianceParticipants[] = [
                'conversation_id' => $complianceId,
                'user_id'         => $uid,
                'role'            => $uid === 2 ? 'admin' : 'member',
                'joined_at'       => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'last_read_at'    => $this->now->copy()->subMinutes(rand(30, 240))->toDateTimeString(),
                'left_at'         => null,
                'is_muted'        => false,
                'muted_until'     => null,
                'notify_level'    => 'mentions',
            ];
        }
        DB::table('chat_participants')->insertOrIgnore($complianceParticipants);

        // Messages for #general (6 messages)
        $generalMessages = [
            ['sender' => 3,  'body' => 'Welcome to the #general channel! Use this space for company-wide announcements and discussions.',        'offset' => 180],
            ['sender' => 3,  'body' => 'Please ensure all branches complete their end-of-day reconciliation by 4 PM daily.',                     'offset' => 120],
            ['sender' => 5,  'body' => 'Good news: Head Office branch won the customer satisfaction award for Q1!',                               'offset' => 90],
            ['sender' => 2,  'body' => 'Reminder: All staff must complete the annual AML training by end of this month. Link: https://training.bankos.io/aml', 'offset' => 60],
            ['sender' => 6,  'body' => '@everyone The board meeting has been moved from Thursday to Friday this week.',                            'offset' => 30],
            ['sender' => 3,  'body' => 'Reminder: Monthly town hall is this Friday at 3 PM.',                                                     'offset' => 15],
        ];
        $this->insertChannelMessages($generalId, $generalMessages);

        // Messages for #loans-discussion (7 messages)
        $loanMessages = [
            ['sender' => 4,  'body' => 'Welcome to the loans discussion channel. Share updates on applications, policy changes and portfolio metrics here.',   'offset' => 300],
            ['sender' => 4,  'body' => 'New policy: All SME loans above N10M now require two independent collateral valuations.',                               'offset' => 240],
            ['sender' => 12, 'body' => 'I have three applications pending credit committee review. Can we schedule for tomorrow?',                              'offset' => 180],
            ['sender' => 5,  'body' => 'HO branch has disbursed N12M in micro-loans this month. Recovery rate at 96%.',                                         'offset' => 120],
            ['sender' => 16, 'body' => 'Question: What is the maximum DTI ratio we accept for salary earners?',                                                  'offset' => 75],
            ['sender' => 4,  'body' => 'DTI cap is 33% for salary earners, 40% for business owners with verified income. See policy doc in bookmarks.',          'offset' => 60],
            ['sender' => 4,  'body' => 'PAR ratio improved to 3.8% this week. Great work on collections, team!',                                                 'offset' => 30],
        ];
        $this->insertChannelMessages($loansDiscId, $loanMessages);

        // Messages for #compliance-alerts (5 messages)
        $complianceMessages = [
            ['sender' => 2,  'body' => 'This channel is for regulatory updates and compliance notices. Treat all information here as confidential.',  'offset' => 350],
            ['sender' => 2,  'body' => 'URGENT: CBN has updated the KYC requirements for Tier 3 accounts. Please review the attached circular.',      'offset' => 240],
            ['sender' => 2,  'body' => 'Quarterly AML report has been submitted to NFIU. No suspicious transactions flagged this period.',             'offset' => 150],
            ['sender' => 3,  'body' => '@managers Please ensure all branch staff have read the updated whistleblower policy.',                          'offset' => 90],
            ['sender' => 2,  'body' => 'New CBN circular on PEP screening released today. All branches must implement within 30 days.',                 'offset' => 60],
        ];
        $this->insertChannelMessages($complianceId, $complianceMessages);
    }

    private function insertChannelMessages(string $conversationId, array $messages): void
    {
        foreach ($messages as $msg) {
            $msgId = Str::uuid()->toString();
            DB::table('chat_messages')->insertOrIgnore([
                'id'              => $msgId,
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $conversationId,
                'sender_id'       => $msg['sender'],
                'reply_to_id'     => null,
                'body'            => $msg['body'],
                'type'            => 'text',
                'delivery_status' => 'delivered',
                'is_edited'       => false,
                'is_deleted'      => false,
                'is_disappearing' => false,
                'thread_id'             => null,
                'thread_reply_count'    => 0,
                'thread_last_reply_at'  => null,
                'is_scheduled'          => false,
                'created_at'      => $this->now->copy()->subMinutes($msg['offset'])->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subMinutes($msg['offset'])->toDateTimeString(),
            ]);
            // Track for later use
            $this->existingMsgIds[] = $msgId;
        }
    }

    // ── 2. Threads ──────────────────────────────────────────────────────
    private function seedThreads(): void
    {
        // Thread on "ATM at Ikeja Branch is showing a cash-low alert"
        $parentMsg1 = 'fede8c38-148a-4551-85fd-c0ebe7ffe683'; // Operations Team
        $threadReplies1 = [
            ['sender' => 5,  'body' => 'I have contacted the CIT company. They confirmed pickup at 11 AM.',     'offset' => 10],
            ['sender' => 6,  'body' => 'This is the third time this month. Should we increase the float?',       'offset' => 8],
            ['sender' => 3,  'body' => 'Good point. Let us review the ATM cash projection model this week.',      'offset' => 5],
        ];
        $this->createThread($parentMsg1, $this->opsTeamConvId, $threadReplies1);

        // Thread on "I recommend approval with a 90-day moratorium"
        $parentMsg2 = 'ad6c9d01-716d-4907-8b9c-1f4da01d9ff7'; // Loan Committee
        $threadReplies2 = [
            ['sender' => 3,  'body' => 'I approve. The risk assessment looks thorough.',                          'offset' => 15],
            ['sender' => 7,  'body' => 'Seconded. What is the expected drawdown timeline?',                        'offset' => 12],
            ['sender' => 4,  'body' => 'Full drawdown within 14 days of offer acceptance.',                        'offset' => 9],
        ];
        $this->createThread($parentMsg2, $this->loanCommConvId, $threadReplies2);

        // Thread on "Good morning team. Please share your branch updates"
        $parentMsg3 = '6adfad4b-d0e2-4981-a898-773759ea3e73'; // Management Team
        $threadReplies3 = [
            ['sender' => 7,  'body' => 'Abuja branch: 8 new accounts, 2 fixed deposits worth N5M.',               'offset' => 20],
            ['sender' => 5,  'body' => 'Head Office also processed 15 interbank transfers today.',                  'offset' => 18],
        ];
        $this->createThread($parentMsg3, $this->mgmtTeamConvId, $threadReplies3);
    }

    private function createThread(string $parentMsgId, string $convId, array $replies): void
    {
        $lastReplyAt = null;
        foreach ($replies as $reply) {
            $replyId = Str::uuid()->toString();
            $createdAt = $this->now->copy()->subMinutes($reply['offset'])->toDateTimeString();
            DB::table('chat_messages')->insertOrIgnore([
                'id'              => $replyId,
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $convId,
                'sender_id'       => $reply['sender'],
                'reply_to_id'     => null,
                'body'            => $reply['body'],
                'type'            => 'text',
                'delivery_status' => 'delivered',
                'is_edited'       => false,
                'is_deleted'      => false,
                'is_disappearing' => false,
                'thread_id'             => $parentMsgId,
                'thread_reply_count'    => 0,
                'thread_last_reply_at'  => null,
                'is_scheduled'          => false,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);
            $lastReplyAt = $createdAt;
            $this->existingMsgIds[] = $replyId;
        }

        // Update parent message thread metadata
        DB::table('chat_messages')
            ->where('id', $parentMsgId)
            ->update([
                'thread_reply_count'   => count($replies),
                'thread_last_reply_at' => $lastReplyAt,
            ]);
    }

    // ── 3. Reactions ────────────────────────────────────────────────────
    private function seedReactions(): void
    {
        $reactions = [
            // "Ikeja: We hit our deposit target!" — celebratory reactions
            ['message_id' => '0ef2af1f-f919-498a-9c96-9312c2ca5f8d', 'user_id' => 3, 'emoji' => "\u{1F44D}"],
            ['message_id' => '0ef2af1f-f919-498a-9c96-9312c2ca5f8d', 'user_id' => 5, 'emoji' => "\u{2764}"],
            ['message_id' => '0ef2af1f-f919-498a-9c96-9312c2ca5f8d', 'user_id' => 7, 'emoji' => "\u{1F44D}"],

            // "Excellent work everyone. Keep pushing on the deposit mobilisation."
            ['message_id' => 'e31f0331-7234-40a5-895f-d20e3dd5b958', 'user_id' => 5, 'emoji' => "\u{1F64F}"],
            ['message_id' => 'e31f0331-7234-40a5-895f-d20e3dd5b958', 'user_id' => 6, 'emoji' => "\u{1F44D}"],
            ['message_id' => 'e31f0331-7234-40a5-895f-d20e3dd5b958', 'user_id' => 4, 'emoji' => "\u{1F64F}"],

            // "The SME facility for Eze Holdings has been approved."
            ['message_id' => 'a31bef3c-36f8-42e4-9189-f93a88dcc94f', 'user_id' => 3, 'emoji' => "\u{1F44D}"],
            ['message_id' => 'a31bef3c-36f8-42e4-9189-f93a88dcc94f', 'user_id' => 5, 'emoji' => "\u{1F44D}"],
            ['message_id' => 'a31bef3c-36f8-42e4-9189-f93a88dcc94f', 'user_id' => 6, 'emoji' => "\u{1F44D}"],

            // "Approved. Good risk profile."
            ['message_id' => '6acfb933-cfc9-472a-8e34-03e74b688cd8', 'user_id' => 4, 'emoji' => "\u{1F44D}"],
            ['message_id' => '6acfb933-cfc9-472a-8e34-03e74b688cd8', 'user_id' => 3, 'emoji' => "\u{2764}"],

            // "Loan recovery rate is at 94% this month."
            ['message_id' => '022314cc-97d6-4be7-8f4b-ee7dabf73ebb', 'user_id' => 3, 'emoji' => "\u{1F44D}"],
            ['message_id' => '022314cc-97d6-4be7-8f4b-ee7dabf73ebb', 'user_id' => 6, 'emoji' => "\u{1F602}"],

            // "GL reconciliation is done for today."
            ['message_id' => '6d7a1600-2c22-4f45-ad4a-de1c0e37d3b6', 'user_id' => 2, 'emoji' => "\u{1F44D}"],
        ];

        foreach ($reactions as $r) {
            DB::table('chat_reactions')->insertOrIgnore([
                'message_id' => $r['message_id'],
                'user_id'    => $r['user_id'],
                'emoji'      => $r['emoji'],
                'created_at' => $this->now->copy()->subMinutes(rand(5, 120))->toDateTimeString(),
            ]);
        }
    }

    // ── 4. Pinned Messages ──────────────────────────────────────────────
    private function seedPinnedMessages(): void
    {
        $pins = [
            // Pin the loan approval message in Loan Committee
            ['conv' => $this->loanCommConvId, 'msg' => 'a31bef3c-36f8-42e4-9189-f93a88dcc94f', 'by' => 4],
            // Pin the deposit target message in Management Team
            ['conv' => $this->mgmtTeamConvId, 'msg' => '0ef2af1f-f919-498a-9c96-9312c2ca5f8d', 'by' => 3],
            // Pin the reconciliation reminder in Operations Team
            ['conv' => $this->opsTeamConvId,  'msg' => '97cfd4e3-024e-4396-91d7-ca2de7b1476e', 'by' => 2],
        ];

        foreach ($pins as $pin) {
            DB::table('chat_pinned_messages')->insertOrIgnore([
                'conversation_id' => $pin['conv'],
                'message_id'      => $pin['msg'],
                'pinned_by'       => $pin['by'],
                'pinned_at'       => $this->now->copy()->subHours(rand(1, 24))->toDateTimeString(),
            ]);
        }
    }

    // ── 5. Starred Messages ─────────────────────────────────────────────
    private function seedStarredMessages(): void
    {
        $starredMsgIds = [
            'e8b30f38-68bd-48f9-8332-b77832733e28', // "The new loan product rates have been approved by the board."
            '0ef2af1f-f919-498a-9c96-9312c2ca5f8d', // "Ikeja: We hit our deposit target!"
            'a31bef3c-36f8-42e4-9189-f93a88dcc94f', // "The SME facility for Eze Holdings has been approved."
            '8d91a6d6-59e1-49f7-b691-95142d24326b', // "AML screening is up to date."
        ];

        foreach ($starredMsgIds as $msgId) {
            DB::table('chat_starred_messages')->insertOrIgnore([
                'message_id' => $msgId,
                'user_id'    => 3,
                'created_at' => $this->now->copy()->subHours(rand(1, 48))->toDateTimeString(),
            ]);
        }
    }

    // ── 6. Polls ────────────────────────────────────────────────────────
    private function seedPolls(): void
    {
        // Poll 1: Banking hours — in Operations Team
        $poll1Id   = 'a0110001-0000-4000-a000-000000000001';
        $poll1MsgId = Str::uuid()->toString();

        DB::table('chat_messages')->insertOrIgnore([
            'id'              => $poll1MsgId,
            'tenant_id'       => $this->tenantId,
            'conversation_id' => $this->opsTeamConvId,
            'sender_id'       => 3,
            'body'            => '[Poll] Should we extend Friday banking hours?',
            'type'            => 'poll',
            'delivery_status' => 'delivered',
            'is_edited'       => false,
            'is_deleted'      => false,
            'is_disappearing' => false,
            'thread_id'             => null,
            'thread_reply_count'    => 0,
            'thread_last_reply_at'  => null,
            'is_scheduled'          => false,
            'created_at'      => $this->now->copy()->subDays(2)->toDateTimeString(),
            'updated_at'      => $this->now->copy()->subDays(2)->toDateTimeString(),
        ]);

        DB::table('chat_polls')->insertOrIgnore([
            'id'              => $poll1Id,
            'message_id'      => $poll1MsgId,
            'conversation_id' => $this->opsTeamConvId,
            'question'        => 'Should we extend Friday banking hours?',
            'allow_multiple'  => false,
            'is_anonymous'    => false,
            'is_closed'       => false,
            'closes_at'       => $this->now->copy()->addDays(5)->toDateTimeString(),
            'created_at'      => $this->now->copy()->subDays(2)->toDateTimeString(),
            'updated_at'      => $this->now->copy()->subDays(2)->toDateTimeString(),
        ]);

        $opt1a = $this->insertPollOption($poll1Id, 'Yes, until 5pm', 1);
        $opt1b = $this->insertPollOption($poll1Id, 'Yes, until 6pm', 2);
        $opt1c = $this->insertPollOption($poll1Id, 'No, keep current hours', 3);

        // Votes for poll 1
        $this->insertPollVote($poll1Id, $opt1a, 5);
        $this->insertPollVote($poll1Id, $opt1a, 6);
        $this->insertPollVote($poll1Id, $opt1b, 4);
        $this->insertPollVote($poll1Id, $opt1b, 7);
        $this->insertPollVote($poll1Id, $opt1b, 3);
        $this->insertPollVote($poll1Id, $opt1c, 2);

        // Poll 2: Training day — in Management Team
        $poll2Id   = 'a0110001-0000-4000-a000-000000000002';
        $poll2MsgId = Str::uuid()->toString();

        DB::table('chat_messages')->insertOrIgnore([
            'id'              => $poll2MsgId,
            'tenant_id'       => $this->tenantId,
            'conversation_id' => $this->mgmtTeamConvId,
            'sender_id'       => 3,
            'body'            => '[Poll] Preferred training day next month?',
            'type'            => 'poll',
            'delivery_status' => 'delivered',
            'is_edited'       => false,
            'is_deleted'      => false,
            'is_disappearing' => false,
            'thread_id'             => null,
            'thread_reply_count'    => 0,
            'thread_last_reply_at'  => null,
            'is_scheduled'          => false,
            'created_at'      => $this->now->copy()->subDay()->toDateTimeString(),
            'updated_at'      => $this->now->copy()->subDay()->toDateTimeString(),
        ]);

        DB::table('chat_polls')->insertOrIgnore([
            'id'              => $poll2Id,
            'message_id'      => $poll2MsgId,
            'conversation_id' => $this->mgmtTeamConvId,
            'question'        => 'Preferred training day next month?',
            'allow_multiple'  => false,
            'is_anonymous'    => false,
            'is_closed'       => false,
            'closes_at'       => $this->now->copy()->addDays(7)->toDateTimeString(),
            'created_at'      => $this->now->copy()->subDay()->toDateTimeString(),
            'updated_at'      => $this->now->copy()->subDay()->toDateTimeString(),
        ]);

        $opt2a = $this->insertPollOption($poll2Id, 'Monday', 1);
        $opt2b = $this->insertPollOption($poll2Id, 'Wednesday', 2);
        $opt2c = $this->insertPollOption($poll2Id, 'Friday', 3);

        // Votes for poll 2
        $this->insertPollVote($poll2Id, $opt2a, 5);
        $this->insertPollVote($poll2Id, $opt2b, 2);
        $this->insertPollVote($poll2Id, $opt2b, 4);
        $this->insertPollVote($poll2Id, $opt2b, 6);
        $this->insertPollVote($poll2Id, $opt2c, 3);
        $this->insertPollVote($poll2Id, $opt2c, 7);
    }

    private function insertPollOption(string $pollId, string $text, int $sortOrder): int
    {
        return DB::table('chat_poll_options')->insertGetId([
            'poll_id'    => $pollId,
            'text'       => $text,
            'sort_order' => $sortOrder,
        ]);
    }

    private function insertPollVote(string $pollId, int $optionId, int $userId): void
    {
        DB::table('chat_poll_votes')->insertOrIgnore([
            'poll_id'    => $pollId,
            'option_id'  => $optionId,
            'user_id'    => $userId,
            'created_at' => $this->now->copy()->subHours(rand(1, 36))->toDateTimeString(),
        ]);
    }

    // ── 7. Tasks ────────────────────────────────────────────────────────
    private function seedTasks(): void
    {
        // Task messages
        $taskMsgs = [
            ['id' => Str::uuid()->toString(), 'conv' => $this->loanCommConvId, 'sender' => 3, 'body' => '[Task] Review Q1 loan applications — assigned to Loan Officer'],
            ['id' => Str::uuid()->toString(), 'conv' => $this->mgmtTeamConvId,  'sender' => 3, 'body' => '[Task] Update KYC records for flagged accounts — assigned to Compliance'],
            ['id' => Str::uuid()->toString(), 'conv' => $this->mgmtTeamConvId,  'sender' => 3, 'body' => '[Task] Prepare NDIC monthly report'],
            ['id' => Str::uuid()->toString(), 'conv' => $this->opsTeamConvId,   'sender' => 3, 'body' => '[Task] Fix ATM dispenser at Ikeja branch — URGENT'],
        ];

        foreach ($taskMsgs as $tm) {
            DB::table('chat_messages')->insertOrIgnore([
                'id'              => $tm['id'],
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $tm['conv'],
                'sender_id'       => $tm['sender'],
                'body'            => $tm['body'],
                'type'            => 'task',
                'delivery_status' => 'delivered',
                'is_edited'       => false,
                'is_deleted'      => false,
                'is_disappearing' => false,
                'thread_id'             => null,
                'thread_reply_count'    => 0,
                'thread_last_reply_at'  => null,
                'is_scheduled'          => false,
                'created_at'      => $this->now->copy()->subDays(rand(1, 5))->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subDays(rand(1, 5))->toDateTimeString(),
            ]);
        }

        $tasks = [
            [
                'id'           => Str::uuid()->toString(),
                'message_id'   => $taskMsgs[0]['id'],
                'conversation_id' => $this->loanCommConvId,
                'title'        => 'Review Q1 loan applications',
                'description'  => 'Go through all pending Q1 loan applications and prepare summary report with recommendations for the credit committee.',
                'assigned_to'  => 4,
                'created_by'   => 3,
                'priority'     => 'high',
                'status'       => 'in_progress',
                'due_date'     => $this->now->copy()->addDay()->toDateString(),
                'completed_at' => null,
            ],
            [
                'id'           => Str::uuid()->toString(),
                'message_id'   => $taskMsgs[1]['id'],
                'conversation_id' => $this->mgmtTeamConvId,
                'title'        => 'Update KYC records for flagged accounts',
                'description'  => 'Review and update KYC documentation for the 12 accounts flagged during the quarterly AML review.',
                'assigned_to'  => 2,
                'created_by'   => 3,
                'priority'     => 'medium',
                'status'       => 'pending',
                'due_date'     => $this->now->copy()->addDays(3)->toDateString(),
                'completed_at' => null,
            ],
            [
                'id'           => Str::uuid()->toString(),
                'message_id'   => $taskMsgs[2]['id'],
                'conversation_id' => $this->mgmtTeamConvId,
                'title'        => 'Prepare NDIC monthly report',
                'description'  => 'Compile monthly returns for NDIC submission including deposit insurance premiums and risk assessment data.',
                'assigned_to'  => 3,
                'created_by'   => 3,
                'priority'     => 'high',
                'status'       => 'pending',
                'due_date'     => $this->now->copy()->endOfWeek()->toDateString(),
                'completed_at' => null,
            ],
            [
                'id'           => Str::uuid()->toString(),
                'message_id'   => $taskMsgs[3]['id'],
                'conversation_id' => $this->opsTeamConvId,
                'title'        => 'Fix ATM dispenser at Ikeja branch',
                'description'  => 'ATM cash dispenser is jamming intermittently. Vendor has been contacted. Ensure fix is completed today.',
                'assigned_to'  => 6,
                'created_by'   => 3,
                'priority'     => 'urgent',
                'status'       => 'completed',
                'due_date'     => $this->now->copy()->subDay()->toDateString(),
                'completed_at' => $this->now->copy()->subDay()->addHours(4)->toDateTimeString(),
            ],
        ];

        foreach ($tasks as $task) {
            DB::table('chat_tasks')->insertOrIgnore(array_merge($task, [
                'tenant_id'  => $this->tenantId,
                'created_at' => $this->now->copy()->subDays(rand(1, 5))->toDateTimeString(),
                'updated_at' => $this->now->copy()->subHours(rand(1, 24))->toDateTimeString(),
            ]));
        }
    }

    // ── 8. User Groups ──────────────────────────────────────────────────
    private function seedUserGroups(): void
    {
        $groups = [
            [
                'id'          => 'b0110001-0000-4000-a000-000000000001',
                'name'        => 'Managers',
                'handle'      => 'managers',
                'description' => 'All branch managers',
                'members'     => range(5, 11),
            ],
            [
                'id'          => 'b0110001-0000-4000-a000-000000000002',
                'name'        => 'Loan Officers',
                'handle'      => 'loan-officers',
                'description' => 'All loan officers across branches',
                'members'     => [4, 12, 14, 16, 18, 20, 22],
            ],
            [
                'id'          => 'b0110001-0000-4000-a000-000000000003',
                'name'        => 'Tellers',
                'handle'      => 'tellers',
                'description' => 'All tellers across branches',
                'members'     => [13, 15, 17, 19, 21, 23],
            ],
        ];

        foreach ($groups as $group) {
            $members = $group['members'];
            unset($group['members']);

            DB::table('chat_user_groups')->insertOrIgnore(array_merge($group, [
                'tenant_id'  => $this->tenantId,
                'created_by' => 3,
                'created_at' => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subMonths(2)->toDateTimeString(),
            ]));

            $memberRows = [];
            foreach ($members as $uid) {
                $memberRows[] = [
                    'group_id' => $group['id'],
                    'user_id'  => $uid,
                    'added_at' => $this->now->copy()->subMonths(2)->toDateTimeString(),
                ];
            }
            DB::table('chat_user_group_members')->insertOrIgnore($memberRows);
        }
    }

    // ── 9. Bookmarks ────────────────────────────────────────────────────
    private function seedBookmarks(): void
    {
        $bookmarks = [
            [
                'id'              => Str::uuid()->toString(),
                'conversation_id' => $this->mgmtTeamConvId,
                'created_by'      => 3,
                'title'           => 'CBN Regulations Portal',
                'url'             => 'https://www.cbn.gov.ng/supervision/mfbregulations.asp',
                'message_id'      => null,
                'sort_order'      => 1,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'conversation_id' => 'c1a00001-0000-4000-a000-000000000002', // #loans-discussion
                'created_by'      => 4,
                'title'           => 'Loan Policy Document',
                'url'             => 'https://docs.bankos.io/policies/loan-policy-v3.pdf',
                'message_id'      => null,
                'sort_order'      => 1,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'conversation_id' => $this->opsTeamConvId,
                'created_by'      => 2,
                'title'           => 'Daily Rates Dashboard',
                'url'             => 'https://app.bankos.io/treasury/rates',
                'message_id'      => null,
                'sort_order'      => 1,
            ],
            [
                'id'              => Str::uuid()->toString(),
                'conversation_id' => 'c1a00001-0000-4000-a000-000000000003', // #compliance-alerts
                'created_by'      => 2,
                'title'           => 'NFIU Reporting Portal',
                'url'             => 'https://nfiu.gov.ng/reporting',
                'message_id'      => null,
                'sort_order'      => 2,
            ],
        ];

        foreach ($bookmarks as $bm) {
            DB::table('chat_bookmarks')->insertOrIgnore(array_merge($bm, [
                'created_at' => $this->now->copy()->subDays(rand(1, 30))->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(rand(1, 30))->toDateTimeString(),
            ]));
        }
    }

    // ── 10. Reminders ───────────────────────────────────────────────────
    private function seedReminders(): void
    {
        DB::table('chat_reminders')->insertOrIgnore([
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'user_id'         => 3,
                'conversation_id' => $this->mgmtTeamConvId,
                'message_id'      => null,
                'note'            => 'Follow up on Ikeja branch audit findings',
                'remind_at'       => $this->now->copy()->addHours(2)->toDateTimeString(),
                'is_fired'        => false,
                'created_at'      => $this->now->copy()->subHour()->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subHour()->toDateTimeString(),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'user_id'         => 3,
                'conversation_id' => 'c1a00001-0000-4000-a000-000000000003', // #compliance-alerts
                'message_id'      => null,
                'note'            => 'Submit monthly compliance report to CBN',
                'remind_at'       => $this->now->copy()->addDay()->setHour(9)->setMinute(0)->setSecond(0)->toDateTimeString(),
                'is_fired'        => false,
                'created_at'      => $this->now->copy()->subMinutes(30)->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subMinutes(30)->toDateTimeString(),
            ],
        ]);
    }

    // ── 11. User Status ─────────────────────────────────────────────────
    private function seedUserStatus(): void
    {
        // Admin: In a meeting
        DB::table('users')->where('id', 3)->update([
            'chat_status_emoji' => "\u{1F3E2}",
            'chat_status_text'  => 'In a meeting',
            'chat_status_until' => $this->now->copy()->addHours(2)->toDateTimeString(),
            'chat_dnd_until'    => null,
        ]);

        // Loan Officer: Reviewing applications
        DB::table('users')->where('id', 4)->update([
            'chat_status_emoji' => "\u{1F4CB}",
            'chat_status_text'  => 'Reviewing applications',
            'chat_status_until' => null,
            'chat_dnd_until'    => null,
        ]);

        // Compliance Officer: Audit in progress
        DB::table('users')->where('id', 2)->update([
            'chat_status_emoji' => "\u{1F512}",
            'chat_status_text'  => 'Audit in progress',
            'chat_status_until' => null,
            'chat_dnd_until'    => null,
        ]);
    }

    // ── 12. Canvas / Docs ───────────────────────────────────────────────
    private function seedCanvas(): void
    {
        DB::table('chat_canvas')->insertOrIgnore([
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $this->mgmtTeamConvId,
                'title'           => 'Q1 2026 Lending Strategy',
                'content'         => json_encode([
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Q1 2026 Lending Strategy']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Objectives']]],
                        ['type' => 'bulletList', 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Grow loan portfolio by 15% while maintaining PAR below 5%']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Launch new SME loan product with competitive rates (18-22%)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Expand agricultural lending to 3 new LGAs']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Reduce average loan processing time from 5 days to 3 days']]]]],
                        ]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Risk Mitigation']]],
                        ['type' => 'bulletList', 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Mandatory credit bureau check for all loans above N500K']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Two independent collateral valuations for facilities above N10M']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Quarterly portfolio stress testing against CBN scenarios']]]]],
                        ]],
                    ],
                ]),
                'created_by'      => 3,
                'last_edited_by'  => 3,
                'is_shared'       => true,
                'created_at'      => $this->now->copy()->subWeek()->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subDays(2)->toDateTimeString(),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'conversation_id' => $this->opsTeamConvId,
                'title'           => 'Branch Operations SOP',
                'content'         => json_encode([
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Branch Operations Standard Operating Procedures']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Daily Opening Procedures']]],
                        ['type' => 'orderedList', 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Vault opening: Two-key control with Branch Manager and Head Teller']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Cash count and reconciliation against overnight balance']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'System login and verify all teller terminals are operational']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'ATM cash verification and receipt paper check']]]]],
                        ]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'End-of-Day Checklist']]],
                        ['type' => 'orderedList', 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'All tellers balanced and cash returned to vault']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Suspense accounts cleared (deadline: 3 PM)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'GL reconciliation completed and signed off']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'CBN returns file uploaded to NIBSS portal']]]]],
                        ]],
                    ],
                ]),
                'created_by'      => 2,
                'last_edited_by'  => 5,
                'is_shared'       => true,
                'created_at'      => $this->now->copy()->subWeeks(2)->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subDays(3)->toDateTimeString(),
            ],
        ]);
    }

    // ── 13. Workflows ───────────────────────────────────────────────────
    private function seedWorkflows(): void
    {
        DB::table('chat_workflows')->insertOrIgnore([
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'name'            => 'Welcome New Member',
                'description'     => 'Automatically greet new members when they join a channel',
                'created_by'      => 3,
                'is_active'       => true,
                'trigger'         => json_encode(['type' => 'new_member']),
                'steps'           => json_encode([
                    [
                        'action' => 'send_message',
                        'config' => [
                            'message' => 'Welcome to the team! Please introduce yourself and let us know your role and branch.',
                        ],
                    ],
                ]),
                'conversation_id' => 'c1a00001-0000-4000-a000-000000000001', // #general
                'run_count'       => 5,
                'last_run_at'     => $this->now->copy()->subDays(3)->toDateTimeString(),
                'created_at'      => $this->now->copy()->subMonths(2)->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subDays(3)->toDateTimeString(),
            ],
            [
                'id'              => Str::uuid()->toString(),
                'tenant_id'       => $this->tenantId,
                'name'            => 'Loan Approval Alert',
                'description'     => 'Celebrate when a loan is approved by reacting and sending a congratulatory message',
                'created_by'      => 3,
                'is_active'       => true,
                'trigger'         => json_encode([
                    'type'  => 'message_contains',
                    'value' => 'loan approved',
                ]),
                'steps'           => json_encode([
                    [
                        'action' => 'add_reaction',
                        'config' => ['emoji' => "\u{1F389}"],
                    ],
                    [
                        'action' => 'send_message',
                        'config' => [
                            'message' => 'Great news! Another loan approved. Keep up the excellent work, team!',
                        ],
                    ],
                ]),
                'conversation_id' => 'c1a00001-0000-4000-a000-000000000002', // #loans-discussion
                'run_count'       => 2,
                'last_run_at'     => $this->now->copy()->subDay()->toDateTimeString(),
                'created_at'      => $this->now->copy()->subMonth()->toDateTimeString(),
                'updated_at'      => $this->now->copy()->subDay()->toDateTimeString(),
            ],
        ]);
    }

    // ── 14. Mentions ────────────────────────────────────────────────────
    private function seedMentions(): void
    {
        // Get some channel messages that contain @mentions
        // We know the #compliance-alerts has "@managers" and the #general has "@everyone"
        $complianceChannelId = 'c1a00001-0000-4000-a000-000000000003';
        $generalChannelId    = 'c1a00001-0000-4000-a000-000000000001';

        // Find the message with "@managers" in compliance channel
        $managersMsg = DB::table('chat_messages')
            ->where('conversation_id', $complianceChannelId)
            ->where('body', 'like', '%@managers%')
            ->first();

        if ($managersMsg) {
            // Create a mention for each manager
            foreach (range(5, 11) as $managerId) {
                DB::table('chat_mentions')->insertOrIgnore([
                    'message_id'        => $managersMsg->id,
                    'conversation_id'   => $complianceChannelId,
                    'mentioned_user_id' => $managerId,
                    'mention_type'      => 'group',
                    'is_read'           => $managerId <= 7, // first 3 have read it
                    'created_at'        => $managersMsg->created_at,
                ]);
            }
        }

        // Find the message with "@everyone" in general channel
        $everyoneMsg = DB::table('chat_messages')
            ->where('conversation_id', $generalChannelId)
            ->where('body', 'like', '%@everyone%')
            ->first();

        if ($everyoneMsg) {
            // Create a mention for a subset of users (simulating @here / @channel)
            foreach ([2, 4, 5, 6, 7, 8, 9, 10, 11] as $uid) {
                DB::table('chat_mentions')->insertOrIgnore([
                    'message_id'        => $everyoneMsg->id,
                    'conversation_id'   => $generalChannelId,
                    'mentioned_user_id' => $uid,
                    'mention_type'      => 'channel',
                    'is_read'           => $uid <= 6,
                    'created_at'        => $everyoneMsg->created_at,
                ]);
            }
        }
    }

    // ── 15. Call History ────────────────────────────────────────────────
    private function seedCallHistory(): void
    {
        // Call 1: Audio call between admin (3) and loan officer (4)
        $call1Id = Str::uuid()->toString();
        $call1Start = $this->now->copy()->subHours(3);
        DB::table('chat_calls')->insertOrIgnore([
            'id'               => $call1Id,
            'tenant_id'        => $this->tenantId,
            'conversation_id'  => '67cacf5b-cd5e-4929-b9df-3edced5f4f15', // direct between admin and loan officer
            'initiated_by'     => 3,
            'livekit_room_name' => 'call-' . Str::random(12),
            'type'             => 'audio',
            'status'           => 'ended',
            'started_at'       => $call1Start->toDateTimeString(),
            'ended_at'         => $call1Start->copy()->addMinutes(5)->toDateTimeString(),
            'duration_seconds' => 300,
            'created_at'       => $call1Start->toDateTimeString(),
            'updated_at'       => $call1Start->copy()->addMinutes(5)->toDateTimeString(),
        ]);

        DB::table('chat_call_participants')->insertOrIgnore([
            [
                'call_id'           => $call1Id,
                'user_id'           => 3,
                'joined_at'         => $call1Start->toDateTimeString(),
                'left_at'           => $call1Start->copy()->addMinutes(5)->toDateTimeString(),
                'is_muted'          => false,
                'is_video_on'       => false,
                'is_screen_sharing' => false,
            ],
            [
                'call_id'           => $call1Id,
                'user_id'           => 4,
                'joined_at'         => $call1Start->copy()->addSeconds(10)->toDateTimeString(),
                'left_at'           => $call1Start->copy()->addMinutes(5)->toDateTimeString(),
                'is_muted'          => false,
                'is_video_on'       => false,
                'is_screen_sharing' => false,
            ],
        ]);

        // Call 2: Video call in Management Team group
        $call2Id = Str::uuid()->toString();
        $call2Start = $this->now->copy()->subDay()->setHour(14);
        DB::table('chat_calls')->insertOrIgnore([
            'id'               => $call2Id,
            'tenant_id'        => $this->tenantId,
            'conversation_id'  => $this->mgmtTeamConvId,
            'initiated_by'     => 3,
            'livekit_room_name' => 'call-' . Str::random(12),
            'type'             => 'video',
            'status'           => 'ended',
            'started_at'       => $call2Start->toDateTimeString(),
            'ended_at'         => $call2Start->copy()->addMinutes(15)->toDateTimeString(),
            'duration_seconds' => 900,
            'created_at'       => $call2Start->toDateTimeString(),
            'updated_at'       => $call2Start->copy()->addMinutes(15)->toDateTimeString(),
        ]);

        DB::table('chat_call_participants')->insertOrIgnore([
            [
                'call_id'           => $call2Id,
                'user_id'           => 3,
                'joined_at'         => $call2Start->toDateTimeString(),
                'left_at'           => $call2Start->copy()->addMinutes(15)->toDateTimeString(),
                'is_muted'          => false,
                'is_video_on'       => true,
                'is_screen_sharing' => false,
            ],
            [
                'call_id'           => $call2Id,
                'user_id'           => 2,
                'joined_at'         => $call2Start->copy()->addSeconds(15)->toDateTimeString(),
                'left_at'           => $call2Start->copy()->addMinutes(15)->toDateTimeString(),
                'is_muted'          => false,
                'is_video_on'       => true,
                'is_screen_sharing' => false,
            ],
            [
                'call_id'           => $call2Id,
                'user_id'           => 5,
                'joined_at'         => $call2Start->copy()->addMinutes(1)->toDateTimeString(),
                'left_at'           => $call2Start->copy()->addMinutes(14)->toDateTimeString(),
                'is_muted'          => false,
                'is_video_on'       => true,
                'is_screen_sharing' => false,
            ],
            [
                'call_id'           => $call2Id,
                'user_id'           => 6,
                'joined_at'         => $call2Start->copy()->addMinutes(2)->toDateTimeString(),
                'left_at'           => $call2Start->copy()->addMinutes(15)->toDateTimeString(),
                'is_muted'          => true,
                'is_video_on'       => false,
                'is_screen_sharing' => false,
            ],
        ]);
    }

    // ── 16. Read Receipts ───────────────────────────────────────────────
    private function seedReadReceipts(): void
    {
        // Get recent messages (last 20)
        $recentMessages = DB::table('chat_messages')
            ->where('tenant_id', $this->tenantId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $receipts = [];
        foreach ($recentMessages as $msg) {
            // Admin (3) has read everything
            $receipts[] = [
                'message_id' => $msg->id,
                'user_id'    => 3,
                'read_at'    => Carbon::parse($msg->created_at)->addMinutes(rand(1, 30))->toDateTimeString(),
            ];

            // Loan Officer (4) has read most
            if (rand(1, 10) <= 8) {
                $receipts[] = [
                    'message_id' => $msg->id,
                    'user_id'    => 4,
                    'read_at'    => Carbon::parse($msg->created_at)->addMinutes(rand(5, 60))->toDateTimeString(),
                ];
            }

            // Compliance (2) has read most
            if (rand(1, 10) <= 7) {
                $receipts[] = [
                    'message_id' => $msg->id,
                    'user_id'    => 2,
                    'read_at'    => Carbon::parse($msg->created_at)->addMinutes(rand(10, 90))->toDateTimeString(),
                ];
            }

            // Random other users have read some
            foreach ([5, 6, 7] as $uid) {
                if (rand(1, 10) <= 5) {
                    $receipts[] = [
                        'message_id' => $msg->id,
                        'user_id'    => $uid,
                        'read_at'    => Carbon::parse($msg->created_at)->addMinutes(rand(15, 120))->toDateTimeString(),
                    ];
                }
            }
        }

        // Also update delivery_status on those messages
        DB::table('chat_messages')
            ->where('tenant_id', $this->tenantId)
            ->update(['delivery_status' => 'read']);

        // Insert in chunks to avoid huge single insert
        foreach (array_chunk($receipts, 50) as $chunk) {
            DB::table('chat_read_receipts')->insertOrIgnore($chunk);
        }
    }

    // ── 17. Presence ────────────────────────────────────────────────────
    private function seedPresence(): void
    {
        $onlineUsers = [
            ['user_id' => 3, 'last_seen_at' => $this->now->copy()->subSeconds(30)->toDateTimeString()],
            ['user_id' => 4, 'last_seen_at' => $this->now->copy()->subMinute()->toDateTimeString()],
            ['user_id' => 2, 'last_seen_at' => $this->now->copy()->subMinutes(2)->toDateTimeString()],
            ['user_id' => 5, 'last_seen_at' => $this->now->copy()->subMinutes(5)->toDateTimeString()],
            ['user_id' => 6, 'last_seen_at' => $this->now->copy()->subMinutes(15)->toDateTimeString()],
            ['user_id' => 7, 'last_seen_at' => $this->now->copy()->subMinutes(30)->toDateTimeString()],
        ];

        foreach ($onlineUsers as $presence) {
            // Use upsert-like approach: delete then insert since it's a primary key table
            DB::table('chat_presence')
                ->where('user_id', $presence['user_id'])
                ->delete();

            DB::table('chat_presence')->insert([
                'user_id'      => $presence['user_id'],
                'last_seen_at' => $presence['last_seen_at'],
                'typing_in'    => null,
                'typing_at'    => null,
            ]);
        }
    }
}
