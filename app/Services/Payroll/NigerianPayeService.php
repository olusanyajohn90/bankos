<?php
namespace App\Services\Payroll;

class NigerianPayeService
{
    // Finance Act 2020 / 2023 amendment PAYE bands (annual)
    private const TAX_BANDS = [
        [300_000,   0.07],
        [300_000,   0.11],
        [500_000,   0.15],
        [500_000,   0.19],
        [1_600_000, 0.21],
        [PHP_INT_MAX, 0.24],
    ];

    /**
     * Compute annual PAYE for a given annual gross income.
     * Deductions (pension employee, NHF) are subtracted before applying CRA.
     */
    public function computeAnnualPaye(
        float $annualGross,
        float $annualPensionEmployee = 0,
        float $annualNhf = 0
    ): float {
        if ($annualGross <= 0) return 0.0;

        // Consolidated Relief Allowance (CRA): higher of ₦200,000 or 1% of gross, PLUS 20% of gross
        $cra = max(200_000, 0.01 * $annualGross) + 0.20 * $annualGross;

        // Taxable income after statutory deductions and CRA
        $taxable = $annualGross - $annualPensionEmployee - $annualNhf - $cra;
        if ($taxable <= 0) return 0.0;

        $tax = 0.0;
        $remaining = $taxable;

        foreach (self::TAX_BANDS as [$bandSize, $rate]) {
            if ($remaining <= 0) break;
            $taxable_band = min($remaining, $bandSize);
            $tax += $taxable_band * $rate;
            $remaining -= $taxable_band;
        }

        return round(max(0, $tax), 2);
    }

    /**
     * Monthly PAYE: annualise monthly figures, compute annual tax, divide by 12.
     */
    public function computeMonthlyPaye(
        float $monthlyGross,
        float $monthlyPensionEmployee = 0,
        float $monthlyNhf = 0
    ): float {
        $annual = $this->computeAnnualPaye(
            $monthlyGross * 12,
            $monthlyPensionEmployee * 12,
            $monthlyNhf * 12
        );
        return round($annual / 12, 2);
    }
}
