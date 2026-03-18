<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\KpiTarget;
use App\Models\StaffProfile;
use App\Models\Team;

class KpiCascadeService
{
    /**
     * Cascade a tenant-level or branch-level KPI target down the hierarchy.
     * Propagation: tenant → branch → team → individual
     */
    public function cascadeFromTenant(string $tenantId, string $kpiId, string $periodType, string $periodValue): int
    {
        $tenantTarget = KpiTarget::where('tenant_id', $tenantId)
            ->where('kpi_id', $kpiId)
            ->where('target_type', 'tenant')
            ->where('period_type', $periodType)
            ->where('period_value', $periodValue)
            ->first();

        if (!$tenantTarget) return 0;

        $created = 0;
        $branches = Branch::where('tenant_id', $tenantId)->get();

        foreach ($branches as $branch) {
            $created += $this->cascadeToBranch($tenantTarget, $branch, $periodType, $periodValue);
        }

        return $created;
    }

    public function cascadeToBranch(KpiTarget $parentTarget, Branch $branch, string $periodType, string $periodValue): int
    {
        $branchTarget = KpiTarget::updateOrCreate(
            [
                'tenant_id'      => $branch->tenant_id,
                'kpi_id'         => $parentTarget->kpi_id,
                'target_type'    => 'branch',
                'target_ref_id'  => $branch->id,
                'period_type'    => $periodType,
                'period_value'   => $periodValue,
            ],
            [
                'target_value'       => $parentTarget->target_value,   // inherit or scale
                'alert_threshold_pct'=> $parentTarget->alert_threshold_pct ?? 70,
                'parent_target_id'   => $parentTarget->id,
                'weight_pct'         => 100,
                'is_cascaded'        => true,
            ]
        );

        $created = 1;
        $teams = Team::where('branch_id', $branch->id)->where('status', 'active')->get();

        foreach ($teams as $team) {
            $created += $this->cascadeToTeam($branchTarget, $team, $periodType, $periodValue);
        }

        return $created;
    }

    public function cascadeToTeam(KpiTarget $parentTarget, Team $team, string $periodType, string $periodValue): int
    {
        $members = StaffProfile::where('team_id', $team->id)->where('status', 'active')->get();
        if ($members->isEmpty()) return 0;

        $teamTarget = KpiTarget::updateOrCreate(
            [
                'tenant_id'      => $parentTarget->tenant_id,
                'kpi_id'         => $parentTarget->kpi_id,
                'target_type'    => 'team',
                'target_ref_id'  => $team->id,
                'period_type'    => $periodType,
                'period_value'   => $periodValue,
            ],
            [
                'target_value'       => $parentTarget->target_value,
                'alert_threshold_pct'=> $parentTarget->alert_threshold_pct ?? 70,
                'parent_target_id'   => $parentTarget->id,
                'weight_pct'         => 100,
                'is_cascaded'        => true,
            ]
        );

        $created = 1;
        foreach ($members as $member) {
            $created += $this->cascadeToIndividual($teamTarget, $member, $periodType, $periodValue);
        }

        return $created;
    }

    public function cascadeToIndividual(KpiTarget $parentTarget, StaffProfile $profile, string $periodType, string $periodValue): int
    {
        KpiTarget::updateOrCreate(
            [
                'tenant_id'      => $parentTarget->tenant_id,
                'kpi_id'         => $parentTarget->kpi_id,
                'target_type'    => 'individual',
                'target_ref_id'  => $profile->id,
                'period_type'    => $periodType,
                'period_value'   => $periodValue,
            ],
            [
                'target_value'       => $parentTarget->target_value,
                'alert_threshold_pct'=> $parentTarget->alert_threshold_pct ?? 70,
                'parent_target_id'   => $parentTarget->id,
                'weight_pct'         => 100,
                'is_cascaded'        => true,
            ]
        );

        return 1;
    }

    /**
     * Push all KPI targets for a period from HQ down to all levels.
     */
    public function cascadeAll(string $tenantId, string $periodType, string $periodValue): array
    {
        $tenantTargets = KpiTarget::where('tenant_id', $tenantId)
            ->where('target_type', 'tenant')
            ->where('period_type', $periodType)
            ->where('period_value', $periodValue)
            ->get();

        $totalCreated = 0;
        foreach ($tenantTargets as $target) {
            $totalCreated += $this->cascadeFromTenant($tenantId, $target->kpi_id, $periodType, $periodValue);
        }

        return ['cascaded' => $totalCreated, 'source_targets' => $tenantTargets->count()];
    }
}
