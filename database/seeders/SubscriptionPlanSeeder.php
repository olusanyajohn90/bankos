<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'id'                       => Str::uuid(),
                'name'                     => 'Starter',
                'slug'                     => 'starter',
                'price_monthly'            => 15000.00,
                'price_yearly'             => 25000.00,
                'max_customers'            => 1000,
                'max_staff_users'          => 5,
                'max_branches'             => 1,
                'max_transactions_monthly' => 10000,
                'features'                 => json_encode([
                    'core_banking',
                    'savings_accounts',
                    'loan_management',
                    'basic_reports',
                    'customer_portal',
                    'sms_notifications',
                ]),
                'is_active'                => true,
            ],
            [
                'id'                       => Str::uuid(),
                'name'                     => 'Growth',
                'slug'                     => 'growth',
                'price_monthly'            => 45000.00,
                'price_yearly'             => 75000.00,
                'max_customers'            => 10000,
                'max_staff_users'          => 25,
                'max_branches'             => 5,
                'max_transactions_monthly' => 100000,
                'features'                 => json_encode([
                    'core_banking',
                    'savings_accounts',
                    'loan_management',
                    'fixed_deposits',
                    'standing_orders',
                    'advanced_reports',
                    'customer_portal',
                    'sms_notifications',
                    'email_notifications',
                    'kpi_tracking',
                    'hr_management',
                    'nip_transfers',
                    'agent_banking',
                    'ussd',
                ]),
                'is_active'                => true,
            ],
            [
                'id'                       => Str::uuid(),
                'name'                     => 'Enterprise',
                'slug'                     => 'enterprise',
                'price_monthly'            => 0.00,
                'price_yearly'             => 0.00,
                'max_customers'            => null,
                'max_staff_users'          => null,
                'max_branches'             => null,
                'max_transactions_monthly' => null,
                'features'                 => json_encode([
                    'core_banking',
                    'savings_accounts',
                    'loan_management',
                    'fixed_deposits',
                    'standing_orders',
                    'overdraft',
                    'cheques',
                    'advanced_reports',
                    'regulatory_reports',
                    'customer_portal',
                    'sms_notifications',
                    'email_notifications',
                    'kpi_tracking',
                    'hr_management',
                    'payroll',
                    'nip_transfers',
                    'agent_banking',
                    'ussd',
                    'ecl_ifrs9',
                    'credit_bureau',
                    'fixed_assets',
                    'custom_branding',
                    'api_access',
                    'dedicated_support',
                    'sla_guarantee',
                ]),
                'is_active'                => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded: Starter, Growth, Enterprise');
    }
}
