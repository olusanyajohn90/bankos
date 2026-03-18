<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Services\Payroll\PayrollRunService;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(protected PayrollRunService $service) {}

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $runs = PayrollRun::where('tenant_id', $tenantId)
            ->with(['runBy', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return view('payroll.runs.index', compact('runs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year'  => 'required|integer|min:2000|max:2099',
            'notes'        => 'nullable|string|max:500',
        ]);

        try {
            $run = $this->service->initRun(
                auth()->user()->tenant_id,
                (int) $request->period_month,
                (int) $request->period_year,
                auth()->user()
            );

            if ($request->filled('notes')) {
                $run->update(['notes' => $request->notes]);
            }

            return redirect()->route('payroll.runs.show', $run)
                ->with('success', "Payroll run for {$run->period_label} created successfully.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(PayrollRun $payrollRun)
    {
        $this->authorizeRun($payrollRun);

        $items = $payrollRun->items()
            ->with(['staffProfile.user', 'staffProfile.payConfig.payGrade', 'bankDetail'])
            ->paginate(50);

        $payrollRun->load(['runBy', 'approvedBy']);

        return view('payroll.runs.show', compact('payrollRun', 'items'));
    }

    public function process(PayrollRun $payrollRun)
    {
        $this->authorizeRun($payrollRun);

        abort_if($payrollRun->status !== 'draft', 403, 'Only draft runs can be processed.');

        try {
            $this->service->processRun($payrollRun);
            return back()->with('success', "Payroll run for {$payrollRun->period_label} processed successfully. {$payrollRun->fresh()->staff_count} staff computed.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, PayrollRun $payrollRun)
    {
        $this->authorizeRun($payrollRun);

        try {
            $this->service->approveRun($payrollRun, auth()->user());
            return back()->with('success', "Payroll run for {$payrollRun->period_label} approved successfully.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(PayrollRun $payrollRun)
    {
        $this->authorizeRun($payrollRun);

        try {
            $this->service->markPaid($payrollRun);
            return back()->with('success', "Payroll run for {$payrollRun->period_label} marked as paid.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(PayrollRun $payrollRun)
    {
        $this->authorizeRun($payrollRun);

        try {
            $this->service->cancelRun($payrollRun);
            return back()->with('success', "Payroll run for {$payrollRun->period_label} cancelled.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function authorizeRun(PayrollRun $run): void
    {
        abort_if($run->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
