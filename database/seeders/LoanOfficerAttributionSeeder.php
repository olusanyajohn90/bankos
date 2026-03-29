<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanOfficerAttributionSeeder extends Seeder
{
    private string $tenantId = 'a14a96f2-006d-4232-973e-9683ef578737';

    public function run(): void
    {
        $this->command->info('Attributing loans and accounts to loan officers...');

        // Get all loan officers for the demo tenant
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
            $this->command->warn('No loan officers found for tenant. Skipping attribution.');
            return;
        }

        $officerCount = count($officers);
        $this->command->info("Found {$officerCount} loan officer(s).");

        // ── Loans: round-robin assignment ───────────────────────────
        $loanIds = DB::table('loans')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('officer_id')
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();

        $loanUpdated = 0;
        foreach ($loanIds as $index => $loanId) {
            $officerId = $officers[$index % $officerCount];
            DB::table('loans')
                ->where('id', $loanId)
                ->update(['officer_id' => $officerId]);
            $loanUpdated++;
        }

        // ── Accounts: round-robin assignment ────────────────────────
        $accountIds = DB::table('accounts')
            ->where('tenant_id', $this->tenantId)
            ->whereNull('opened_by')
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();

        $accountUpdated = 0;
        foreach ($accountIds as $index => $accountId) {
            $officerId = $officers[$index % $officerCount];
            DB::table('accounts')
                ->where('id', $accountId)
                ->update(['opened_by' => $officerId]);
            $accountUpdated++;
        }

        $this->command->info("Attributed {$loanUpdated} loans and {$accountUpdated} accounts to loan officers.");
    }
}
