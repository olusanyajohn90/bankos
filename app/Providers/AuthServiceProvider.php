<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\LoanPolicy;
use App\Models\StaffProfile;
use App\Policies\KpiPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Loan::class        => LoanPolicy::class,
        Transaction::class => TransactionPolicy::class,
        Customer::class    => CustomerPolicy::class,
        Account::class      => AccountPolicy::class,
        StaffProfile::class => KpiPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
