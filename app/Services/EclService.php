<?php

namespace App\Services;

use App\Models\EclProvision;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EclService
{
    // IFRS 9 stage thresholds (days past due)
    private const STAGE_2_DPD = 30;
    private const STAGE_3_DPD = 90;

    // Default LGD assumptions by collateral type
    private const LGD_SECURED   = 0.25;
    private const LGD_UNSECURED = 0.45;

    // PD by stage (simplified point-in-time estimates)
    private const PD = [
        1 => 0.02,  // 2% for Stage 1
        2 => 0.15,  // 15% for Stage 2
        3 => 0.70,  // 70% for Stage 3
    ];

    public function computeForTenant(string $tenantId): array
    {
        $loans = Loan::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'overdue', 'defaulted'])
            ->with('customer')
            ->get();

        $results = [];
        $reportingDate = Carbon::today()->toDateString();

        foreach ($loans as $loan) {
            $results[] = $this->computeForLoan($loan, $reportingDate);
        }

        return $results;
    }

    public function computeForLoan(Loan $loan, string $reportingDate): EclProvision
    {
        $dpd   = $this->getDaysPastDue($loan);
        $stage = $this->getStage($dpd);

        $ead = $loan->outstanding_balance;
        $pd  = self::PD[$stage];
        $lgd = $loan->collateral_value > 0 ? self::LGD_SECURED : self::LGD_UNSECURED;
        $ecl = $pd * $lgd * $ead;

        return EclProvision::updateOrCreate(
            ['loan_id' => $loan->id, 'reporting_date' => $reportingDate],
            [
                'tenant_id'               => $loan->tenant_id,
                'customer_id'             => $loan->customer_id,
                'days_past_due'           => $dpd,
                'stage'                   => $stage,
                'outstanding_balance'     => $ead,
                'probability_of_default'  => $pd,
                'loss_given_default'      => $lgd,
                'exposure_at_default'     => $ead,
                'ecl_amount'              => round($ecl, 2),
            ]
        );
    }

    private function getDaysPastDue(Loan $loan): int
    {
        if ($loan->next_due_date === null) {
            return 0;
        }

        $due = Carbon::parse($loan->next_due_date);

        return max(0, $due->diffInDays(Carbon::today(), false));
    }

    private function getStage(int $dpd): int
    {
        if ($dpd >= self::STAGE_3_DPD) return 3;
        if ($dpd >= self::STAGE_2_DPD) return 2;
        return 1;
    }
}
