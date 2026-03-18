@extends('layouts.app')

@section('title', 'KPI Alerts')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">KPI Alerts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Performance alerts for your tracked KPIs</p>
        </div>
        <div class="flex items-center gap-2">
            @if($unreadRed + $unreadYellow > 0)
                <form method="POST" action="{{ route('kpi.alerts.mark-all-read') }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-secondary text-sm">Mark all read</button>
                </form>
            @endif
            <a href="{{ route('kpi.me') }}" class="btn btn-secondary text-sm">← My Performance</a>
        </div>
    </div>

    {{-- Alert count strip --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="card p-4 border-l-4 border-red-400">
            <div class="text-2xl font-black text-red-600">{{ $unreadRed }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Unread Red Alerts</div>
        </div>
        <div class="card p-4 border-l-4 border-yellow-400">
            <div class="text-2xl font-black text-yellow-600">{{ $unreadYellow }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Unread Yellow Alerts</div>
        </div>
        <div class="card p-4">
            <div class="text-2xl font-black text-gray-700">{{ $alerts->total() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Alerts</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex gap-3 flex-wrap">
        <select name="severity" class="form-input text-sm py-1.5">
            <option value="">All severities</option>
            <option value="red"    {{ request('severity') === 'red'    ? 'selected' : '' }}>Red</option>
            <option value="yellow" {{ request('severity') === 'yellow' ? 'selected' : '' }}>Yellow</option>
            <option value="green"  {{ request('severity') === 'green'  ? 'selected' : '' }}>Green</option>
        </select>
        <select name="status" class="form-input text-sm py-1.5">
            <option value="">All statuses</option>
            <option value="unread"    {{ request('status') === 'unread'    ? 'selected' : '' }}>Unread</option>
            <option value="read"      {{ request('status') === 'read'      ? 'selected' : '' }}>Read</option>
            <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
        </select>
        <button type="submit" class="btn btn-secondary text-sm">Filter</button>
        @if(request('severity') || request('status'))
            <a href="{{ route('kpi.alerts.index') }}" class="btn btn-secondary text-sm">Clear</a>
        @endif
    </form>

    {{-- Alerts list --}}
    @if($alerts->isEmpty())
        <div class="card p-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <p class="font-medium">No alerts found.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($alerts as $alert)
                @php
                    $kpiName = $alert->kpiTarget?->kpiDefinition?->name ?? 'KPI';
                    $bg = match($alert->severity) {
                        'red'    => 'bg-red-50 border-red-200',
                        'yellow' => 'bg-yellow-50 border-yellow-200',
                        default  => 'bg-gray-50 border-gray-200',
                    };
                    $badge = match($alert->severity) {
                        'red'    => 'bg-red-500 text-white',
                        'yellow' => 'bg-yellow-400 text-white',
                        default  => 'bg-emerald-500 text-white',
                    };
                    $unread = $alert->status === 'unread';
                @endphp
                <div class="card border {{ $bg }} p-5 {{ $unread ? 'ring-1 ring-red-300' : '' }}">
                    <div class="flex items-start gap-4 flex-wrap">
                        <span class="px-3 py-1.5 rounded-lg text-sm font-black {{ $badge }} shrink-0">
                            {{ $alert->severity === 'red' ? '⚠' : '●' }} {{ strtoupper($alert->severity) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="font-semibold text-gray-900">{{ $kpiName }}</span>
                                @if($unread)
                                    <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-medium">NEW</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $alert->period_value }}</span>
                            </div>
                            <div class="text-sm text-gray-700 mb-2">
                                Achieved <strong>{{ number_format($alert->achievement_pct, 1) }}%</strong> of target
                                &middot; Actual: <strong>{{ number_format($alert->actual_value, 2) }}</strong>
                                &middot; Target: <strong>{{ number_format($alert->target_value, 2) }}</strong>
                            </div>
                            {{-- Progress bar --}}
                            <div class="w-full max-w-sm bg-gray-200 rounded-full h-1.5">
                                @php $barW = min($alert->achievement_pct, 100); @endphp
                                <div class="h-1.5 rounded-full" style="width:{{ $barW }}%;background:{{ $alert->severity === 'red' ? '#ef4444' : ($alert->severity === 'yellow' ? '#eab308' : '#10b981') }}"></div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 items-end shrink-0">
                            <span class="text-xs text-gray-400">{{ $alert->created_at->format('d M Y, H:i') }}</span>
                            <div class="flex gap-2">
                                @if($alert->status === 'unread')
                                    <form method="POST" action="{{ route('kpi.alerts.read', $alert) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Mark read</button>
                                    </form>
                                @endif
                                @if($alert->status !== 'dismissed')
                                    <form method="POST" action="{{ route('kpi.alerts.dismiss', $alert) }}">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-gray-400 hover:text-gray-600">Dismiss</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-300">Dismissed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $alerts->links() }}</div>
    @endif
</div>
@endsection
