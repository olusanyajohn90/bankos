<?php

namespace App\Services;

use App\Models\KpiActual;
use App\Models\KpiAlert;
use App\Models\KpiDefinition;
use App\Models\KpiNote;
use App\Models\KpiTarget;
use App\Models\StaffProfile;
use App\Models\User;
use App\Notifications\KpiAlertNotification;

class KpiAlertService
{
    /**
     * Check all targets for the period against actuals and fire alerts.
     * Returns the count of new alerts created.
     */
    public function checkAndFire(string $tenantId, string $periodType, string $periodValue): int
    {
        $count = 0;

        $targets = KpiTarget::where('tenant_id', $tenantId)
            ->where('period_type', $periodType)
            ->where('period_value', $periodValue)
            ->with('kpiDefinition')
            ->get();

        foreach ($targets as $target) {
            $actual = $target->matchingActual();
            if (!$actual) continue;

            $kpi    = $target->kpiDefinition;
            $pct    = $this->computeAchievement($actual, $target, $kpi);
            $sev    = $this->determineSeverity($pct, $target->alert_threshold_pct);

            // Only alert on yellow/red; green = performing well
            if ($sev === 'green') continue;

            $recipients = $this->resolveRecipients($target);

            foreach ($recipients as $user) {
                $alert = $this->fireAlert($target, $actual, $pct, $sev, $user->id);
                if ($alert) {
                    $user->notify(new KpiAlertNotification($alert, $kpi));
                    $count++;
                }
            }
        }

        return $count;
    }

    public function computeAchievement(KpiActual $actual, KpiTarget $target, ?KpiDefinition $kpi = null): float
    {
        if ($target->target_value == 0) return 0.0;

        $kpiDef = $kpi ?? $target->kpiDefinition;

        if ($kpiDef && $kpiDef->direction === 'lower_better' && $actual->value > 0) {
            // Lower is better — invert: target/actual × 100
            return round(($target->target_value / $actual->value) * 100, 2);
        }

        return round(($actual->value / $target->target_value) * 100, 2);
    }

    public function determineSeverity(float $pct, int $thresholdPct = 70): string
    {
        if ($pct >= 90) return 'green';
        if ($pct >= $thresholdPct) return 'yellow';
        return 'red';
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function fireAlert(KpiTarget $target, KpiActual $actual, float $pct, string $severity, string $recipientId): ?KpiAlert
    {
        // Update existing unread alert for same target+period+recipient rather than duplicate
        $existing = KpiAlert::where('kpi_target_id', $target->id)
            ->where('period_value', $target->period_value)
            ->where('recipient_id', $recipientId)
            ->whereIn('status', ['unread', 'read'])
            ->first();

        if ($existing) {
            $existing->update([
                'severity'        => $severity,
                'achievement_pct' => $pct,
                'actual_value'    => $actual->value,
                'status'          => 'unread',
                'read_at'         => null,
            ]);
            return $existing;
        }

        return KpiAlert::create([
            'tenant_id'       => $target->tenant_id,
            'kpi_target_id'   => $target->id,
            'kpi_actual_id'   => $actual->id,
            'recipient_id'    => $recipientId,
            'severity'        => $severity,
            'achievement_pct' => $pct,
            'target_value'    => $target->target_value,
            'actual_value'    => $actual->value,
            'period_value'    => $target->period_value,
            'status'          => 'unread',
        ]);
    }

    private function resolveRecipients(KpiTarget $target): array
    {
        $users = [];

        if ($target->target_type === 'individual' && $target->target_ref_id) {
            // target_ref_id is StaffProfile UUID (not user ID)
            $profile = StaffProfile::find($target->target_ref_id);
            if ($profile && $profile->user_id) {
                $user = User::find($profile->user_id);
                if ($user) $users[] = $user;
            }

            // Also notify the direct manager
            if ($profile && $profile->manager_id) {
                $manager = User::find($profile->manager_id);
                if ($manager) $users[] = $manager;
            }
        } elseif ($target->target_type === 'team' && $target->target_ref_id) {
            // Notify team lead
            $team = \App\Models\Team::find($target->target_ref_id);
            if ($team && $team->team_lead_id) {
                $lead = User::find($team->team_lead_id);
                if ($lead) $users[] = $lead;
            }
        } elseif ($target->target_type === 'branch' && $target->target_ref_id) {
            $branch = \App\Models\Branch::find($target->target_ref_id);
            if ($branch && $branch->manager_id) {
                $manager = User::find($branch->manager_id);
                if ($manager) $users[] = $manager;
            }
        }

        // Deduplicate by id
        $seen = [];
        return array_filter($users, function($u) use (&$seen) {
            if (isset($seen[$u->id])) return false;
            $seen[$u->id] = true;
            return true;
        });
    }
}
