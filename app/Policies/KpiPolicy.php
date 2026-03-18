<?php

namespace App\Policies;

use App\Models\StaffProfile;
use App\Models\User;

class KpiPolicy
{
    /**
     * Can the viewer see the KPI dashboard of the given StaffProfile?
     * Allowed: self, direct manager, any ancestor in the manager chain, or tenant admin.
     */
    public function viewStaffKpi(User $viewer, StaffProfile $subject): bool
    {
        // Viewing own KPIs is always allowed
        if ($viewer->id === $subject->user_id) {
            return true;
        }

        // Tenant admins / HR managers can always view
        if ($viewer->hasAnyRole(['super_admin', 'tenant_admin', 'hr_manager', 'ceo', 'md'])) {
            return true;
        }

        // Walk up the manager chain from subject to root
        return $this->isManagerOf($viewer->id, $subject);
    }

    /**
     * Recursively walk up manager_id chain to check if $managerId is an ancestor.
     * Stops at 10 levels to prevent infinite loops from bad data.
     */
    private function isManagerOf(int $managerId, StaffProfile $profile, int $depth = 0): bool
    {
        if ($depth >= 10) return false;

        // Get the profile's direct manager_id (FK on staff_profiles = users.id bigint)
        if (!$profile->manager_id) return false;

        if ($profile->manager_id === $managerId) return true;

        // Load the manager's StaffProfile and recurse
        $managerProfile = StaffProfile::where('user_id', $profile->manager_id)
            ->where('tenant_id', $profile->tenant_id)
            ->first();

        if (!$managerProfile) return false;

        return $this->isManagerOf($managerId, $managerProfile, $depth + 1);
    }
}
