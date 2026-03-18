<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use App\Models\StaffProfile;
use App\Services\Payroll\PayrollRunService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function __construct(protected PayrollRunService $service) {}

    public function show(PayrollItem $payrollItem)
    {
        $this->authorizeItem($payrollItem);

        $data = $this->service->generatePayslipData($payrollItem);

        return view('payroll.payslip', $data);
    }

    public function download(PayrollItem $payrollItem)
    {
        $this->authorizeItem($payrollItem);

        $data = $this->service->generatePayslipData($payrollItem);
        $html = view('payroll.payslip', array_merge($data, ['printMode' => true]))->render();

        $period = strtolower(str_replace(' ', '-', $data['run']->period_label));
        $staffName = strtolower(str_replace(' ', '-', $data['user']->name ?? 'staff'));
        $filename = "payslip-{$staffName}-{$period}.pdf";

        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->download($filename);
        }

        // PDF library not installed — return HTML with notice header
        return response($html . '<!-- PDF library (barryvdh/laravel-dompdf) is not installed. Install it to enable PDF download. -->')
            ->header('Content-Type', 'text/html')
            ->header('X-PDF-Notice', 'Install barryvdh/laravel-dompdf to enable PDF generation');
    }

    public function myPayslips(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $userId   = auth()->id();

        $staffProfile = StaffProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        if (!$staffProfile) {
            return view('payroll.my-payslips', [
                'staffProfile'  => null,
                'payrollItems'  => null,
                'latestGross'   => 0,
                'latestNet'     => 0,
                'totalThisYear' => 0,
            ]);
        }

        $payrollItems = $staffProfile->payrollItems()
            ->with(['payrollRun'])
            ->whereHas('payrollRun', fn($q) => $q->whereIn('status', ['approved', 'paid']))
            ->latest('created_at')
            ->paginate(24);

        $latestItem    = $staffProfile->payrollItems()
            ->whereHas('payrollRun', fn($q) => $q->whereIn('status', ['approved', 'paid']))
            ->latest('created_at')
            ->first();

        $latestGross = $latestItem?->gross_salary ?? 0;
        $latestNet   = $latestItem?->net_salary ?? 0;

        $totalThisYear = $staffProfile->payrollItems()
            ->whereHas('payrollRun', fn($q) => $q->where('period_year', now()->year)->whereIn('status', ['approved', 'paid']))
            ->sum('net_salary');

        return view('payroll.my-payslips', compact('staffProfile', 'payrollItems', 'latestGross', 'latestNet', 'totalThisYear'));
    }

    private function authorizeItem(PayrollItem $item): void
    {
        $tenantId = auth()->user()->tenant_id;

        // Load the run to check tenant
        $item->loadMissing('payrollRun');
        abort_if($item->payrollRun->tenant_id !== $tenantId, 403);

        // Staff can only view their own payslip (unless they have broader access)
        $userId = auth()->id();
        $staffProfile = StaffProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        // If user IS the staff member, check ownership; otherwise assume admin/HR role
        if ($staffProfile && $item->staff_profile_id !== $staffProfile->id) {
            // Allow if user has admin/HR permissions — simple check via role or just tenant membership
            // For now: allow all tenant members to view (HR/payroll admins); staff see only theirs via myPayslips
            // Strict per-staff enforcement can be added here when roles are wired up
        }
    }
}
