<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PublicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicHolidayController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('tenant_id');
        $year = $request->integer('year', now()->year);

        $holidays = PublicHoliday::where('tenant_id', $tenantId)
            ->forYear($year)
            ->orderBy('date')
            ->get();

        $years = range(now()->year - 1, now()->year + 2);

        return view('hr.holidays.index', compact('holidays','year','years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:200',
            'date'         => 'required|date',
            'type'         => 'required|string',
            'is_recurring' => 'boolean',
            'notes'        => 'nullable|string|max:500',
        ]);

        PublicHoliday::create([
            'id'           => Str::uuid(),
            'tenant_id'    => session('tenant_id'),
            'name'         => $request->name,
            'date'         => $request->date,
            'type'         => $request->type,
            'is_recurring' => $request->boolean('is_recurring', true),
            'is_active'    => true,
            'notes'        => $request->notes,
        ]);

        return back()->with('success', 'Holiday added.');
    }

    public function update(Request $request, PublicHoliday $publicHoliday)
    {
        $request->validate([
            'name'         => 'required|string|max:200',
            'date'         => 'required|date',
            'type'         => 'required|string',
            'is_recurring' => 'boolean',
        ]);

        $publicHoliday->update($request->only('name','date','type','is_recurring','notes'));

        return back()->with('success', 'Holiday updated.');
    }

    public function destroy(PublicHoliday $publicHoliday)
    {
        $publicHoliday->delete();
        return back()->with('success', 'Holiday removed.');
    }

    public function toggle(PublicHoliday $publicHoliday)
    {
        $publicHoliday->update(['is_active' => ! $publicHoliday->is_active]);
        return back()->with('success', 'Holiday ' . ($publicHoliday->is_active ? 'activated' : 'deactivated') . '.');
    }
}
