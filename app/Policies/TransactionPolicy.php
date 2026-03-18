<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
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

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->tenant_id === $transaction->tenant_id;
    }

    /** Compliance officers / admins with explicit reversal rights. */
    public function reverse(User $user, Transaction $transaction): bool
    {
        return $user->tenant_id === $transaction->tenant_id
            && $user->hasPermissionTo('reverse-transactions');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-transactions')
            || $user->hasRole(['teller', 'branch_manager', 'tenant_admin']);
    }
}
