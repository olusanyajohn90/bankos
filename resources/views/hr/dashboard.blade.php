@extends('layouts.app')
@section('title', 'HR Dashboard')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">HR Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hr.leave.requests.index') }}" class="btn text-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Leave Requests</a>
            <a href="{{ route('hr.approvals.requests') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Approval Inbox @if($myPendingApprovals > 0)<span class="ml-1 bg-white text-blue-700 text-xs font-bold rounded-full px-1.5">{{ $myPendingApprovals }}</span>@endif
            </a>
        </div>
    </div>

    {{-- Headcount row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Total Active Staff</p>
            <p class="text-3xl font-bold text-gray-900">{{ $totalStaff }}</p>
            @if($newThisMonth > 0)
                <p class="text-xs text-green-600 mt-1">+{{ $newThisMonth }} joined this month</p>
            @endif
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Present Today</p>
            <p class="text-3xl font-bold text-green-600">{{ $presentToday }}</p>
            @if($lateToday > 0)<p class="text-xs text-amber-600 mt-1">{{ $lateToday }} arrived late</p>@endif
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">On Leave Today</p>
            <p class="text-3xl font-bold text-blue-600">{{ $onLeaveNow }}</p>
            @if($absentToday > 0)<p class="text-xs text-red-500 mt-1">{{ $absentToday }} absent</p>@endif
        </div>
        <div class="card p-5">
            <p class="text-xs text-gray-400 uppercase font-semibold mb-1">Pending Approvals</p>
            <p class="text-3xl font-bold {{ $myPendingApprovals > 0 ? 'text-amber-500' : 'text-gray-400' }}">{{ $myPendingApprovals }}</p>
            <p class="text-xs text-gray-400 mt-1">Waiting on you</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column --}}
        <div class="space-y-4">

            {{-- Employment type breakdown --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Staff Breakdown</h2>
                <div class="space-y-2">
                    @forelse($byEmploymentType as $type => $count)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ ucwords(str_replace('_',' ',$type ?? 'Unknown')) }}</span>
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width:{{ $totalStaff > 0 ? round($count/$totalStaff*100) : 0 }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 w-6 text-right">{{ $count }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No staff data.</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('hr.org.index') }}" class="text-xs text-blue-600 hover:underline">View Org Structure →</a>
                </div>
            </div>

            {{-- Pending actions --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Pending Actions</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-2.5 rounded-lg {{ $pendingLeave > 0 ? 'bg-amber-50' : 'bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🏖️</span>
                            <span class="text-sm text-gray-700">Leave Requests</span>
                        </div>
                        <a href="{{ route('hr.leave.requests.index') }}" class="text-sm font-bold {{ $pendingLeave > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $pendingLeave }}</a>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg {{ $pendingExpenses > 0 ? 'bg-amber-50' : 'bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-base">💳</span>
                            <span class="text-sm text-gray-700">Expense Claims</span>
                        </div>
                        <a href="{{ route('hr.expense-claims.index') }}" class="text-sm font-bold {{ $pendingExpenses > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $pendingExpenses }}</a>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg {{ $pendingAdvances > 0 ? 'bg-amber-50' : 'bg-gray-50' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-base">💰</span>
                            <span class="text-sm text-gray-700">Salary Advances</span>
                        </div>
                        <a href="{{ route('hr.salary-advances.index') }}" class="text-sm font-bold {{ $pendingAdvances > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $pendingAdvances }}</a>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <span class="text-sm text-gray-700">Approval Inbox</span>
                        </div>
                        <a href="{{ route('hr.approvals.requests') }}" class="text-sm font-bold {{ $myPendingApprovals > 0 ? 'text-blue-600' : 'text-gray-400' }}">{{ $myPendingApprovals }}</a>
                    </div>
                </div>
            </div>

            {{-- Upcoming holidays --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Upcoming Holidays</h2>
                    <a href="{{ route('hr.holidays.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                @forelse($upcomingHolidays as $h)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $h->name }}</p>
                            <p class="text-xs text-gray-400">{{ $h->date->format('l, d M Y') }}</p>
                        </div>
                        @php $days = now()->diffInDays($h->date, false); @endphp
                        <span class="text-xs font-semibold {{ $days <= 7 ? 'text-amber-600' : 'text-gray-400' }}">
                            {{ $days === 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : 'in ' . $days . 'd') }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-3">No holidays in the next 30 days.</p>
                @endforelse
            </div>
        </div>

        {{-- Middle column --}}
        <div class="space-y-4">

            {{-- Payroll status --}}
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Latest Payroll Run</h2>
                @if($latestPayroll)
                    @php
                        $pc = match($latestPayroll->status) {
                            'draft'     => 'bg-gray-100 text-gray-500',
                            'processing'=> 'bg-amber-100 text-amber-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'paid'      => 'bg-purple-100 text-purple-700',
                            default     => 'bg-gray-100 text-gray-400',
                        };
                    @endphp
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $latestPayroll->name ?? $latestPayroll->period_label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $latestPayroll->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $pc }}">{{ ucfirst($latestPayroll->status) }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400">Net Pay</p>
                            <p class="font-bold text-gray-900">₦{{ number_format($latestPayroll->total_net_pay ?? 0) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs text-gray-400">Employees</p>
                            <p class="font-bold text-gray-900">{{ $latestPayroll->employee_count ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('payroll.runs.index') }}" class="text-xs text-blue-600 hover:underline">View payroll runs →</a>
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">No payroll runs yet.</p>
                    <a href="{{ route('payroll.runs.index') }}" class="block text-center text-xs text-blue-600 hover:underline mt-2">Start first payroll run →</a>
                @endif
            </div>

            {{-- Attendance today summary --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Attendance Today</h2>
                    <a href="{{ route('hr.attendance.index') }}" class="text-xs text-blue-600 hover:underline">Full report</a>
                </div>
                @php $marked = $presentToday + $lateToday + $absentToday + $onLeaveToday; @endphp
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $presentToday }}</p>
                        <p class="text-xs text-gray-500">Present</p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-amber-500">{{ $lateToday }}</p>
                        <p class="text-xs text-gray-500">Late</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-red-500">{{ $absentToday }}</p>
                        <p class="text-xs text-gray-500">Absent</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-blue-500">{{ $onLeaveToday }}</p>
                        <p class="text-xs text-gray-500">On Leave</p>
                    </div>
                </div>
                @if($totalStaff > 0)
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-400 mb-1">
                            <span>Attendance rate</span>
                            <span>{{ $totalStaff > 0 ? round(($presentToday + $lateToday) / $totalStaff * 100) : 0 }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width:{{ $totalStaff > 0 ? round(($presentToday + $lateToday) / $totalStaff * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Expense summary --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-700">Expense Claims</h2>
                    <a href="{{ route('hr.expense-claims.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-amber-50 rounded-lg p-3 text-center">
                        <p class="text-xl font-bold text-amber-600">{{ $pendingExpenses }}</p>
                        <p class="text-xs text-gray-500">Pending Review</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <p class="text-xl font-bold text-blue-600">₦{{ number_format($expenseAmount / 1000, 0) }}k</p>
                        <p class="text-xs text-gray-500">Outstanding</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4">

            {{-- Announcements --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Notice Board</h2>
                    <a href="{{ route('hr.announcements.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentAnnouncements as $a)
                        @php
                            $pb = match($a->priority) {
                                'urgent' => 'bg-red-100 text-red-700',
                                'high'   => 'bg-amber-100 text-amber-700',
                                default  => 'bg-blue-100 text-blue-700',
                            };
                        @endphp
                        <div class="p-3 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-1.5 mb-1">
                                @if($a->is_pinned)<span class="text-amber-500 text-xs">📌</span>@endif
                                <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $pb }}">{{ ucfirst($a->priority) }}</span>
                            </div>
                            <p class="text-sm font-medium text-gray-800">{{ $a->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $a->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-3">No announcements.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent leave requests --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-700">Recent Leave Requests</h2>
                    <a href="{{ route('hr.leave.requests.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
                </div>
                <div class="space-y-2">
                    @forelse($recentLeave as $lr)
                        @php
                            $lc = match($lr->status) {
                                'pending'  => 'bg-amber-100 text-amber-700',
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-600',
                                default    => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $lr->staffProfile?->user?->name }}</p>
                                <p class="text-xs text-gray-400">{{ $lr->leaveType?->name }} · {{ $lr->start_date?->format('d M') }}–{{ $lr->end_date?->format('d M') }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $lc }}">{{ ucfirst($lr->status) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-3">No leave requests.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
