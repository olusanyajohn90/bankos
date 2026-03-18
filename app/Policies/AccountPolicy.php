<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, Account $account): bool
    {
        return $user->tenant_id === $account->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-accounts')
            || $user->hasRole(['branch_manager', 'tenant_admin']);
    }

    /** PND / account freeze — compliance or admin. */
    public function freeze(User $user, Account $account): bool
    {
        return $user->tenant_id === $account->tenant_id
            && $user->hasPermissionTo('freeze-accounts');
    }

    /** Closing an account is branch manager / admin level. */
    public function close(User $user, Account $account): bool
    {
        return $user->tenant_id === $account->tenant_id
            && $user->hasRole(['branch_manager', 'tenant_admin']);
    }
}
