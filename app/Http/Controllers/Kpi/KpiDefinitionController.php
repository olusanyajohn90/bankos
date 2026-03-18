<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiDefinition;
use Illuminate\Http\Request;

class KpiDefinitionController extends Controller
{
    public function index()
    {
        $kpis = KpiDefinition::orderBy('category')->orderBy('name')->paginate(50);
        $categories = KpiDefinition::select('category')->distinct()->pluck('category');
        return view('kpi.setup.definitions', compact('kpis', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'                   => 'required|string|max:60|unique:kpi_definitions,code',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'category'               => 'required|in:business_development,credit_lending,operations,customer_service,branch',
            'unit'                   => 'required|string|max:30',
            'direction'              => 'required|in:higher_better,lower_better,target_exact',
            'weight'                 => 'required|numeric|min:0|max:10',
            'computation_type'       => 'required|in:auto,manual',
            'department_applicable'  => 'nullable|array',
        ]);

        KpiDefinition::create([
            ...$data,
            'tenant_id' => auth()->user()->tenant_id,
            'is_system' => false,
        ]);

        return back()->with('success', 'KPI definition created.');
    }

    public function update(Request $request, KpiDefinition $kpiDefinition)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'weight'                => 'required|numeric|min:0|max:10',
            'is_active'             => 'boolean',
            'alert_threshold_pct'   => 'nullable|integer|min:1|max:99',
            'department_applicable' => 'nullable|array',
        ]);

        $kpiDefinition->update($data);
        return back()->with('success', 'KPI definition updated.');
    }

    public function destroy(KpiDefinition $kpiDefinition)
    {
        if ($kpiDefinition->is_system) {
            return back()->with('error', 'System KPIs cannot be deleted.');
        }
        $kpiDefinition->delete();
        return back()->with('success', 'KPI definition deleted.');
    }
}
