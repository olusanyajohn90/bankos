@extends('layouts.app')
@section('title', 'Visit Log')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <a href="{{ route('visitor.dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">← Visitor Management</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Visit Log</h1>
            <p class="text-sm text-gray-500 mt-0.5">All visitor check-ins and check-outs.</p>
        </div>
        <a href="{{ route('visitor.dashboard') }}" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ Check In</a>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, badge, plate…" class="form-input flex-1 min-w-[180px] text-sm">
            <select name="status" class="form-input text-sm">
                <option value="">All Statuses</option>
                @foreach(['checked_in','checked_out','expected','denied','no_show'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm">
            <button type="submit" class="btn text-sm bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">Filter</button>
            @if(request()->hasAny(['search','status','date_from','date_to']))
                <a href="{{ route('visitor.visits') }}" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Visitor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Host</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Purpose</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Check In</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Check Out</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($visits as $visit)
                @php
                    $colors = [
                        'checked_in'  => 'bg-blue-100 text-blue-700',
                        'checked_out' => 'bg-green-100 text-green-700',
                        'expected'    => 'bg-amber-100 text-amber-700',
                        'denied'      => 'bg-red-100 text-red-700',
                        'no_show'     => 'bg-gray-100 text-gray-500',
                    ];
                    $badge = $colors[$visit->status] ?? 'bg-gray-100 text-gray-500';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $visit->visitor?->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $visit->visitor?->company }} {{ $visit->badge_number ? '· Badge '.$visit->badge_number : '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $visit->host?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ ucwords(str_replace('_',' ',$visit->purpose)) }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $visit->checked_in_at ? $visit->checked_in_at->format('d M Y H:i') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        @if($visit->checked_out_at)
                            {{ $visit->checked_out_at->format('d M Y H:i') }}
                            <span class="text-gray-400">({{ $visit->duration() }})</span>
                        @else —
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ ucwords(str_replace('_',' ',$visit->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('visitor.visit-show', $visit) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            @if($visit->status === 'checked_in')
                                <form action="{{ route('visitor.check-out', $visit) }}" method="POST" onsubmit="return confirm('Check out this visitor?')">
                                    @csrf
                                    <button class="text-xs text-green-600 hover:text-green-800 font-medium">Check Out</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No visits found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($visits->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $visits->links() }}</div>
        @endif
    </div>

</div>
@endsection
