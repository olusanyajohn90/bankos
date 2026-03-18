<?php
namespace App\Services\Hr;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function initBalances(string $tenantId, int $year): int
    {
        $types = LeaveType::where('tenant_id', $tenantId)->active()->get();
        $profiles = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $created = 0;
        foreach ($profiles as $profile) {
            foreach ($types as $type) {
                // Respect gender restriction
                if ($type->gender_restriction !== 'all') {
                    $gender = $profile->user->gender ?? null;
                    if ($gender !== $type->gender_restriction) continue;
                }
                $balance = LeaveBalance::firstOrCreate(
                    ['staff_profile_id' => $profile->id, 'leave_type_id' => $type->id, 'year' => $year],
                    ['tenant_id' => $tenantId, 'entitled_days' => $type->days_entitled, 'used_days' => 0, 'pending_days' => 0]
                );
                if ($balance->wasRecentlyCreated) $created++;
            }
        }
        return $created;
    }

    public function requestLeave(StaffProfile $staff, array $data): LeaveRequest
    {
        $type = LeaveType::findOrFail($data['leave_type_id']);
        $year = now()->year;
        $balance = LeaveBalance::where('staff_profile_id', $staff->id)
            ->where('leave_type_id', $type->id)
            ->where('year', $year)
            ->firstOrFail();

        $daysRequested = $data['days_requested'];

        if ($balance->availableDays() < $daysRequested) {
            throw new \InvalidArgumentException("Insufficient leave balance. Available: {$balance->availableDays()} days.");
        }

        // Check for overlapping requests
        $overlap = LeaveRequest::where('staff_profile_id', $staff->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                  ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                  ->orWhere(function ($q) use ($data) {
                      $q->where('start_date', '<=', $data['start_date'])->where('end_date', '>=', $data['end_date']);
                  });
            })->exists();

        if ($overlap) {
            throw new \InvalidArgumentException('You have an overlapping leave request for this period.');
        }

        return DB::transaction(function () use ($staff, $data, $balance, $daysRequested, $type) {
            $request = LeaveRequest::create([
                'tenant_id'         => $staff->tenant_id,
                'staff_profile_id'  => $staff->id,
                'leave_type_id'     => $type->id,
                'start_date'        => $data['start_date'],
                'end_date'          => $data['end_date'],
                'days_requested'    => $daysRequested,
                'reason'            => $data['reason'] ?? null,
                'relief_officer_id' => $data['relief_officer_id'] ?? null,
                'status'            => $type->requires_approval ? 'pending' : 'approved',
                'approved_at'       => $type->requires_approval ? null : now(),
            ]);

            $balance->increment('pending_days', $daysRequested);
            return $request;
        });
    }

    public function approveLeave(LeaveRequest $request, User $approver): void
    {
        DB::transaction(function () use ($request, $approver) {
            $request->update([
                'status'      => 'approved',
                'approver_id' => $approver->id,
                'approved_at' => now(),
            ]);
            LeaveBalance::where('staff_profile_id', $request->staff_profile_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $request->start_date->year)
                ->decrement('pending_days', $request->days_requested);
            LeaveBalance::where('staff_profile_id', $request->staff_profile_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $request->start_date->year)
                ->increment('used_days', $request->days_requested);
        });
    }

    public function rejectLeave(LeaveRequest $request, User $approver, string $reason): void
    {
        DB::transaction(function () use ($request, $approver, $reason) {
            $request->update([
                'status'           => 'rejected',
                'approver_id'      => $approver->id,
                'rejection_reason' => $reason,
            ]);
            LeaveBalance::where('staff_profile_id', $request->staff_profile_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $request->start_date->year)
                ->decrement('pending_days', $request->days_requested);
        });
    }

    public function cancelLeave(LeaveRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $wasApproved = $request->status === 'approved';
            $request->update(['status' => 'cancelled']);

            if ($wasApproved) {
                LeaveBalance::where('staff_profile_id', $request->staff_profile_id)
                    ->where('leave_type_id', $request->leave_type_id)
                    ->where('year', $request->start_date->year)
                    ->decrement('used_days', $request->days_requested);
            } else {
                LeaveBalance::where('staff_profile_id', $request->staff_profile_id)
                    ->where('leave_type_id', $request->leave_type_id)
                    ->where('year', $request->start_date->year)
                    ->decrement('pending_days', $request->days_requested);
            }
        });
    }

    public function carryOverBalances(string $tenantId, int $fromYear, int $toYear): int
    {
        $types = LeaveType::where('tenant_id', $tenantId)->where('carry_over_days', '>', 0)->get()->keyBy('id');
        $balances = LeaveBalance::where('tenant_id', $tenantId)->where('year', $fromYear)->get();
        $carried = 0;

        foreach ($balances as $balance) {
            $type = $types[$balance->leave_type_id] ?? null;
            if (!$type || $type->carry_over_days <= 0) continue;
            $carryAmount = min($balance->availableDays(), $type->carry_over_days);
            if ($carryAmount <= 0) continue;

            $newBalance = LeaveBalance::firstOrCreate(
                ['staff_profile_id' => $balance->staff_profile_id, 'leave_type_id' => $balance->leave_type_id, 'year' => $toYear],
                ['tenant_id' => $tenantId, 'entitled_days' => $type->days_entitled, 'used_days' => 0, 'pending_days' => 0]
            );
            $newBalance->increment('entitled_days', $carryAmount);
            $carried++;
        }
        return $carried;
    }
}
