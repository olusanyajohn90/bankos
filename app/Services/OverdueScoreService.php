<?php

namespace App\Services;

use App\Models\CollectionLog;
use App\Models\Loan;
use Carbon\Carbon;

class OverdueScoreService
{
    /**
     * Compute a composite overdue risk score (0–100) for a loan.
     *
     * Higher = higher risk / more urgency for collection action.
     */
    public function score(Loan $loan): int
    {
        $score = 0;

        // DPD component — up to 50 points
        $dpd = $this->getDpd($loan);
        if ($dpd >= 90)      $score += 50;
        elseif ($dpd >= 60)  $score += 40;
        elseif ($dpd >= 30)  $score += 25;
        elseif ($dpd >= 14)  $score += 15;
        elseif ($dpd >= 7)   $score += 8;
        elseif ($dpd > 0)    $score += 3;

        // Outstanding balance component — up to 20 points
        $balance = $loan->outstanding_balance;
        if ($balance >= 1_000_000)     $score += 20;
        elseif ($balance >= 500_000)   $score += 15;
        elseif ($balance >= 100_000)   $score += 10;
        elseif ($balance >= 50_000)    $score += 5;

        // No contact / failed collection attempts — up to 20 points
        $failedAttempts = CollectionLog::where('loan_id', $loan->id)
            ->where('outcome', 'unreachable')
            ->count();
        $score += min(20, $failedAttempts * 5);

        // Loan type risk — up to 10 points
        if ($loan->type === 'group') $score += 3;
        if ($loan->type === 'individual') $score += 5;

        return min(100, $score);
    }

    public function getDpd(Loan $loan): int
    {
        if ($loan->next_due_date === null) return 0;

        $due = Carbon::parse($loan->next_due_date);
        return max(0, $due->diffInDays(Carbon::today(), false));
    }

    public function getOverdueLoansWithScores(string $tenantId): \Illuminate\Support\Collection
    {
        return Loan::where('tenant_id', $tenantId)
            ->whereIn('status', ['overdue', 'defaulted'])
            ->with('customer')
            ->get()
            ->map(function (Loan $loan) {
                $loan->overdue_score = $this->score($loan);
                $loan->days_past_due = $this->getDpd($loan);
                return $loan;
            })
            ->sortByDesc('overdue_score');
    }
}
