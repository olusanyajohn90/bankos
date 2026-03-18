<?php
namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BranchBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('short_name', 'demo')->firstOrFail();
        $tid = $tenant->id;

        $ho = Branch::where('tenant_id', $tid)->where('branch_code', 'HO001')->first();
        if (!$ho) return;

        // Assign all unattached records to Head Office
        Customer::where('tenant_id', $tid)->whereNull('branch_id')->update(['branch_id' => $ho->id]);
        Account::where('tenant_id', $tid)->whereNull('branch_id')->update(['branch_id' => $ho->id]);
        Loan::where('tenant_id', $tid)->whereNull('branch_id')->update(['branch_id' => $ho->id]);

        // Re-assign BranchSeeder customers by phone to their correct branches
        $phoneMap = [
            // Kano
            '+23481-100-2001' => 'KAN001', '+23481-100-2002' => 'KAN001',
            '+23481-100-2003' => 'KAN001', '+23481-100-2004' => 'KAN001',
            // Port Harcourt
            '+23481-100-3001' => 'PHC001', '+23481-100-3002' => 'PHC001',
            '+23481-100-3003' => 'PHC001', '+23481-100-3004' => 'PHC001',
            // Enugu
            '+23481-100-4001' => 'ENG001', '+23481-100-4002' => 'ENG001',
            '+23481-100-4003' => 'ENG001', '+23481-100-4004' => 'ENG001',
            // Ibadan
            '+23481-100-5001' => 'IBD001', '+23481-100-5002' => 'IBD001',
            '+23481-100-5003' => 'IBD001', '+23481-100-5004' => 'IBD001',
        ];

        $branches = Branch::where('tenant_id', $tid)->get()->keyBy('branch_code');

        foreach ($phoneMap as $phone => $code) {
            $branch = $branches[$code] ?? null;
            if (!$branch) continue;
            $customer = Customer::where('phone', $phone)->first();
            if (!$customer) continue;
            $customer->update(['branch_id' => $branch->id]);
            Account::where('customer_id', $customer->id)->update(['branch_id' => $branch->id]);
            Loan::where('customer_id', $customer->id)->update(['branch_id' => $branch->id]);
        }

        // Distribute Ikeja/Abuja branches: assign demo customers by random sampling
        // Ikeja gets 4 customers, Abuja gets 4 customers from the head-office pool
        $ikeja = $branches['IKJ001'] ?? null;
        $abuja = $branches['ABJ001'] ?? null;

        if ($ikeja) {
            Customer::where('tenant_id', $tid)->where('branch_id', $ho->id)
                ->inRandomOrder()->limit(4)->get()
                ->each(function ($c) use ($ikeja) {
                    $c->update(['branch_id' => $ikeja->id]);
                    Account::where('customer_id', $c->id)->update(['branch_id' => $ikeja->id]);
                    Loan::where('customer_id', $c->id)->update(['branch_id' => $ikeja->id]);
                });
        }

        if ($abuja) {
            Customer::where('tenant_id', $tid)->where('branch_id', $ho->id)
                ->inRandomOrder()->limit(4)->get()
                ->each(function ($c) use ($abuja) {
                    $c->update(['branch_id' => $abuja->id]);
                    Account::where('customer_id', $c->id)->update(['branch_id' => $abuja->id]);
                    Loan::where('customer_id', $c->id)->update(['branch_id' => $abuja->id]);
                });
        }
    }
}
