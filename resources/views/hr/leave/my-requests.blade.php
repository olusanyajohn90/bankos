@extends('layouts.app')

@section('title', 'My Leave')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Leave</h1>
        <p class="text-sm text-gray-500 mt-1">View your leave balances and submit leave requests.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Balance Cards --}}
    <div class="mb-8">
        <h2 class="text-base font-semibold text-gray-800 mb-4">
            Leave Balances — {{ now()->year }}
        </h2>
        @if ($balances->isEmpty())
            <p class="text-sm text-gray-500 italic">No leave balances found for this year. Contact HR to initialise balances.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($balances as $balance)
                    @php
                        $available   = $balance->availableDays();
                        $pct         = $balance->entitled_days > 0
                            ? min(100, round(($balance->used_days / $balance->entitled_days) * 100))
                            : 0;
                        $barColor    = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-400' : 'bg-green-500');
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ optional($balance->leaveType)->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ optional($balance->leaveType)->code }}</p>
                            </div>
                            @if (optional($balance->leaveType)->is_paid)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Paid</span>
                            @endif
                        </div>
                        <div class="space-y-1 text-xs text-gray-600 mb-3">
                            <div class="flex justify-between">
                                <span>Entitled</span>
                                <span class="font-medium text-gray-800">{{ $balance->entitled_days }} days</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Used</span>
                                <span class="font-medium text-gray-800">{{ $balance->used_days }} days</span>
                            </div>
                            @if ($balance->pending_days > 0)
                            <div class="flex justify-between text-yellow-600">
                                <span>Pending</span>
                                <span class="font-medium">{{ $balance->pending_days }} days</span>
                            </div>
                            @endif
                            <div class="flex justify-between border-t border-gray-100 pt-1 mt-1">
                                <span class="font-semibold text-gray-800">Available</span>
                                <span class="font-bold {{ $available <= 0 ? 'text-red-600' : 'text-blue-700' }}">{{ $available }} days</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $pct }}% used</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Apply for Leave Form --}}
        <div class="lg:col-span-1">
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Apply for Leave</h3>
                <form action="{{ route('hr.leave.requests.store') }}" method="POST" class="space-y-4"
                      x-data="leaveForm()" x-init="init()">
                    @csrf
                    <input type="hidden" name="staff_profile_id" value="{{ $profile->id }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type_id" required
                                class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                x-model="leaveTypeId">
                            <option value="">Select leave type...</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" required
                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                               x-model="startDate" @change="computeDays()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" required
                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                               x-model="endDate" @change="computeDays()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Days Requested <span class="text-red-500">*</span></label>
                        <input type="number" name="days_requested" step="0.5" min="0.5" required
                               class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                               x-model="daysRequested">
                        <p class="text-xs text-gray-400 mt-1">Calendar days between dates (excluding weekends if applicable).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <textarea name="reason" rows="3" placeholder="Optional reason for leave..."
                                  class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Relief Officer</label>
                        <select name="relief_officer_id" class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">— None —</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-primary w-full py-2.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Submit Leave Request
                    </button>
                </form>
            </div>
        </div>

        {{-- Leave History --}}
        <div class="lg:col-span-2">
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Leave History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($requests as $req)
                                @php
                                    $statusColors = [
                                        'pending'   => 'bg-yellow-100 text-yellow-800',
                                        'approved'  => 'bg-green-100 text-green-800',
                                        'rejected'  => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-500',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ optional($req->leaveType)->name }}</td>
                                    <td class="px-4 py-3 text-gray-600 text-xs">
                                        {{ $req->start_date->format('d M Y') }}<br>
                                        <span class="text-gray-400">to {{ $req->end_date->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $req->days_requested }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                        @if ($req->status === 'rejected' && $req->rejection_reason)
                                            <p class="text-xs text-red-500 mt-0.5 max-w-xs truncate" title="{{ $req->rejection_reason }}">
                                                {{ Str::limit($req->rejection_reason, 35) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ optional($req->approver)->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if (in_array($req->status, ['pending', 'approved']))
                                            <form action="{{ route('hr.leave.requests.cancel', $req) }}" method="POST"
                                                  onsubmit="return confirm('Cancel this leave request?')">
                                                @csrf
                                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm">No leave requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($requests->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">{{ $requests->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function leaveForm() {
    return {
        leaveTypeId: '',
        startDate: '',
        endDate: '',
        daysRequested: '',
        init() {},
        computeDays() {
            if (this.startDate && this.endDate) {
                const start = new Date(this.startDate);
                const end   = new Date(this.endDate);
                if (end >= start) {
                    const diff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    this.daysRequested = diff;
                }
            }
        }
    }
}
</script>
@endpush
@endsection
