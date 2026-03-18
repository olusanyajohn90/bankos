<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::paginate();
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'institution_code' => 'required|string|max:20|unique:tenants,institution_code',
        ]);

        Tenant::create([
            'id' => Str::uuid(),
            'name' => $validated['name'],
            'domain' => $validated['domain'],
            'institution_code' => $validated['institution_code'],
            'status' => 'active',
        ]);

        return redirect()->route('tenants.index')->with('success', 'Institution/Tenant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'institution_code' => 'required|string|max:20|unique:tenants,institution_code,' . $tenant->id,
            'status' => 'required|in:active,inactive',
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.index')->with('success', 'Institution/Tenant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        if ($tenant->id === session('tenant_id')) {
            return back()->with('error', 'Cannot delete the currently active tenant.');
        }

        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Institution/Tenant deleted successfully.');
    }
}
