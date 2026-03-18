@extends('layouts.app')

@section('title', 'Leave Requests (HR)')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Leave Requests</h1>
        <p class="text-sm text-gray-500 mt-1">Review, approve, and manage staff leave requests.</p>
    </div>

    @include('hr.org._tabs')

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filter Bar --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="form-input rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Leave Type</label>
                <select name="leave_type_id" class="form-input rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach ($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Search Staff</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Staff name..."
                       class="form-input rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 w-48">
            </div>
            <button type="submit" class="btn-primary px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Filter</button>
            @if (request()->hasAny(['status', 'leave_type_id', 'search']))
                <a href="{{ route('hr.leave.requests.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    {{-- Requests Table --}}
    <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Start</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">End</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Days</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Reason</th>
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
                        <tr class="hover:bg-gray-50" x-data="{ rejectModal: false }">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ optional($req->staffProfile->user)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ optional($req->leaveType)->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $req->start_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $req->end_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $req->days_requested }}</td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs">
                                <span title="{{ $req->reason }}">{{ Str::limit($req->reason, 40) ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ optional($req->approver)->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($req->status === 'pending')
                                        <form action="{{ route('hr.leave.requests.approve', $req) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 px-2 py-1 rounded"
                                                    onclick="return confirm('Approve this leave request?')">Approve</button>
                                        </form>
                                        <button @click="rejectModal = true" class="text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 px-2 py-1 rounded">Reject</button>
                                    @endif
                                    @if (in_array($req->status, ['pending', 'approved']))
                                        <form action="{{ route('hr.leave.requests.cancel', $req) }}" method="POST"
                                              onsubmit="return confirm('Cancel this leave request?')">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">Cancel</button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Reject Modal --}}
                                <div x-show="rejectModal" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
                                        <h3 class="text-base font-semibold text-gray-900 mb-2">Reject Leave Request</h3>
                                        <p class="text-sm text-gray-500 mb-4">
                                            Rejecting leave for <strong>{{ optional($req->staffProfile->user)->name }}</strong> — {{ $req->days_requested }} days.
                                        </p>
                                        <form action="{{ route('hr.leave.requests.reject', $req) }}" method="POST">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                                                <textarea name="rejection_reason" rows="3" required
                                                          class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-red-400 focus:ring-red-400"
                                                          placeholder="Provide a reason for rejection..."></textarea>
                                            </div>
                                            <div class="flex justify-end gap-3">
                                                <button type="button" @click="rejectModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Reject Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-400 text-sm">No leave requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
