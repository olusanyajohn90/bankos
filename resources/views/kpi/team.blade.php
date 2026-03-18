@extends('layouts.app')

@section('title', $team->name . ' — Team KPI Dashboard')

@section('content')
@php
    $severityBg = fn($s) => match($s) {
        'green'  => 'bg-emerald-100 text-emerald-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red'    => 'bg-red-100 text-red-800',
        default  => 'bg-gray-100 text-gray-500',
    };
    $periods = [];
    for ($i = 5; $i >= 0; $i--) {
        $periods[] = now()->subMonths($i)->format('Y-m');
    }
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <nav class="text-xs text-gray-400 mb-1">
                <a href="{{ route('kpi.me') }}" class="hover:text-indigo-600">Performance</a>
                <span class="mx-1">/</span>
                <span class="text-gray-600">{{ $team->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $team->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ ucfirst($team->department) }} · Lead: {{ $team->teamLead?->name ?? '—' }}
                @if($team->branch) · {{ $team->branch->name }} @endif
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="period_type" onchange="this.form.submit()" class="form-input text-sm py-1.5">
                <option value="monthly"   {{ $periodType === 'monthly'   ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ $periodType === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            </select>
            <select name="period" onchange="this.form.submit()" class="form-input text-sm py-1.5">
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ $period === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Team summary strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5 text-center">
            <div class="text-3xl font-black {{ $teamMatrix['composite_pct'] !== null ? ($teamMatrix['composite_pct'] >= 90 ? 'text-emerald-600' : ($teamMatrix['composite_pct'] >= 70 ? 'text-yellow-500' : 'text-red-600')) : 'text-gray-400' }}">
                {{ $teamMatrix['composite_pct'] !== null ? $teamMatrix['composite_pct'].'%' : '—' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Team Achievement</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-gray-800">{{ $members->count() }}</div>
            <div class="text-xs text-gray-500 mt-1">Members</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-emerald-600">{{ $teamMatrix['green_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">On Target KPIs</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-red-600">{{ $teamMatrix['red_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Below Threshold</div>
        </div>
    </div>

    {{-- Member performance matrix --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Member Performance Matrix</h2>
        @if($members->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">No members in this team.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 whitespace-nowrap">Staff Member</th>
                            @foreach($kpis->take(8) as $kpi)
                                <th class="text-center px-2 py-3 font-semibold text-gray-500 max-w-[80px]">
                                    <div class="truncate max-w-[80px]" title="{{ $kpi->name }}">{{ Str::limit($kpi->name, 12) }}</div>
                                    <div class="text-gray-400 font-normal">{{ $kpi->unit }}</div>
                                </th>
                            @endforeach
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">Overall</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($members as $profile)
                            @php
                                $memberMatrix = $matrix[$profile->id] ?? ['rows' => [], 'composite_pct' => null, 'green_count' => 0, 'yellow_count' => 0, 'red_count' => 0];
                                $composite = $memberMatrix['composite_pct'];
                                $compClass = $composite === null ? 'text-gray-400' : ($composite >= 90 ? 'text-emerald-600' : ($composite >= 70 ? 'text-yellow-600' : 'text-red-600'));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($profile->user->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 text-xs">{{ $profile->user->name ?? '—' }}</div>
                                            <div class="text-xs text-gray-400">{{ $profile->job_title }}</div>
                                        </div>
                                    </div>
                                </td>
                                @foreach($kpis->take(8) as $kpi)
                                    @php
                                        $row = $memberMatrix['rows'][$kpi->id] ?? null;
                                        $sev = $row ? $row['severity'] : 'gray';
                                        $cellBg = match($sev) {
                                            'green'  => 'bg-emerald-100 text-emerald-800',
                                            'yellow' => 'bg-yellow-100 text-yellow-800',
                                            'red'    => 'bg-red-100 text-red-800',
                                            default  => 'bg-gray-100 text-gray-400',
                                        };
                                    @endphp
                                    <td class="px-2 py-3 text-center">
                                        @if($row && $row['achievement'] !== null)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold {{ $cellBg }}">
                                                {{ number_format($row['achievement'], 0) }}%
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold {{ $compClass }}">
                                        {{ $composite !== null ? $composite.'%' : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('kpi.staff', $profile) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Leave a note --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave a Team Note</h2>
        <form method="POST" action="{{ route('kpi.notes.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="subject_type" value="team">
            <input type="hidden" name="subject_id" value="{{ $team->id }}">
            <input type="hidden" name="period_value" value="{{ $period }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <textarea name="body" rows="2" placeholder="Add a note about this team's performance…"
                              class="form-input w-full resize-none" required></textarea>
                </div>
                <div class="flex flex-col gap-2">
                    <select name="kpi_id" class="form-input text-sm">
                        <option value="">General note</option>
                        @foreach($kpis as $kpi)
                            <option value="{{ $kpi->id }}">{{ $kpi->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary text-sm">Add Note</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
