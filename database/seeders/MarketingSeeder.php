<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarketingSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $now = now();
        $adminId = 3;

        // ═══ Templates ═══
        $t1 = Str::uuid()->toString();
        $t2 = Str::uuid()->toString();
        $t3 = Str::uuid()->toString();
        $t4 = Str::uuid()->toString();
        $t5 = Str::uuid()->toString();
        $t6 = Str::uuid()->toString();

        DB::table('marketing_templates')->insertOrIgnore([
            ['id' => $t1, 'tenant_id' => $this->tenantId, 'name' => 'New Loan Product Launch', 'channel' => 'sms', 'subject' => null, 'body' => 'Dear {first_name}, exciting news! Our new {product_name} is now available with rates as low as {interest_rate}%. Apply today at your nearest branch. Demo MFB.', 'placeholders' => json_encode(['first_name','product_name','interest_rate']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(30), 'updated_at' => $now],
            ['id' => $t2, 'tenant_id' => $this->tenantId, 'name' => 'Deposit Mobilization Drive', 'channel' => 'sms', 'subject' => null, 'body' => 'Hi {first_name}, save more and earn more! Open a Target Savings account today and enjoy up to 8% annual interest. Visit Demo MFB.', 'placeholders' => json_encode(['first_name']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(25), 'updated_at' => $now],
            ['id' => $t3, 'tenant_id' => $this->tenantId, 'name' => 'Loan Repayment Reminder', 'channel' => 'sms', 'subject' => null, 'body' => 'Dear {first_name}, this is a friendly reminder that your loan repayment of N{amount} is due on {due_date}. Please ensure timely payment. Demo MFB.', 'placeholders' => json_encode(['first_name','amount','due_date']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(20), 'updated_at' => $now],
            ['id' => $t4, 'tenant_id' => $this->tenantId, 'name' => 'Welcome New Customer', 'channel' => 'email', 'subject' => 'Welcome to Demo Microfinance Bank!', 'body' => "Dear {first_name},\n\nWelcome to Demo Microfinance Bank! Your account ({account_number}) is now active.\n\nVisit our portal at app.bankostest.com.ng\n\nBest regards,\nDemo MFB Team", 'placeholders' => json_encode(['first_name','account_number']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(45), 'updated_at' => $now],
            ['id' => $t5, 'tenant_id' => $this->tenantId, 'name' => 'Insurance Awareness', 'channel' => 'sms', 'subject' => null, 'body' => 'Dear {first_name}, protect your family with our affordable insurance plans starting from N1,000/month. Ask about Credit Life, Health & Asset insurance at Demo MFB.', 'placeholders' => json_encode(['first_name']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(15), 'updated_at' => $now],
            ['id' => $t6, 'tenant_id' => $this->tenantId, 'name' => 'Birthday Greeting', 'channel' => 'sms', 'subject' => null, 'body' => 'Happy Birthday {first_name}! Wishing you a wonderful day from Demo MFB. As a gift, enjoy 50% discount on your next loan processing fee!', 'placeholders' => json_encode(['first_name']), 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(40), 'updated_at' => $now],
        ]);
        $this->command->info('Templates: 6');

        // ═══ Segments ═══
        $s1 = Str::uuid()->toString();
        $s2 = Str::uuid()->toString();
        $s3 = Str::uuid()->toString();
        $s4 = Str::uuid()->toString();
        $s5 = Str::uuid()->toString();
        $s6 = Str::uuid()->toString();

        DB::table('marketing_segments')->insertOrIgnore([
            ['id' => $s1, 'tenant_id' => $this->tenantId, 'name' => 'All Active Customers', 'description' => 'Every active customer', 'rules' => json_encode([['field'=>'status','operator'=>'equals','value'=>'active']]), 'is_system' => true, 'cached_count' => 62, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(60), 'updated_at' => $now],
            ['id' => $s2, 'tenant_id' => $this->tenantId, 'name' => 'High-Value Savers', 'description' => 'Balance above N500,000', 'rules' => json_encode([['field'=>'status','operator'=>'equals','value'=>'active'],['field'=>'available_balance','operator'=>'greater_than','value'=>'500000']]), 'is_system' => false, 'cached_count' => 28, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(30), 'updated_at' => $now],
            ['id' => $s3, 'tenant_id' => $this->tenantId, 'name' => 'Active Borrowers', 'description' => 'Customers with active loans', 'rules' => json_encode([['field'=>'has_loan','operator'=>'equals','value'=>'yes'],['field'=>'loan_status','operator'=>'equals','value'=>'active']]), 'is_system' => false, 'cached_count' => 26, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(25), 'updated_at' => $now],
            ['id' => $s4, 'tenant_id' => $this->tenantId, 'name' => 'Savings Only (No Loans)', 'description' => 'Cross-sell opportunity: savers without loans', 'rules' => json_encode([['field'=>'status','operator'=>'equals','value'=>'active'],['field'=>'has_loan','operator'=>'equals','value'=>'no']]), 'is_system' => false, 'cached_count' => 36, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(20), 'updated_at' => $now],
            ['id' => $s5, 'tenant_id' => $this->tenantId, 'name' => 'Female Customers', 'description' => 'Active female customers', 'rules' => json_encode([['field'=>'status','operator'=>'equals','value'=>'active'],['field'=>'gender','operator'=>'equals','value'=>'female']]), 'is_system' => false, 'cached_count' => 31, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(15), 'updated_at' => $now],
            ['id' => $s6, 'tenant_id' => $this->tenantId, 'name' => 'No Insurance', 'description' => 'Customers without insurance', 'rules' => json_encode([['field'=>'status','operator'=>'equals','value'=>'active'],['field'=>'has_insurance','operator'=>'equals','value'=>'no']]), 'is_system' => false, 'cached_count' => 45, 'count_computed_at' => $now, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(10), 'updated_at' => $now],
        ]);
        $this->command->info('Segments: 6');

        // ═══ Campaigns ═══
        DB::table('marketing_campaigns')->insertOrIgnore([
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'Q1 Loan Product Launch', 'description' => 'Announce new SME and Agric loan products', 'type' => 'broadcast', 'channel' => 'sms', 'template_id' => $t1, 'segment_id' => $s1, 'custom_message' => null, 'custom_subject' => null, 'status' => 'sent', 'scheduled_at' => $now->copy()->subDays(20), 'sent_at' => $now->copy()->subDays(20), 'completed_at' => $now->copy()->subDays(20), 'total_recipients' => 62, 'sent_count' => 62, 'delivered_count' => 58, 'opened_count' => 0, 'clicked_count' => 0, 'converted_count' => 8, 'failed_count' => 4, 'unsubscribed_count' => 1, 'cost' => 186.00, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(21), 'updated_at' => $now->copy()->subDays(20)],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'Cross-sell Loans to Savers', 'description' => 'Target savings-only customers with loan offers', 'type' => 'cross_sell', 'channel' => 'sms', 'template_id' => $t2, 'segment_id' => $s4, 'custom_message' => null, 'custom_subject' => null, 'status' => 'sent', 'scheduled_at' => $now->copy()->subDays(14), 'sent_at' => $now->copy()->subDays(14), 'completed_at' => $now->copy()->subDays(14), 'total_recipients' => 36, 'sent_count' => 36, 'delivered_count' => 34, 'opened_count' => 0, 'clicked_count' => 0, 'converted_count' => 5, 'failed_count' => 2, 'unsubscribed_count' => 0, 'cost' => 108.00, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(15), 'updated_at' => $now->copy()->subDays(14)],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'Insurance Awareness Week', 'description' => 'Promote insurance to uninsured customers', 'type' => 'broadcast', 'channel' => 'sms', 'template_id' => $t5, 'segment_id' => $s6, 'custom_message' => null, 'custom_subject' => null, 'status' => 'sent', 'scheduled_at' => $now->copy()->subDays(7), 'sent_at' => $now->copy()->subDays(7), 'completed_at' => $now->copy()->subDays(7), 'total_recipients' => 45, 'sent_count' => 45, 'delivered_count' => 42, 'opened_count' => 0, 'clicked_count' => 0, 'converted_count' => 3, 'failed_count' => 3, 'unsubscribed_count' => 0, 'cost' => 135.00, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(8), 'updated_at' => $now->copy()->subDays(7)],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'Welcome Email Series', 'description' => 'Auto welcome email to new customers', 'type' => 'event_triggered', 'channel' => 'email', 'template_id' => $t4, 'segment_id' => null, 'custom_message' => null, 'custom_subject' => null, 'status' => 'sent', 'scheduled_at' => null, 'sent_at' => $now->copy()->subDays(30), 'completed_at' => $now->copy()->subDays(1), 'total_recipients' => 15, 'sent_count' => 15, 'delivered_count' => 14, 'opened_count' => 11, 'clicked_count' => 6, 'converted_count' => 0, 'failed_count' => 1, 'unsubscribed_count' => 0, 'cost' => 0, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(35), 'updated_at' => $now->copy()->subDays(1)],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'April Deposit Drive', 'description' => 'Competitive rates for April deposits', 'type' => 'broadcast', 'channel' => 'sms', 'template_id' => $t2, 'segment_id' => $s1, 'custom_message' => null, 'custom_subject' => null, 'status' => 'scheduled', 'scheduled_at' => $now->copy()->addDays(2), 'sent_at' => null, 'completed_at' => null, 'total_recipients' => 62, 'sent_count' => 0, 'delivered_count' => 0, 'opened_count' => 0, 'clicked_count' => 0, 'converted_count' => 0, 'failed_count' => 0, 'unsubscribed_count' => 0, 'cost' => 0, 'created_by' => $adminId, 'created_at' => $now->copy()->subDays(1), 'updated_at' => $now],
            ['id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'name' => 'Women Empowerment Loan', 'description' => 'Special loan offer for female entrepreneurs', 'type' => 'broadcast', 'channel' => 'sms', 'template_id' => $t1, 'segment_id' => $s5, 'custom_message' => 'Dear {first_name}, as a valued female customer, you qualify for our Women Empowerment Loan at just 15% p.a. with no processing fee! Visit your branch. Demo MFB.', 'custom_subject' => null, 'status' => 'draft', 'scheduled_at' => null, 'sent_at' => null, 'completed_at' => null, 'total_recipients' => 31, 'sent_count' => 0, 'delivered_count' => 0, 'opened_count' => 0, 'clicked_count' => 0, 'converted_count' => 0, 'failed_count' => 0, 'unsubscribed_count' => 0, 'cost' => 0, 'created_by' => $adminId, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->command->info('Campaigns: 6');

        // ═══ Cross-sell Opportunities ═══
        $customers = DB::table('customers')->where('tenant_id', $this->tenantId)->where('status', 'active')->limit(12)->pluck('id')->toArray();
        $officers = DB::table('users')->where('tenant_id', $this->tenantId)->limit(5)->pluck('id')->toArray();

        $crossSells = [];
        foreach (array_slice($customers, 0, 5) as $i => $cid) {
            $crossSells[] = [
                'id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'customer_id' => $cid,
                'opportunity_type' => 'savings_to_loan', 'recommended_product' => 'SME Loan',
                'reason' => 'Consistent savings pattern with high balance. No loan history.',
                'estimated_value' => [1000000, 2000000, 500000, 1500000, 750000][$i],
                'status' => ['identified', 'contacted', 'interested', 'converted', 'identified'][$i],
                'assigned_to' => $officers[$i % count($officers)],
                'contacted_at' => $i < 3 ? $now->copy()->subDays(rand(1,10)) : null,
                'converted_at' => $i === 3 ? $now->copy()->subDays(2) : null,
                'created_at' => $now->copy()->subDays(15), 'updated_at' => $now,
            ];
        }
        foreach (array_slice($customers, 5, 4) as $i => $cid) {
            $crossSells[] = [
                'id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'customer_id' => $cid,
                'opportunity_type' => 'loan_to_insurance', 'recommended_product' => 'Credit Life Insurance',
                'reason' => 'Active loan without insurance coverage.',
                'estimated_value' => [5000, 10000, 7500, 3000][$i],
                'status' => ['identified', 'contacted', 'identified', 'declined'][$i],
                'assigned_to' => $officers[$i % count($officers)],
                'contacted_at' => $i === 1 ? $now->copy()->subDays(5) : null,
                'converted_at' => null,
                'created_at' => $now->copy()->subDays(10), 'updated_at' => $now,
            ];
        }
        foreach (array_slice($customers, 9, 3) as $i => $cid) {
            $crossSells[] = [
                'id' => Str::uuid()->toString(), 'tenant_id' => $this->tenantId, 'customer_id' => $cid,
                'opportunity_type' => 'dormant_reactivation', 'recommended_product' => 'Target Savings',
                'reason' => 'Account inactive for 90+ days.',
                'estimated_value' => [200000, 100000, 150000][$i],
                'status' => 'identified',
                'assigned_to' => $officers[$i % count($officers)],
                'contacted_at' => null, 'converted_at' => null,
                'created_at' => $now->copy()->subDays(5), 'updated_at' => $now,
            ];
        }
        DB::table('marketing_cross_sells')->insertOrIgnore($crossSells);
        $this->command->info('Cross-sells: ' . count($crossSells));

        $this->command->info('Marketing data seeded successfully!');
    }
}
