<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\StaffProfile;
use App\Services\Hr\PerformanceService;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function __construct(protected PerformanceService $performanceService) {}

    public function myReviews()
    {
        $tenantId = auth()->user()->tenant_id;

        $profile = StaffProfile::where('user_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $reviews = PerformanceReview::where('staff_profile_id', $profile->id)
            ->with('reviewCycle')
            ->latest('created_at')
            ->get();

        return view('hr.performance.my-reviews', compact('profile', 'reviews'));
    }

    public function show(PerformanceReview $performanceReview)
    {
        abort_unless($performanceReview->tenant_id === auth()->user()->tenant_id, 403);

        $performanceReview->load(['items', 'staffProfile.user', 'reviewer', 'reviewCycle']);

        return view('hr.performance.show', compact('performanceReview'));
    }

    public function selfAssess(Request $request, PerformanceReview $performanceReview)
    {
        abort_unless($performanceReview->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'scores'          => 'required|array',
            'scores.*'        => 'required|numeric|min:1|max:5',
            'staff_comments'  => 'required|string|max:2000',
        ]);

        if ($performanceReview->status !== 'pending') {
            return back()->with('error', 'Self-assessment can only be submitted for pending reviews.');
        }

        $this->performanceService->submitSelfAssessment(
            $performanceReview,
            $request->scores,
            $request->staff_comments
        );

        return back()->with('success', 'Self-assessment submitted successfully.');
    }

    public function managerReview(Request $request, PerformanceReview $performanceReview)
    {
        abort_unless($performanceReview->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'scores'           => 'required|array',
            'scores.*'         => 'required|numeric|min:1|max:5',
            'manager_comments' => 'required|string|max:2000',
        ]);

        if ($performanceReview->status !== 'self_assessed') {
            return back()->with('error', 'Manager review can only be submitted after self-assessment.');
        }

        $this->performanceService->submitManagerReview(
            $performanceReview,
            $request->scores,
            $request->manager_comments,
            auth()->user()
        );

        return back()->with('success', 'Manager review submitted successfully.');
    }

    public function hrApprove(PerformanceReview $performanceReview)
    {
        abort_unless($performanceReview->tenant_id === auth()->user()->tenant_id, 403);

        if ($performanceReview->status !== 'manager_reviewed') {
            return back()->with('error', 'Only manager-reviewed assessments can be approved by HR.');
        }

        $this->performanceService->approveByHr($performanceReview);

        return back()->with('success', 'Performance review approved by HR.');
    }
}
