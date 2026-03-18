@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ showMark: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Attendance</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($startDate)->format('F Y') }}</p>
        </div>
        <div class="flex gap-2">
            <button @click="showMark = !showMark" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Mark Attendance</button>
            <a href="{{ route('hr.attendance.export', ['month' => $month, 'branch_id' => $branchId]) }}"
               class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Export CSV</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Mark single attendance --}}
    <div x-show="showMark" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Mark Attendance</h2>
        <form action="{{ route('hr.attendance.mark') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Staff *</label>
                <select name="staff_profile_id" required class="form-input w-full text-sm">
                    <option value="">—</option>
                    @foreach(array_values($summary) as $s)
                        <option value="{{ $s['profile']->id }}">{{ $s['profile']->user?->name }} ({{ $s['profile']->staff_code }})</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Date *</label>
                <input type="date" name="date" value="{{ now()->toDateString() }}" required class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Status *</label>
                <select name="status" required class="form-input w-full text-sm">
                    @foreach(['present','absent','late','half_day','excused','on_leave','public_holiday'] as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select></div>
            <div><label class="block text-xs text-gray-500 mb-1">Clock In</label>
                <input type="time" name="clock_in" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Clock Out</label>
                <input type="time" name="clock_out" class="form-input w-full text-sm"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Notes</label>
                <input type="text" name="notes" class="form-input w-full text-sm"></div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save</button>
                <button type="button" @click="showMark = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="month" name="month" value="{{ $month }}" class="form-input text-sm">
        <select name="branch_id" class="form-input text-sm">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Filter</button>
    </form>

    {{-- Summary table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Staff</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-green-600 uppercase">Present</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-amber-500 uppercase">Late</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-red-500 uppercase">Absent</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-blue-500 uppercase">On Leave</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hours</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($summary as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $s['profile']->user?->name }}</p>
                            <p class="text-xs text-gray-400">{{ $s['profile']->staff_code }} · {{ $s['profile']->branch?->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-green-700">{{ $s['present'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ $s['late'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-red-600">{{ $s['absent'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-blue-600">{{ $s['on_leave'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $s['total_hours'] }}h</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('hr.attendance.staff', [$s['profile'], 'month' => $month]) }}"
                               class="text-xs text-blue-600 hover:underline">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No staff records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
