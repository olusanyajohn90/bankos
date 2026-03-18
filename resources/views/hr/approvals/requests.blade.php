@extends('layouts.app')

@section('title', 'Approval Requests — Inbox')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Approval Requests</h1>
            <p class="text-sm text-gray-500 mt-0.5">Review and action pending approval requests across the system.</p>
        </div>
        <a href="{{ route('hr.approvals.matrix') }}" class="btn btn-secondary text-sm">⚙ Configure Matrices</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- My Inbox --}}
    @if($pending->isNotEmpty())
    <div class="card p-6 border-l-4 border-amber-400">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">{{ $pending->count() }}</span>
            Awaiting Your Action
        </h2>
        <div class="space-y-3">
            @foreach($pending as $req)
                @php
                    $statusColor = match($req->status) {
                        'pending', 'in_review' => 'bg-amber-100 text-amber-800',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <div class="flex items-start gap-4 p-4 rounded-xl bg-amber-50 border border-amber-200">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">{{ $req->reference }}</span>
                            <span class="text-xs text-gray-500">Step {{ $req->current_step }} of {{ $req->total_steps }}</span>
                            @if($req->due_at)
                                <span class="text-xs text-gray-400">Due {{ $req->due_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $req->summary }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ ucwords(str_replace('_', ' ', $req->action_type)) }}
                            @if($req->amount) · ₦{{ number_format($req->amount) }} @endif
                            · Initiated by {{ $req->initiatedBy?->name ?? '—' }}
                            · {{ $req->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <form action="{{ route('hr.approvals.requests.approve', $req) }}" method="POST" class="inline">
                            @csrf
                            <button class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">Approve</button>
                        </form>
                        <a href="{{ route('hr.approvals.requests.show', $req) }}" class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg">View</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <select name="status" onchange="this.form.submit()" class="form-input text-sm py-1.5">
            <option value="">All Statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In Review</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <select name="action_type" onchange="this.form.submit()" class="form-input text-sm py-1.5">
            <option value="">All Action Types</option>
            @foreach(['loan_disbursal'=>'Loan Disbursement','expense_claim'=>'Expense Claim','leave_request'=>'Leave Request','asset_purchase'=>'Asset Purchase','payroll_run'=>'Payroll Run','staff_hire'=>'Staff Hire'] as $k=>$v)
                <option value="{{ $k }}" {{ request('action_type') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </form>

    {{-- All Requests Table --}}
    <div class="card overflow-hidden">
        @if($requests->isEmpty())
            <div class="p-12 text-center text-sm text-gray-400">No approval requests found.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Reference</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Summary</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Type</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-500">Amount</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">Step</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">Status</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Initiator</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Date</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($requests as $req)
                            @php
                                $statusBadge = match($req->status) {
                                    'pending', 'in_review' => 'bg-amber-100 text-amber-800',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'cancelled' => 'bg-gray-100 text-gray-500',
                                    'escalated' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-indigo-700 font-bold">{{ $req->reference }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-[200px]">
                                    <p class="truncate text-sm text-gray-800">{{ $req->summary }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-600">{{ ucwords(str_replace('_', ' ', $req->action_type)) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-700">
                                    {{ $req->amount ? '₦'.number_format($req->amount) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    {{ $req->current_step }}/{{ $req->total_steps }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $req->initiatedBy?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $req->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('hr.approvals.requests.show', $req) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
