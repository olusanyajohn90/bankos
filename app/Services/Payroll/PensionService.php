<?php
namespace App\Services\Payroll;

use App\Models\StaffPayConfig;

class PensionService
{
    public const EMPLOYEE_RATE = 0.08;  // PRA 2014: 8% of monthly emolument
    public const EMPLOYER_RATE = 0.10;  // 10% of monthly emolument

    /**
     * Pensionable base = Basic + Housing + Transport (PRA 2014, S.4)
     */
    public function pensionableBase(StaffPayConfig $config): float
    {
        return $config->basic_salary + $config->housing_allowance + $config->transport_allowance;
    }

    public function employeeContribution(StaffPayConfig $config): float
    {
        return round($this->pensionableBase($config) * self::EMPLOYEE_RATE, 2);
    }

    public function employerContribution(StaffPayConfig $config): float
    {
        return round($this->pensionableBase($config) * self::EMPLOYER_RATE, 2);
    }

    /**
     * NHF: 2.5% of monthly basic salary (Federal Mortgage Bank Act)
     * Only deducted if staff has NHF number registered.
     */
    public function nhfContribution(StaffPayConfig $config): float
    {
        if (!$config->nhf_number) return 0.0;
        return round($config->basic_salary * 0.025, 2);
    }

    /**
     * NSITF: 1% of gross salary (employer only, not deducted from staff pay)
     */
    public function nsitfContribution(float $grossSalary): float
    {
        return round($grossSalary * 0.01, 2);
    }
}
