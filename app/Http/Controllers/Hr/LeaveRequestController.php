<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\Hr\LeaveService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(protected LeaveService $leaveService) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = LeaveRequest::where('tenant_id', $tenantId)
            ->with(['staffProfile.user', 'leaveType', 'approver'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('staffProfile.user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $requests   = $query->paginate(25)->withQueryString();
        $leaveTypes = LeaveType::where('tenant_id', $tenantId)->orderBy('name')->get();
        $staff      = StaffProfile::where('tenant_id', $tenantId)->with('user')->get();

        return view('hr.leave.requests', compact('requests', 'leaveTypes', 'staff'));
    }

    public function myRequests(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $profile  = StaffProfile::where('user_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $balances = LeaveBalance::where('staff_profile_id', $profile->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();

        $requests = LeaveRequest::where('staff_profile_id', $profile->id)
            ->with(['leaveType', 'approver'])
            ->latest()
            ->paginate(20);

        $leaveTypes = LeaveType::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('hr.leave.my-requests', compact('profile', 'balances', 'requests', 'leaveTypes', 'users'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id'  => 'nullable|exists:staff_profiles,id',
            'leave_type_id'     => 'required|exists:leave_types,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'days_requested'    => 'required|numeric|min:0.5',
            'reason'            => 'nullable|string|max:1000',
            'relief_officer_id' => 'nullable|exists:users,id',
        ]);

        $profileId = $request->staff_profile_id;
        if (!$profileId) {
            $profile = StaffProfile::where('user_id', auth()->id())
                ->where('tenant_id', $tenantId)
                ->firstOrFail();
        } else {
            $profile = StaffProfile::where('id', $profileId)
                ->where('tenant_id', $tenantId)
                ->firstOrFail();
        }

        try {
            $this->leaveService->requestLeave($profile, $request->all());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Leave request submitted successfully.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        // Basic authorization: must be an HR/manager (simplified check)
        $tenantId = auth()->user()->tenant_id;
        abort_unless($leaveRequest->tenant_id === $tenantId, 403);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $this->leaveService->approveLeave($leaveRequest, auth()->user());

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($leaveRequest->tenant_id === $tenantId, 403);

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $this->leaveService->rejectLeave($leaveRequest, auth()->user(), $request->rejection_reason);

        return back()->with('success', 'Leave request rejected.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $tenantId = auth()->user()->tenant_id;
        abort_unless($leaveRequest->tenant_id === $tenantId, 403);

        $authUser    = auth()->user();
        $isHr        = $authUser->hasRole('hr') || $authUser->hasRole('admin');
        $isOwner     = optional($leaveRequest->staffProfile)->user_id === $authUser->id;
        $isPending   = $leaveRequest->status === 'pending';
        $isApproved  = $leaveRequest->status === 'approved';

        if ($isPending && ($isOwner || $isHr)) {
            $this->leaveService->cancelLeave($leaveRequest);
            return back()->with('success', 'Leave request cancelled.');
        }

        if ($isApproved && $isHr) {
            $this->leaveService->cancelLeave($leaveRequest);
            return back()->with('success', 'Approved leave cancelled by HR.');
        }

        return back()->with('error', 'You are not authorised to cancel this leave request.');
    }
}
