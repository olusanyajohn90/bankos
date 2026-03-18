<?php
namespace App\Services\Payroll;

use App\Models\PayComponent;
use App\Models\PayrollItem;
use App\Models\PayrollItemLine;
use App\Models\PayrollRun;
use App\Models\StaffPayConfig;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PayrollRunService
{
    public function __construct(
        protected NigerianPayeService $paye,
        protected PensionService $pension
    ) {}

    public function initRun(string $tenantId, int $month, int $year, User $runBy): PayrollRun
    {
        // Check for non-cancelled run for this period
        $existing = PayrollRun::where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereNot('status', 'cancelled')
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException(
                "A payroll run for {$month}/{$year} already exists with status: {$existing->status}."
            );
        }

        return PayrollRun::create([
            'tenant_id'    => $tenantId,
            'period_month' => $month,
            'period_year'  => $year,
            'status'       => 'draft',
            'run_by'       => $runBy->id,
        ]);
    }

    public function processRun(PayrollRun $run): void
    {
        DB::transaction(function () use ($run) {
            // Delete any previously computed items (re-processing)
            $run->items()->each(fn($item) => $item->lines()->delete());
            $run->items()->delete();

            $tenantId = $run->tenant_id;

            // Load all active staff pay configs for this tenant
            $configs = StaffPayConfig::where('tenant_id', $tenantId)
                ->whereHas('staffProfile', fn($q) => $q->where('status', 'active'))
                ->with(['staffProfile.bankDetails' => fn($q) => $q->where('is_primary', true)])
                ->get();

            // Load custom (non-formula) pay components for tenant
            $components = PayComponent::where('tenant_id', $tenantId)
                ->active()
                ->where('computation_type', '!=', 'formula')
                ->get();

            $totals = [
                'gross' => 0, 'deductions' => 0, 'net' => 0,
                'paye' => 0, 'pension_employee' => 0, 'pension_employer' => 0,
                'nhf' => 0, 'nsitf' => 0, 'staff_count' => 0,
            ];

            foreach ($configs as $config) {
                $item = $this->computeStaffItem($run, $config, $components);
                $totals['gross']            += $item->gross_salary;
                $totals['deductions']       += $item->total_deductions;
                $totals['net']              += $item->net_salary;
                $totals['paye']             += $item->paye;
                $totals['pension_employee'] += $item->employee_pension;
                $totals['pension_employer'] += $item->employer_pension;
                $totals['nhf']              += $item->nhf;
                $totals['nsitf']            += $item->nsitf;
                $totals['staff_count']++;
            }

            $run->update([
                'status'                  => 'processing',
                'total_gross'             => $totals['gross'],
                'total_deductions'        => $totals['deductions'],
                'total_net'               => $totals['net'],
                'total_paye'              => $totals['paye'],
                'total_pension_employee'  => $totals['pension_employee'],
                'total_pension_employer'  => $totals['pension_employer'],
                'total_nhf'               => $totals['nhf'],
                'total_nsitf'             => $totals['nsitf'],
                'staff_count'             => $totals['staff_count'],
            ]);
        });
    }

    private function computeStaffItem(PayrollRun $run, StaffPayConfig $config, $components): PayrollItem
    {
        $lines = [];

        // --- EARNINGS ---
        $earnings = [
            'Basic Salary'        => $config->basic_salary,
            'Housing Allowance'   => $config->housing_allowance,
            'Transport Allowance' => $config->transport_allowance,
            'Meal Allowance'      => $config->meal_allowance,
        ];

        // Other allowances from JSON
        if (is_array($config->other_allowances)) {
            foreach ($config->other_allowances as $otherItem) {
                $earnings[$otherItem['name'] ?? 'Allowance'] = (float)($otherItem['amount'] ?? 0);
            }
        }

        // Custom earning components
        $gross = array_sum($earnings);
        foreach ($components->where('type', 'earning') as $comp) {
            $amount = $this->computeComponentAmount($comp, $config->basic_salary, $gross);
            if ($amount > 0) {
                $earnings[$comp->name] = $amount;
                $gross += $amount;
            }
        }

        foreach ($earnings as $name => $amount) {
            if ($amount > 0) {
                $lines[] = ['name' => $name, 'type' => 'earning', 'statutory' => false, 'amount' => $amount];
            }
        }

        // --- STATUTORY DEDUCTIONS ---
        $pensionEmployee = $this->pension->employeeContribution($config);
        $pensionEmployer = $this->pension->employerContribution($config);
        $nhf             = $this->pension->nhfContribution($config);
        $nsitf           = $this->pension->nsitfContribution($gross);
        $paye            = $this->paye->computeMonthlyPaye($gross, $pensionEmployee, $nhf);

        $taxableIncome = max(0, $gross - $pensionEmployee - $nhf);

        $lines[] = ['name' => 'PAYE (Income Tax)',    'type' => 'deduction', 'statutory' => true, 'amount' => $paye];
        $lines[] = ['name' => 'Employee Pension (8%)', 'type' => 'deduction', 'statutory' => true, 'amount' => $pensionEmployee];
        if ($nhf > 0) {
            $lines[] = ['name' => 'NHF (2.5%)', 'type' => 'deduction', 'statutory' => true, 'amount' => $nhf];
        }

        // Custom deduction components
        $customDeductions = 0;
        foreach ($components->where('type', 'deduction')->where('is_statutory', false) as $comp) {
            $amount = $this->computeComponentAmount($comp, $config->basic_salary, $gross);
            if ($amount > 0) {
                $lines[] = ['name' => $comp->name, 'type' => 'deduction', 'statutory' => false, 'amount' => $amount];
                $customDeductions += $amount;
            }
        }

        $totalDeductions = $paye + $pensionEmployee + $nhf + $customDeductions;
        $netSalary       = max(0, $gross - $totalDeductions);

        $primaryBank = $config->staffProfile->bankDetails->first();

        $item = PayrollItem::create([
            'payroll_run_id'   => $run->id,
            'staff_profile_id' => $config->staff_profile_id,
            'gross_salary'     => round($gross, 2),
            'taxable_income'   => round($taxableIncome, 2),
            'total_deductions' => round($totalDeductions, 2),
            'paye'             => $paye,
            'employee_pension' => $pensionEmployee,
            'employer_pension' => $pensionEmployer,
            'nhf'              => $nhf,
            'nsitf'            => $nsitf,
            'net_salary'       => round($netSalary, 2),
            'bank_detail_id'   => $primaryBank?->id,
            'payment_status'   => 'pending',
        ]);

        foreach ($lines as $line) {
            PayrollItemLine::create([
                'payroll_item_id'  => $item->id,
                'component_name'   => $line['name'],
                'component_type'   => $line['type'],
                'is_statutory'     => $line['statutory'],
                'amount'           => $line['amount'],
            ]);
        }

        // Employer NSITF line (info only, not deducted from staff)
        PayrollItemLine::create([
            'payroll_item_id' => $item->id,
            'component_name'  => 'Employer NSITF (1%)',
            'component_type'  => 'deduction',
            'is_statutory'    => true,
            'amount'          => $nsitf,
        ]);

        return $item;
    }

    private function computeComponentAmount(PayComponent $comp, float $basic, float $gross): float
    {
        return match($comp->computation_type) {
            'fixed'                => (float)$comp->value,
            'percentage_of_basic'  => round($basic * ($comp->value / 100), 2),
            'percentage_of_gross'  => round($gross * ($comp->value / 100), 2),
            default                => 0.0,
        };
    }

    public function approveRun(PayrollRun $run, User $approver): void
    {
        if ($run->status !== 'processing') {
            throw new \InvalidArgumentException('Only runs in "processing" status can be approved.');
        }
        $run->update([
            'status'      => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function markPaid(PayrollRun $run): void
    {
        if ($run->status !== 'approved') {
            throw new \InvalidArgumentException('Only approved runs can be marked as paid.');
        }
        DB::transaction(function () use ($run) {
            $run->update(['status' => 'paid', 'paid_at' => now()]);
            $run->items()->update(['payment_status' => 'paid', 'payment_date' => now()]);
        });
    }

    public function cancelRun(PayrollRun $run): void
    {
        if (in_array($run->status, ['paid'])) {
            throw new \InvalidArgumentException('Paid runs cannot be cancelled.');
        }
        $run->update(['status' => 'cancelled']);
    }

    public function generatePayslipData(PayrollItem $item): array
    {
        $item->load(['payrollRun', 'staffProfile.user', 'staffProfile.orgDepartment', 'staffProfile.payConfig.payGrade', 'bankDetail', 'lines']);
        $earnings        = $item->lines->where('component_type', 'earning')->values();
        $deductions      = $item->lines->where('component_type', 'deduction')->where('is_statutory', true)->values();
        $otherDeductions = $item->lines->where('component_type', 'deduction')->where('is_statutory', false)->values();

        return [
            'item'             => $item,
            'run'              => $item->payrollRun,
            'staff'            => $item->staffProfile,
            'user'             => $item->staffProfile->user,
            'earnings'         => $earnings,
            'deductions'       => $deductions,
            'other_deductions' => $otherDeductions,
            'bank'             => $item->bankDetail,
        ];
    }
}
