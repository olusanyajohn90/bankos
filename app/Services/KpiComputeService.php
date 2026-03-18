<?php

namespace App\Services;

use App\Models\KpiActual;
use App\Models\KpiDefinition;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Computes KPI actuals from live transaction / loan / account data.
 *
 * Period formats:
 *   monthly   → '2025-03'
 *   quarterly → '2025-Q1'
 *   yearly    → '2025'
 */
class KpiComputeService
{
    // ── Public orchestration ──────────────────────────────────────────────────

    /**
     * Compute all auto KPIs for every individual in the tenant for a period.
     * Returns a summary array ['computed' => int, 'errors' => int].
     */
    public function computeAll(string $tenantId, string $periodType, string $periodValue): array
    {
        $computed = 0;
        $errors   = 0;

        [$from, $to] = $this->periodToDateRange($periodType, $periodValue);

        $kpis = KpiDefinition::where('computation_type', 'auto')
            ->where('is_active', true)
            ->get();

        // ── Individual level ─────────────────────────────────────────────────
        $staffIds = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->pluck('user_id');

        foreach ($staffIds as $userId) {
            foreach ($kpis as $kpi) {
                try {
                    $value = $this->computeForSubject(
                        $kpi->auto_compute_method,
                        $tenantId, 'individual', $userId, $from, $to
                    );
                    if ($value !== null) {
                        $this->upsertActual($tenantId, $kpi->id, 'individual', $userId, 'user', $periodType, $periodValue, $value);
                        $computed++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    \Log::warning("KPI compute error [{$kpi->code}] user:{$userId}: " . $e->getMessage());
                }
            }
        }

        // ── Team level ───────────────────────────────────────────────────────
        $teams = Team::where('tenant_id', $tenantId)->where('status', 'active')->get();
        foreach ($teams as $team) {
            $memberIds = $this->getTeamMemberIds($team->id);
            foreach ($kpis as $kpi) {
                try {
                    $value = $this->computeForGroup($kpi->auto_compute_method, $tenantId, $memberIds, $from, $to, $kpi->direction);
                    if ($value !== null) {
                        $this->upsertActual($tenantId, $kpi->id, 'team', $team->id, 'team', $periodType, $periodValue, $value);
                        $computed++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        }

        // ── Branch level ─────────────────────────────────────────────────────
        $branches = Branch::where('tenant_id', $tenantId)->get();
        foreach ($branches as $branch) {
            $staffIdsByBranch = $this->getBranchStaffIds($tenantId, $branch->id);
            foreach ($kpis as $kpi) {
                try {
                    $value = $this->computeForGroup($kpi->auto_compute_method, $tenantId, $staffIdsByBranch, $from, $to, $kpi->direction);
                    if ($value !== null) {
                        $this->upsertActual($tenantId, $kpi->id, 'branch', $branch->id, 'branch', $periodType, $periodValue, $value);
                        $computed++;
                    }
                } catch (\Throwable $e) { $errors++; }
            }
        }

        return ['computed' => $computed, 'errors' => $errors];
    }

    // ── Individual compute methods ────────────────────────────────────────────

    public function loansCount(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        return (float) DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereIn('status', ['active', 'overdue', 'closed', 'written_off'])
            ->whereBetween('disbursed_at', [$from, $to])
            ->count();
    }

    public function loansValue(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        return (float) DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereIn('status', ['active', 'overdue', 'closed', 'written_off'])
            ->whereBetween('disbursed_at', [$from, $to])
            ->sum('principal_amount');
    }

    public function accountsOpened(string $tenantId, string $userId, Carbon $from, Carbon $to): float
    {
        return (float) DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('opened_by', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    public function depositMobilization(string $tenantId, string $userId, Carbon $from, Carbon $to): float
    {
        return (float) DB::table('accounts')
            ->where('tenant_id', $tenantId)
            ->where('opened_by', $userId)
            ->whereIn('type', ['savings', 'current'])
            ->where('status', 'active')
            ->sum('available_balance');
    }

    public function par30(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        return $this->parByDpd($tenantId, $officerId, 30);
    }

    public function par60(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        return $this->parByDpd($tenantId, $officerId, 60);
    }

    public function par90(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        return $this->parByDpd($tenantId, $officerId, 90);
    }

    public function collectionEfficiency(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        // Repayments received in period on officer's active/overdue loans
        $repaid = (float) DB::table('transactions as t')
            ->join('loans as l', 'l.account_id', '=', 't.account_id')
            ->where('l.tenant_id', $tenantId)
            ->where('l.officer_id', $officerId)
            ->whereIn('l.status', ['active', 'overdue'])
            ->where('t.type', 'loan_repayment')
            ->whereBetween('t.created_at', [$from, $to])
            ->sum('t.amount');

        // Simplified: monthly installment × number of active loans (approximate due)
        $totalDue = (float) DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereIn('status', ['active', 'overdue'])
            ->where('disbursed_at', '<=', $to)
            ->selectRaw('SUM(principal_amount * (1 + interest_rate / 100) / (tenure_days / 30)) as due')
            ->value('due');

        if ($totalDue <= 0) return 100.0;

        return (float) round(min(($repaid / $totalDue) * 100, 100), 2);
    }

    public function disbursementTat(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        $avg = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereNotNull('disbursed_at')
            ->whereBetween('disbursed_at', [$from, $to])
            ->selectRaw(
                DB::getDriverName() === 'pgsql'
                    ? "AVG(EXTRACT(EPOCH FROM (disbursed_at::timestamp - created_at::timestamp)) / 86400) as avg_tat"
                    : "AVG(TIMESTAMPDIFF(DAY, created_at, disbursed_at)) as avg_tat"
            )
            ->value('avg_tat');

        return $avg ? round((float) $avg, 1) : 0.0;
    }

    public function crossSellRatio(string $tenantId, string $officerId, Carbon $from, Carbon $to): float
    {
        $total = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereIn('status', ['active', 'overdue'])
            ->distinct()
            ->count('customer_id');

        if ($total === 0) return 0.0;

        $crossSell = DB::table('loans as l')
            ->join('accounts as a', function ($j) use ($tenantId) {
                $j->on('a.customer_id', '=', 'l.customer_id')
                  ->where('a.tenant_id', $tenantId)
                  ->where('a.type', 'savings')
                  ->where('a.status', 'active');
            })
            ->where('l.tenant_id', $tenantId)
            ->where('l.officer_id', $officerId)
            ->whereIn('l.status', ['active', 'overdue'])
            ->distinct()
            ->count('l.customer_id');

        return (float) round(($crossSell / $total) * 100, 2);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Convert period string to [Carbon $from, Carbon $to].
     */
    public function periodToDateRange(string $periodType, string $periodValue): array
    {
        switch ($periodType) {
            case 'monthly':
                $from = Carbon::createFromFormat('Y-m', $periodValue)->startOfMonth();
                $to   = $from->copy()->endOfMonth();
                break;
            case 'quarterly':
                [$year, $q] = explode('-Q', $periodValue);
                $startMonth = (((int)$q - 1) * 3) + 1;
                $from = Carbon::create((int)$year, $startMonth, 1)->startOfDay();
                $to   = $from->copy()->addMonths(3)->subDay()->endOfDay();
                break;
            case 'yearly':
                $from = Carbon::createFromFormat('Y', $periodValue)->startOfYear();
                $to   = $from->copy()->endOfYear();
                break;
            default:
                $from = now()->startOfMonth();
                $to   = now()->endOfMonth();
        }
        return [$from, $to];
    }

    public function currentPeriodValue(string $periodType): string
    {
        return match($periodType) {
            'monthly'   => now()->format('Y-m'),
            'quarterly' => now()->format('Y') . '-Q' . now()->quarter,
            'yearly'    => now()->format('Y'),
            default     => now()->format('Y-m'),
        };
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function parByDpd(string $tenantId, string $officerId, int $minDpd): float
    {
        $loans = DB::table('loans')
            ->where('tenant_id', $tenantId)
            ->where('officer_id', $officerId)
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('disbursed_at')
            ->select('outstanding_balance', 'disbursed_at', 'principal_amount',
                     'interest_rate', 'tenure_days', 'outstanding_balance')
            ->get();

        $totalBalance    = 0.0;
        $atRiskBalance   = 0.0;

        foreach ($loans as $loan) {
            $bal = (float) $loan->outstanding_balance;
            $totalBalance += $bal;

            // Approximate DPD: months since disbursement * 30 - expected repayment days
            $monthsElapsed = Carbon::parse($loan->disbursed_at)->diffInMonths(now());
            $totalRepayable = (float) $loan->principal_amount * (1 + (float) $loan->interest_rate / 100);
            $monthlyInstall = $loan->tenure_days > 0 ? $totalRepayable / ($loan->tenure_days / 30) : 0;
            $expectedRepaid = $monthlyInstall * $monthsElapsed;
            $actualRepaid   = $totalRepayable - $bal;
            $overdue        = max(0, $expectedRepaid - $actualRepaid);
            $dpd            = $monthlyInstall > 0 ? (int) round(($overdue / $monthlyInstall) * 30) : 0;

            if ($dpd >= $minDpd) {
                $atRiskBalance += $bal;
            }
        }

        if ($totalBalance <= 0) return 0.0;

        return (float) round(($atRiskBalance / $totalBalance) * 100, 2);
    }

    private function computeForSubject(
        ?string $method,
        string $tenantId,
        string $subjectType,
        string $subjectRefId,
        Carbon $from,
        Carbon $to
    ): ?float {
        if (!$method) return null;

        return match($method) {
            'loans_count'           => $this->loansCount($tenantId, $subjectRefId, $from, $to),
            'loans_value'           => $this->loansValue($tenantId, $subjectRefId, $from, $to),
            'accounts_opened'       => $this->accountsOpened($tenantId, $subjectRefId, $from, $to),
            'deposit_mobilization'  => $this->depositMobilization($tenantId, $subjectRefId, $from, $to),
            'par30'                 => $this->par30($tenantId, $subjectRefId, $from, $to),
            'par60'                 => $this->par60($tenantId, $subjectRefId, $from, $to),
            'par90'                 => $this->par90($tenantId, $subjectRefId, $from, $to),
            'collection_efficiency' => $this->collectionEfficiency($tenantId, $subjectRefId, $from, $to),
            'disbursement_tat'      => $this->disbursementTat($tenantId, $subjectRefId, $from, $to),
            'cross_sell_ratio'      => $this->crossSellRatio($tenantId, $subjectRefId, $from, $to),
            default                 => null,
        };
    }

    private function computeForGroup(
        ?string $method,
        string $tenantId,
        array $memberIds,
        Carbon $from,
        Carbon $to,
        string $direction
    ): ?float {
        if (!$method || empty($memberIds)) return null;

        $values = [];
        foreach ($memberIds as $uid) {
            $v = $this->computeForSubject($method, $tenantId, 'individual', $uid, $from, $to);
            if ($v !== null) $values[] = $v;
        }
        if (empty($values)) return null;

        // For rates (PAR, TAT, efficiency): average; for counts/values: sum
        $sumTypes = ['loans_count', 'loans_value', 'accounts_opened', 'deposit_mobilization'];
        if (in_array($method, $sumTypes)) {
            return array_sum($values);
        }
        return round(array_sum($values) / count($values), 2);
    }

    private function getTeamMemberIds(string $teamId): array
    {
        return StaffProfile::where('team_id', $teamId)
            ->where('status', 'active')
            ->pluck('user_id')
            ->toArray();
    }

    private function getBranchStaffIds(string $tenantId, string $branchId): array
    {
        return StaffProfile::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->pluck('user_id')
            ->toArray();
    }

    private function upsertActual(
        string $tenantId,
        string $kpiId,
        string $subjectType,
        ?string $subjectRefId,
        string $subjectRefType,
        string $periodType,
        string $periodValue,
        float $value
    ): void {
        KpiActual::updateOrCreate(
            [
                'tenant_id'       => $tenantId,
                'kpi_id'          => $kpiId,
                'subject_type'    => $subjectType,
                'subject_ref_id'  => $subjectRefId,
                'period_type'     => $periodType,
                'period_value'    => $periodValue,
            ],
            [
                'subject_ref_type'   => $subjectRefType,
                'value'              => $value,
                'source'             => 'auto',
                'computed_at'        => now(),
            ]
        );
    }
}
