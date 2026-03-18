<?php
namespace App\Console\Commands;
use App\Models\Tenant;
use App\Services\AccountLifecycleService;
use Illuminate\Console\Command;

class CheckAccountDormancy extends Command {
    protected $signature = 'bankos:check-dormancy {--months=6 : Inactive months threshold} {--tenant=}';
    protected $description = 'Flag accounts with no transactions as dormant';
    public function handle(AccountLifecycleService $service): int {
        $months = (int) $this->option('months');
        $tenants = $this->option('tenant')
            ? Tenant::where('id', $this->option('tenant'))->where('status','active')->get()
            : Tenant::where('status','active')->get();
        foreach ($tenants as $tenant) {
            $this->components->task($tenant->name, function () use ($service, $tenant, $months) {
                $count = $service->checkDormancy($tenant->id, $months);
                $this->line("  → {$count} accounts flagged dormant");
            });
        }
        return self::SUCCESS;
    }
}
