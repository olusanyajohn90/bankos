<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\ReviewCycle;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\Hr\PerformanceService;
use Illuminate\Http\Request;

class ReviewCycleController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $cycles = ReviewCycle::where('tenant_id', $tenantId)
            ->withCount('reviews')
            ->latest('start_date')
            ->paginate(10);

        return view('hr.performance.cycles', compact('cycles'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:150',
            'period_type' => 'required|in:annual,semi_annual,quarterly',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
        ]);

        ReviewCycle::create([
            'tenant_id'   => $tenantId,
            'name'        => $request->name,
            'period_type' => $request->period_type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'status'      => 'draft',
        ]);

        return back()->with('success', 'Review cycle created successfully.');
    }

    public function activate(ReviewCycle $reviewCycle, PerformanceService $performanceService)
    {
        abort_unless($reviewCycle->tenant_id === auth()->user()->tenant_id, 403);

        if ($reviewCycle->status !== 'draft') {
            return back()->with('error', 'Only draft cycles can be activated.');
        }

        $tenantId = auth()->user()->tenant_id;

        $staffProfiles = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($staffProfiles as $staff) {
            // Attempt to find the staff member's manager as reviewer
            $reviewer = null;
            if ($staff->user && $staff->user->manager_id) {
                $reviewer = User::find($staff->user->manager_id);
            }

            $performanceService->createReview($reviewCycle, $staff, $reviewer);
            $count++;
        }

        $reviewCycle->update(['status' => 'active']);

        return back()->with('success', "Review cycle activated. {$count} reviews created.");
    }

    public function close(ReviewCycle $reviewCycle)
    {
        abort_unless($reviewCycle->tenant_id === auth()->user()->tenant_id, 403);

        $reviewCycle->update(['status' => 'closed']);

        return back()->with('success', 'Review cycle closed.');
    }

    public function show(ReviewCycle $reviewCycle)
    {
        abort_unless($reviewCycle->tenant_id === auth()->user()->tenant_id, 403);

        $reviews = $reviewCycle->reviews()
            ->with(['staffProfile.user', 'staffProfile.orgDepartment', 'reviewer'])
            ->paginate(25);

        return view('hr.performance.cycle-show', compact('reviewCycle', 'reviews'));
    }
}
