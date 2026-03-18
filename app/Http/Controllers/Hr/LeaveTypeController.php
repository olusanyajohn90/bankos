<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\Hr\LeaveService;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $leaveTypes = LeaveType::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('hr.leave.types', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'               => 'required|string|max:100',
            'code'               => "required|string|max:20|unique:leave_types,code,NULL,id,tenant_id,{$tenantId}",
            'days_entitled'      => 'required|numeric|min:0.5',
            'carry_over_days'    => 'required|numeric|min:0',
            'gender_restriction' => 'required|in:all,male,female',
            'requires_approval'  => 'boolean',
            'is_paid'            => 'boolean',
            'is_active'          => 'boolean',
        ]);

        LeaveType::create([
            'tenant_id'          => $tenantId,
            'name'               => $request->name,
            'code'               => strtoupper($request->code),
            'days_entitled'      => $request->days_entitled,
            'carry_over_days'    => $request->carry_over_days ?? 0,
            'gender_restriction' => $request->gender_restriction,
            'requires_approval'  => $request->boolean('requires_approval'),
            'is_paid'            => $request->boolean('is_paid'),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Leave type created successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'               => 'required|string|max:100',
            'code'               => "required|string|max:20|unique:leave_types,code,{$leaveType->id},id,tenant_id,{$tenantId}",
            'days_entitled'      => 'required|numeric|min:0.5',
            'carry_over_days'    => 'required|numeric|min:0',
            'gender_restriction' => 'required|in:all,male,female',
            'requires_approval'  => 'boolean',
            'is_paid'            => 'boolean',
            'is_active'          => 'boolean',
        ]);

        $leaveType->update([
            'name'               => $request->name,
            'code'               => strtoupper($request->code),
            'days_entitled'      => $request->days_entitled,
            'carry_over_days'    => $request->carry_over_days ?? 0,
            'gender_restriction' => $request->gender_restriction,
            'requires_approval'  => $request->boolean('requires_approval'),
            'is_paid'            => $request->boolean('is_paid'),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaveRequests()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'Cannot delete: this leave type has active or pending requests.');
        }

        $leaveType->delete();

        return back()->with('success', 'Leave type deleted successfully.');
    }

    public function initBalances(Request $request, LeaveService $leaveService)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $count    = $leaveService->initBalances($tenantId, (int) $request->year);

        return back()->with('success', "Leave balances initialised: {$count} balance records created for {$request->year}.");
    }
}
