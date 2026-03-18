<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
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

    public function view(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id;
    }

    /** Only tenant admins may delete customers. */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id
            && $user->hasRole('tenant_admin');
    }

    /** Specific export permission — useful for NDPR / data privacy compliance. */
    public function exportData(User $user, Customer $customer): bool
    {
        return $user->tenant_id === $customer->tenant_id
            && $user->hasPermissionTo('export-customer-data');
    }
}
