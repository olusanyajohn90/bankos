@extends('layouts.app')
@section('title', 'Attendance — ' . $staffProfile->user?->name)
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <a href="{{ route('hr.attendance.index', ['month' => $month]) }}" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 mb-2">← Back to Attendance</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $staffProfile->user?->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $staffProfile->staff_code }} · {{ $staffProfile->job_title }} · {{ $staffProfile->branch?->name }}
            · {{ \Carbon\Carbon::parse($startDate)->format('F Y') }}
        </p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Month nav --}}
    <form method="GET" class="flex gap-3">
        <input type="month" name="month" value="{{ $month }}" class="form-input text-sm">
        <button type="submit" class="btn text-sm bg-gray-700 text-white px-4 py-2 rounded-lg">Go</button>
    </form>

    {{-- Calendar-style day grid --}}
    @php
        $start = \Carbon\Carbon::parse($startDate);
        $end   = \Carbon\Carbon::parse($endDate);
        $statusColors = [
            'present'        => 'bg-green-100 text-green-800',
            'late'           => 'bg-amber-100 text-amber-700',
            'absent'         => 'bg-red-100 text-red-700',
            'half_day'       => 'bg-yellow-100 text-yellow-700',
            'excused'        => 'bg-blue-100 text-blue-700',
            'on_leave'       => 'bg-purple-100 text-purple-700',
            'public_holiday' => 'bg-gray-100 text-gray-500',
            'weekend'        => 'bg-gray-50 text-gray-400',
        ];
        $totalPresent = collect($records)->whereIn('status', ['present','late'])->count();
        $totalLate    = collect($records)->where('status', 'late')->count();
        $totalAbsent  = collect($records)->where('status', 'absent')->count();
        $totalHours   = round(collect($records)->sum('hours_worked'), 1);
    @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="card p-4 text-center"><p class="text-xl font-bold text-green-600">{{ $totalPresent }}</p><p class="text-xs text-gray-500">Present</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-amber-500">{{ $totalLate }}</p><p class="text-xs text-gray-500">Late</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-red-500">{{ $totalAbsent }}</p><p class="text-xs text-gray-500">Absent</p></div>
        <div class="card p-4 text-center"><p class="text-xl font-bold text-blue-600">{{ $totalHours }}h</p><p class="text-xs text-gray-500">Total Hours</p></div>
    </div>

    {{-- Day list --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Day</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Clock In</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Clock Out</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hours</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Late (min)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @for($d = $start->copy(); $d->lte($end); $d->addDay())
                    @php
                        $key    = $d->format('Y-m-d');
                        $rec    = $records->get($key);
                        $isWeekend = $d->isWeekend();
                        $status = $rec?->status ?? ($isWeekend ? 'weekend' : '—');
                        $badge  = $statusColors[$status] ?? 'bg-gray-50 text-gray-400';
                    @endphp
                    <tr class="{{ $isWeekend ? 'bg-gray-50/50' : 'hover:bg-gray-50' }}">
                        <td class="px-4 py-2.5">
                            <span class="font-medium text-gray-800">{{ $d->format('d') }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $d->format('D') }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            @if($status !== '—')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucwords(str_replace('_',' ',$status)) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ $rec?->clock_in ? substr($rec->clock_in, 0, 5) : '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ $rec?->clock_out ? substr($rec->clock_out, 0, 5) : '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-gray-600">{{ $rec?->hours_worked ? round($rec->hours_worked, 1) . 'h' : '—' }}</td>
                        <td class="px-4 py-2.5 text-center {{ $rec?->minutes_late > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                            {{ $rec?->minutes_late > 0 ? $rec->minutes_late : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $rec?->notes ?? '' }}</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

</div>
@endsection
