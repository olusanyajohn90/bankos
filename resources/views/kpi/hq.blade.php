@extends('layouts.app')

@section('title', 'HQ KPI Overview')

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
    $redCount    = $alertSummary['red']    ?? 0;
    $yellowCount = $alertSummary['yellow'] ?? 0;
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">HQ Performance Overview</h1>
            <p class="text-sm text-gray-500 mt-0.5">All branches · {{ $periodType }} · {{ $period }}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('kpi.compute') }}" class="inline">
                @csrf
                <input type="hidden" name="period_type" value="{{ $periodType }}">
                <input type="hidden" name="period" value="{{ $period }}">
                <button class="btn btn-secondary text-sm">↻ Refresh KPIs</button>
            </form>
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
    </div>

    {{-- Alert summary strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5 border-l-4 border-red-400">
            <div class="text-3xl font-black text-red-600">{{ $redCount }}</div>
            <div class="text-xs text-gray-500 mt-1">Active Red Alerts</div>
        </div>
        <div class="card p-5 border-l-4 border-yellow-400">
            <div class="text-3xl font-black text-yellow-600">{{ $yellowCount }}</div>
            <div class="text-xs text-gray-500 mt-1">Yellow Alerts</div>
        </div>
        <div class="card p-5">
            <div class="text-3xl font-black text-gray-800">{{ $branches->count() }}</div>
            <div class="text-xs text-gray-500 mt-1">Branches</div>
        </div>
        <div class="card p-5 flex items-center gap-3">
            <div>
                <a href="{{ route('kpi.alerts.index') }}" class="btn btn-secondary text-xs">View All Alerts →</a>
                <div class="text-xs text-gray-400 mt-1">Manage alerts</div>
            </div>
        </div>
    </div>

    {{-- Branch comparison table --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Branch Performance Comparison</h2>
        @if($branches->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No branches found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-500">Branch</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">Overall</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">On Target</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">At Risk</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-500">Below</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($branches as $branch)
                            @php
                                $bData = $branchData[$branch->id] ?? null;
                                $mat   = $bData ? $bData['matrix'] : null;
                                $comp  = $mat ? $mat['composite_pct'] : null;
                                $compClass = $comp === null ? 'text-gray-400' : ($comp >= 90 ? 'text-emerald-600' : ($comp >= 70 ? 'text-yellow-600' : 'text-red-600'));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $branch->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $branch->manager?->name ?? 'No manager' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($comp !== null)
                                        <div class="inline-flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full" style="width:{{ min($comp,100) }}%;background:{{ $comp >= 90 ? '#10b981' : ($comp >= 70 ? '#eab308' : '#ef4444') }}"></div>
                                            </div>
                                            <span class="font-bold {{ $compClass }}">{{ $comp }}%</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-emerald-600">{{ $mat ? $mat['green_count'] : '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-yellow-600">{{ $mat ? $mat['yellow_count'] : '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-red-600">{{ $mat ? $mat['red_count'] : '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('kpi.branch', $branch) }}?period={{ $period }}&period_type={{ $periodType }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">
                                        View Branch →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Top & Bottom Performers --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Top 5 --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🏆 Top Performers</h2>
            @if(empty($top5))
                <p class="text-sm text-gray-400 text-center py-4">No data available.</p>
            @else
                <div class="space-y-3">
                    @foreach($top5 as $profileId => $data)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($data['profile']->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('kpi.staff', $data['profile']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $data['profile']->user->name ?? '—' }}</a>
                                <div class="text-xs text-gray-400">{{ $data['profile']->branch?->name }}</div>
                            </div>
                            <div class="w-20 bg-gray-200 rounded-full h-2 shrink-0">
                                <div class="h-2 rounded-full bg-emerald-500" style="width:{{ min($data['composite'],100) }}%"></div>
                            </div>
                            <span class="text-sm font-black text-emerald-600 w-12 text-right shrink-0">{{ number_format($data['composite'],1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bottom 5 --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">⚠ Needs Attention</h2>
            @if(empty($bottom5))
                <p class="text-sm text-gray-400 text-center py-4">No data available.</p>
            @else
                <div class="space-y-3">
                    @foreach($bottom5 as $profileId => $data)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($data['profile']->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('kpi.staff', $data['profile']) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 truncate block">{{ $data['profile']->user->name ?? '—' }}</a>
                                <div class="text-xs text-gray-400">{{ $data['profile']->branch?->name }}</div>
                            </div>
                            <div class="w-20 bg-gray-200 rounded-full h-2 shrink-0">
                                <div class="h-2 rounded-full bg-red-500" style="width:{{ min($data['composite'],100) }}%"></div>
                            </div>
                            <span class="text-sm font-black text-red-600 w-12 text-right shrink-0">{{ number_format($data['composite'],1) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
