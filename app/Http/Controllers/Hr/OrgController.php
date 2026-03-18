<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Division;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;

class OrgController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $regions = Region::where('tenant_id', $tenantId)
            ->with('manager')
            ->withCount('branches')
            ->orderBy('name')
            ->get();

        $divisions = Division::where('tenant_id', $tenantId)
            ->withCount('departments')
            ->orderBy('name')
            ->get();

        $departments = Department::where('tenant_id', $tenantId)
            ->with(['division', 'head'])
            ->withCount('staffProfiles')
            ->orderBy('name')
            ->get();

        $users = User::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('hr.org.index', compact('regions', 'divisions', 'departments', 'users'));
    }

    // ── Regions ──────────────────────────────────────────────────────────────

    public function storeRegion(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => "required|string|max:10|unique:regions,code,NULL,id,tenant_id,{$tenantId}",
            'manager_id' => 'nullable|exists:users,id',
            'status'     => 'required|in:active,inactive',
        ]);

        Region::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->name,
            'code'       => strtoupper($request->code),
            'manager_id' => $request->manager_id,
            'status'     => $request->status,
        ]);

        return back()->with('success', 'Region created successfully.');
    }

    public function updateRegion(Request $request, Region $region)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => "required|string|max:10|unique:regions,code,{$region->id},id,tenant_id,{$tenantId}",
            'manager_id' => 'nullable|exists:users,id',
            'status'     => 'required|in:active,inactive',
        ]);

        $region->update([
            'name'       => $request->name,
            'code'       => strtoupper($request->code),
            'manager_id' => $request->manager_id,
            'status'     => $request->status,
        ]);

        return back()->with('success', 'Region updated successfully.');
    }

    public function destroyRegion(Region $region)
    {
        if ($region->branches()->exists()) {
            return back()->with('error', 'Cannot delete: branches are assigned to this region.');
        }

        $region->delete();

        return back()->with('success', 'Region deleted successfully.');
    }

    // ── Divisions ────────────────────────────────────────────────────────────

    public function storeDivision(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        Division::create([
            'tenant_id'   => $tenantId,
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Division created successfully.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $division->update([
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Division updated successfully.');
    }

    public function destroyDivision(Division $division)
    {
        if ($division->departments()->exists()) {
            return back()->with('error', 'Cannot delete: departments are assigned to this division.');
        }

        $division->delete();

        return back()->with('success', 'Division deleted successfully.');
    }

    // ── Departments ──────────────────────────────────────────────────────────

    public function storeDepartment(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'              => 'required|string|max:100',
            'code'              => 'required|string|max:20',
            'division_id'       => 'nullable|exists:divisions,id',
            'head_id'           => 'nullable|exists:users,id',
            'cost_centre_code'  => 'nullable|string|max:20',
            'status'            => 'required|in:active,inactive',
        ]);

        Department::create([
            'tenant_id'        => $tenantId,
            'name'             => $request->name,
            'code'             => strtoupper($request->code),
            'division_id'      => $request->division_id,
            'head_id'          => $request->head_id,
            'cost_centre_code' => $request->cost_centre_code,
            'status'           => $request->status,
        ]);

        return back()->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20',
            'division_id'      => 'nullable|exists:divisions,id',
            'head_id'          => 'nullable|exists:users,id',
            'cost_centre_code' => 'nullable|string|max:20',
            'status'           => 'required|in:active,inactive',
        ]);

        $department->update([
            'name'             => $request->name,
            'code'             => strtoupper($request->code),
            'division_id'      => $request->division_id,
            'head_id'          => $request->head_id,
            'cost_centre_code' => $request->cost_centre_code,
            'status'           => $request->status,
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department)
    {
        if ($department->staffProfiles()->exists()) {
            return back()->with('error', 'Cannot delete: staff profiles are assigned to this department.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }
}
