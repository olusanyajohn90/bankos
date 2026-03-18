@extends('layouts.app')

@section('title', $branch->name . ' — Branch KPI Dashboard')

@section('content')
@php
    $comp      = $branchMatrix['composite_pct'];
    $compColor = $comp === null ? '#9ca3af' : ($comp >= 90 ? '#10b981' : ($comp >= 70 ? '#eab308' : '#ef4444'));
    $periods   = [];
    for ($i = 5; $i >= 0; $i--) {
        $periods[] = now()->subMonths($i)->format('Y-m');
    }
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <nav class="text-xs text-gray-400 mb-1">
                <a href="{{ route('kpi.hq') }}" class="hover:text-indigo-600">HQ Overview</a>
                <span class="mx-1">/</span>
                <span class="text-gray-600">{{ $branch->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $branch->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Branch Manager: {{ $branch->manager?->name ?? '—' }}</p>
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

    {{-- Branch summary strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5 text-center">
            <div class="text-3xl font-black" style="color:{{ $compColor }}">
                {{ $comp !== null ? $comp.'%' : '—' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Branch Achievement</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-gray-800">{{ $staff->count() }}</div>
            <div class="text-xs text-gray-500 mt-1">Active Staff</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-gray-800">{{ $teams->count() }}</div>
            <div class="text-xs text-gray-500 mt-1">Teams</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-red-600">{{ $branchMatrix['red_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Below Threshold</div>
        </div>
    </div>

    {{-- Teams table --}}
    @if($teams->isNotEmpty())
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Teams</h2>
        <div class="space-y-2">
            @foreach($teams as $team)
                <div class="flex items-center gap-4 rounded-lg border border-gray-200 bg-gray-50/50 p-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr($team->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">{{ $team->name }}</div>
                        <div class="text-xs text-gray-400">{{ ucfirst($team->department) }} · Lead: {{ $team->teamLead?->name ?? '—' }}</div>
                    </div>
                    <a href="{{ route('kpi.team', $team) }}?period={{ $period }}&period_type={{ $periodType }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium shrink-0">View →</a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Staff leaderboard --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🏆 Top 5 Performers</h2>
            @if(empty($top5))
                <p class="text-sm text-gray-400 text-center py-4">No data yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($top5 as $uid => $data)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($data['profile']->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('kpi.staff', $data['profile']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $data['profile']->user->name ?? '—' }}</a>
                                <div class="text-xs text-gray-400">{{ $data['profile']->job_title }}</div>
                            </div>
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 shrink-0">
                                <div class="h-1.5 rounded-full bg-emerald-500" style="width:{{ min($data['composite'],100) }}%"></div>
                            </div>
                            <span class="text-sm font-black text-emerald-600 shrink-0 w-12 text-right">{{ number_format($data['composite'],1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">⚠ Needs Attention</h2>
            @if(empty($bottom5))
                <p class="text-sm text-gray-400 text-center py-4">No data yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($bottom5 as $uid => $data)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($data['profile']->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('kpi.staff', $data['profile']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $data['profile']->user->name ?? '—' }}</a>
                                <div class="text-xs text-gray-400">{{ $data['profile']->job_title }}</div>
                            </div>
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 shrink-0">
                                <div class="h-1.5 rounded-full bg-red-500" style="width:{{ min($data['composite'],100) }}%"></div>
                            </div>
                            <span class="text-sm font-black text-red-600 shrink-0 w-12 text-right">{{ number_format($data['composite'],1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Staff heatmap --}}
    @if($staffScores)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Staff Performance Heatmap</h2>
        <div class="overflow-x-auto">
            <div class="grid gap-2" style="grid-template-columns: 1fr repeat(3, auto);">
                <div class="text-xs font-semibold text-gray-500 pb-1">Staff</div>
                <div class="text-xs font-semibold text-gray-500 pb-1 text-center">Green</div>
                <div class="text-xs font-semibold text-gray-500 pb-1 text-center">Yellow</div>
                <div class="text-xs font-semibold text-gray-500 pb-1 text-center">Red</div>
                @foreach($staffScores as $uid => $data)
                    <div class="text-sm text-gray-800 py-1">
                        <a href="{{ route('kpi.staff', $data['profile']) }}" class="hover:text-indigo-600">{{ $data['profile']->user->name ?? '—' }}</a>
                    </div>
                    <div class="text-center">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                            {{ $data['matrix']['green_count'] }}
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-800">
                            {{ $data['matrix']['yellow_count'] }}
                        </span>
                    </div>
                    <div class="text-center">
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                            {{ $data['matrix']['red_count'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
