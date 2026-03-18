<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !(auth()->user()->hasRole('tenant_admin') || auth()->user()->hasRole('super_admin'))) {
                abort(403, 'Unauthorized access to Role Management.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Global scope handles isolating standard vs tenant-specific roles
        $roles = Role::orderBy('tenant_id')->paginate(15);
        
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Ensure name is unique for this tenant
        $exists = Role::where('name', $validated['name'])
            ->where('tenant_id', $tenantId)
            ->exists();

        if ($exists) {
            return back()->with('error', 'A role with this name already exists in your institution.')->withInput();
        }

        $role = Role::create([
            'name' => $validated['name'],
            'tenant_id' => $tenantId, // Lock custom roles to the current tenant
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Custom Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        // Don't allow editing standard internal roles
        if (is_null($role->tenant_id)) {
            return redirect()->route('roles.index')->with('error', 'Standard system roles cannot be modified.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        if (is_null($role->tenant_id)) {
            return redirect()->route('roles.index')->with('error', 'Standard system roles cannot be modified.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Check unique constraint except current role
        $exists = Role::where('name', $validated['name'])
            ->where('tenant_id', $role->tenant_id)
            ->where('id', '!=', $role->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'A role with this name already exists in your institution.')->withInput();
        }

        $role->update([
            'name' => $validated['name']
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            // Uncheck all permissions
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Custom Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (is_null($role->tenant_id)) {
            return redirect()->route('roles.index')->with('error', 'Standard system roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete this role because users are currently assigned to it.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Custom Role deleted successfully.');
    }
}
