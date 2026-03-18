<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Super admins bypass all policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    /** Any authenticated user belonging to the same tenant may list loans. */
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /** User must belong to the same tenant as the loan. */
    public function view(User $user, Loan $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id;
    }

    /** Loan officers and anyone with create-loans permission. */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-loans')
            || $user->hasRole(['loan_officer', 'branch_manager', 'tenant_admin']);
    }

    /** Only users with explicit approve-loans permission. */
    public function approve(User $user, Loan $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id
            && $user->hasPermissionTo('approve-loans');
    }

    /** Only users with disburse-loans permission. */
    public function disburse(User $user, Loan $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id
            && $user->hasPermissionTo('disburse-loans');
    }

    /** Same permission as approve (compliance / credit officer). */
    public function reject(User $user, Loan $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id
            && $user->hasPermissionTo('approve-loans');
    }
}
