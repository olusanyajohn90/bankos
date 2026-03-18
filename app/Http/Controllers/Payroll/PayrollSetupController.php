<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayComponent;
use App\Models\PayGrade;
use App\Models\StaffBankDetail;
use App\Models\StaffPayConfig;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class PayrollSetupController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Group pay grades by level for the matrix view
        $payGrades = PayGrade::where('tenant_id', $tenantId)
            ->ordered()
            ->get();

        $gradesByLevel = $payGrades->groupBy('level');

        $payComponents = PayComponent::where('tenant_id', $tenantId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $staffPayConfigs = StaffPayConfig::where('tenant_id', $tenantId)
            ->with(['staffProfile.user', 'payGrade'])
            ->paginate(20);

        $staff = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->sortBy(fn($s) => $s->user?->name)
            ->values();

        $bankDetails = StaffBankDetail::whereHas('staffProfile', fn($q) => $q->where('tenant_id', $tenantId))
            ->with('staffProfile.user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Nigerian banks list for the bank detail form
        $nigerianBanks = $this->nigerianBanks();

        return view('payroll.setup.index', compact(
            'payGrades', 'gradesByLevel', 'payComponents',
            'staffPayConfigs', 'staff', 'bankDetails', 'nigerianBanks'
        ));
    }

    // ── Pay Grades ────────────────────────────────────────────────────────────

    public function storePayGrade(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'code'                 => "required|string|max:20|unique:pay_grades,code,NULL,id,tenant_id,{$tenantId}",
            'name'                 => 'required|string|max:100',
            'level'                => 'required|integer|min:1|max:17',
            'grade'                => 'required|integer|min:1|max:10',
            'basic_min'            => 'required|numeric|min:0',
            'basic_max'            => 'required|numeric|gt:basic_min',
            'annual_increment_pct' => 'nullable|numeric|min:0|max:50',
            'leave_allowance_pct'  => 'nullable|numeric|min:0|max:100',
            'typical_title'        => 'nullable|string|max:100',
            'is_active'            => 'boolean',
        ]);

        // Enforce unique level+grade per tenant
        $exists = PayGrade::where('tenant_id', $tenantId)
            ->where('level', $request->level)
            ->where('grade', $request->grade)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', "Level {$request->level} Grade {$request->grade} already exists.");
        }

        PayGrade::create([
            'tenant_id'            => $tenantId,
            'code'                 => strtoupper($request->code),
            'name'                 => $request->name,
            'level'                => $request->level,
            'grade'                => $request->grade,
            'basic_min'            => $request->basic_min,
            'basic_max'            => $request->basic_max,
            'annual_increment_pct' => $request->annual_increment_pct ?? 5,
            'leave_allowance_pct'  => $request->leave_allowance_pct ?? 10,
            'typical_title'        => $request->typical_title,
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return back()->with('success', "Pay grade Level {$request->level} Grade {$request->grade} created successfully.");
    }

    public function updatePayGrade(Request $request, PayGrade $payGrade)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_if($payGrade->tenant_id !== $tenantId, 403);

        $request->validate([
            'code'                 => "required|string|max:20|unique:pay_grades,code,{$payGrade->id},id,tenant_id,{$tenantId}",
            'name'                 => 'required|string|max:100',
            'level'                => 'required|integer|min:1|max:17',
            'grade'                => 'required|integer|min:1|max:10',
            'basic_min'            => 'required|numeric|min:0',
            'basic_max'            => 'required|numeric|gt:basic_min',
            'annual_increment_pct' => 'nullable|numeric|min:0|max:50',
            'leave_allowance_pct'  => 'nullable|numeric|min:0|max:100',
            'typical_title'        => 'nullable|string|max:100',
            'is_active'            => 'boolean',
        ]);

        $payGrade->update([
            'code'                 => strtoupper($request->code),
            'name'                 => $request->name,
            'level'                => $request->level,
            'grade'                => $request->grade,
            'basic_min'            => $request->basic_min,
            'basic_max'            => $request->basic_max,
            'annual_increment_pct' => $request->annual_increment_pct ?? 5,
            'leave_allowance_pct'  => $request->leave_allowance_pct ?? 10,
            'typical_title'        => $request->typical_title,
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Pay grade updated successfully.');
    }

    // ── Pay Components ────────────────────────────────────────────────────────

    public function storePayComponent(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => "required|string|max:20|unique:pay_components,code,NULL,id,tenant_id,{$tenantId}",
            'type'             => 'required|in:earning,deduction',
            'is_statutory'     => 'boolean',
            'is_taxable'       => 'boolean',
            'computation_type' => 'required|in:fixed,percentage_of_basic,percentage_of_gross,formula',
            'value'            => 'nullable|numeric|min:0',
            'formula_key'      => 'nullable|string|max:50',
            'is_active'        => 'boolean',
        ]);

        PayComponent::create([
            'tenant_id'        => $tenantId,
            'name'             => $request->name,
            'code'             => strtoupper($request->code),
            'type'             => $request->type,
            'is_statutory'     => $request->boolean('is_statutory', false),
            'is_taxable'       => $request->boolean('is_taxable', true),
            'computation_type' => $request->computation_type,
            'value'            => $request->value,
            'formula_key'      => $request->formula_key,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Pay component created successfully.');
    }

    public function updatePayComponent(Request $request, PayComponent $payComponent)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_if($payComponent->tenant_id !== $tenantId, 403);

        $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => "required|string|max:20|unique:pay_components,code,{$payComponent->id},id,tenant_id,{$tenantId}",
            'type'             => 'required|in:earning,deduction',
            'is_statutory'     => 'boolean',
            'is_taxable'       => 'boolean',
            'computation_type' => 'required|in:fixed,percentage_of_basic,percentage_of_gross,formula',
            'value'            => 'nullable|numeric|min:0',
            'formula_key'      => 'nullable|string|max:50',
            'is_active'        => 'boolean',
        ]);

        $payComponent->update([
            'name'             => $request->name,
            'code'             => strtoupper($request->code),
            'type'             => $request->type,
            'is_statutory'     => $request->boolean('is_statutory', false),
            'is_taxable'       => $request->boolean('is_taxable', true),
            'computation_type' => $request->computation_type,
            'value'            => $request->value,
            'formula_key'      => $request->formula_key,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Pay component updated successfully.');
    }

    // ── Staff Pay Config ──────────────────────────────────────────────────────

    public function storePayConfig(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id'           => 'required|exists:staff_profiles,id',
            'pay_grade_id'               => 'nullable|exists:pay_grades,id',
            'basic_salary'               => 'required|numeric|min:0',
            'housing_allowance'          => 'required|numeric|min:0',
            'transport_allowance'        => 'required|numeric|min:0',
            'meal_allowance'             => 'required|numeric|min:0',
            'pension_fund_administrator' => 'nullable|string|max:100',
            'pension_account_number'     => 'nullable|string|max:50',
            'tax_id'                     => 'nullable|string|max:50',
            'nhf_number'                 => 'nullable|string|max:50',
            'effective_date'             => 'required|date',
        ]);

        StaffPayConfig::updateOrCreate(
            ['staff_profile_id' => $request->staff_profile_id, 'tenant_id' => $tenantId],
            [
                'pay_grade_id'               => $request->pay_grade_id,
                'basic_salary'               => $request->basic_salary,
                'housing_allowance'          => $request->housing_allowance,
                'transport_allowance'        => $request->transport_allowance,
                'meal_allowance'             => $request->meal_allowance,
                'pension_fund_administrator' => $request->pension_fund_administrator,
                'pension_account_number'     => $request->pension_account_number,
                'tax_id'                     => $request->tax_id,
                'nhf_number'                 => $request->nhf_number,
                'effective_date'             => $request->effective_date,
            ]
        );

        return back()->with('success', 'Staff pay configuration saved successfully.');
    }

    // ── Bank Details ──────────────────────────────────────────────────────────

    public function storeBankDetail(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'bank_name'        => 'required|string|max:100',
            'bank_code'        => 'required|string|max:10',
            'account_number'   => 'required|digits:10',
            'account_name'     => 'required|string|max:100',
            'is_primary'       => 'boolean',
        ]);

        if ($request->boolean('is_primary', false)) {
            StaffBankDetail::where('staff_profile_id', $request->staff_profile_id)
                ->update(['is_primary' => false]);
        }

        StaffBankDetail::create([
            'tenant_id'        => $tenantId,
            'staff_profile_id' => $request->staff_profile_id,
            'bank_name'        => $request->bank_name,
            'bank_code'        => $request->bank_code,
            'account_number'   => $request->account_number,
            'account_name'     => $request->account_name,
            'is_primary'       => $request->boolean('is_primary', false),
            'is_verified'      => false,
        ]);

        return back()->with('success', 'Bank detail added successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function nigerianBanks(): array
    {
        return [
            ['name' => 'Access Bank',                  'code' => '044'],
            ['name' => 'Citibank Nigeria',             'code' => '023'],
            ['name' => 'Ecobank Nigeria',              'code' => '050'],
            ['name' => 'Fidelity Bank',                'code' => '070'],
            ['name' => 'First Bank of Nigeria',        'code' => '011'],
            ['name' => 'First City Monument Bank',     'code' => '214'],
            ['name' => 'Globus Bank',                  'code' => '103'],
            ['name' => 'Guaranty Trust Bank',          'code' => '058'],
            ['name' => 'Heritage Bank',                'code' => '030'],
            ['name' => 'Keystone Bank',                'code' => '082'],
            ['name' => 'Kuda Bank',                    'code' => '090267'],
            ['name' => 'OPay',                         'code' => '100004'],
            ['name' => 'PalmPay',                      'code' => '100033'],
            ['name' => 'Polaris Bank',                 'code' => '076'],
            ['name' => 'Providus Bank',                'code' => '101'],
            ['name' => 'Stanbic IBTC Bank',            'code' => '221'],
            ['name' => 'Standard Chartered Bank',      'code' => '068'],
            ['name' => 'Sterling Bank',                'code' => '232'],
            ['name' => 'SunTrust Bank',                'code' => '100'],
            ['name' => 'Titan Trust Bank',             'code' => '102'],
            ['name' => 'Union Bank of Nigeria',        'code' => '032'],
            ['name' => 'United Bank for Africa',       'code' => '033'],
            ['name' => 'Unity Bank',                   'code' => '215'],
            ['name' => 'Wema Bank',                    'code' => '035'],
            ['name' => 'Zenith Bank',                  'code' => '057'],
        ];
    }
}
