@extends('layouts.app')

@section('title', 'My Performance')

@section('content')
@php
    $composite = $kpiMatrix['composite_pct'];
    $compositeColor = $composite === null ? 'gray'
        : ($composite >= 90 ? 'emerald' : ($composite >= 70 ? 'yellow' : 'red'));
    $compositeHex = $composite === null ? '#9ca3af'
        : ($composite >= 90 ? '#10b981' : ($composite >= 70 ? '#eab308' : '#ef4444'));

    $severityBg = fn($s) => match($s) {
        'green'  => 'bg-emerald-100 text-emerald-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red'    => 'bg-red-100 text-red-800',
        default  => 'bg-gray-100 text-gray-500',
    };
    $severityBar = fn($s) => match($s) {
        'green'  => '#10b981', 'yellow' => '#eab308', 'red' => '#ef4444', default => '#d1d5db',
    };

    // Period options for selector
    $periods = [];
    for ($i = 5; $i >= 0; $i--) {
        $periods[] = now()->subMonths($i)->format('Y-m');
    }
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Performance</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $profile?->job_title ?? 'Staff' }}
                @if($profile?->branch) · {{ $profile->branch->name }} @endif
                @if($profile?->team) · {{ $profile->team->name }} @endif
            </p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <select name="period_type" onchange="this.form.submit()" class="form-input text-sm py-1.5">
                <option value="monthly"   {{ $periodType === 'monthly'   ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ $periodType === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="yearly"    {{ $periodType === 'yearly'    ? 'selected' : '' }}>Yearly</option>
            </select>
            <select name="period" onchange="this.form.submit()" class="form-input text-sm py-1.5">
                @foreach($periods as $p)
                    <option value="{{ $p }}" {{ $period === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5 text-center">
            <div class="text-3xl font-black" style="color:{{ $compositeHex }}">
                {{ $composite !== null ? $composite . '%' : '—' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Overall Achievement</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-emerald-600">{{ $kpiMatrix['green_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">On Target (≥90%)</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-yellow-500">{{ $kpiMatrix['yellow_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">At Risk</div>
        </div>
        <div class="card p-5 text-center">
            <div class="text-3xl font-black text-red-600">{{ $kpiMatrix['red_count'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Below Threshold</div>
        </div>
    </div>

    {{-- KPI grid --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">KPI Breakdown — {{ $period }}</h2>

        @if(empty($kpiMatrix['rows']))
            <p class="text-sm text-gray-400 py-8 text-center">No KPI data available for this period.</p>
        @else
            <div class="space-y-3">
                @foreach($kpiMatrix['rows'] as $row)
                    @php
                        $pct   = $row['achievement'];
                        $color = $severityBar($row['severity']);
                        $badge = $severityBg($row['severity']);
                        $barW  = $pct !== null ? min($pct, 100) : 0;
                    @endphp
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                        <div class="flex items-start justify-between gap-4 flex-wrap mb-2">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 text-sm">{{ $row['kpi']->name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $row['kpi']->category_label }}
                                    &middot; {{ $row['kpi']->unit }}
                                    @if($row['kpi']->direction === 'lower_better')
                                        <span class="text-blue-500">(lower is better)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if($row['actual'] !== null)
                                    <span class="text-sm font-bold text-gray-800">
                                        {{ $row['kpi']->unit === 'ngn' ? '₦'.number_format($row['actual'],0) : number_format($row['actual'],1) }}
                                        @if($row['target'] !== null)
                                            <span class="text-gray-400 font-normal text-xs">
                                                / {{ $row['kpi']->unit === 'ngn' ? '₦'.number_format($row['target'],0) : number_format($row['target'],1) }}
                                            </span>
                                        @endif
                                    </span>
                                @endif
                                @if($pct !== null)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge }}">
                                        {{ number_format($pct,1) }}%
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">No target</span>
                                @endif
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-500"
                                 style="width:{{ $barW }}%;background:{{ $color }}"></div>
                        </div>
                        @if($row['target'] !== null)
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0</span>
                                <span>Target: {{ $row['kpi']->unit === 'ngn' ? '₦'.number_format($row['target'],0) : number_format($row['target'],1) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Trend chart --}}
    @if(!empty($trends['periods']))
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Achievement Trend (Last 6 Months)</h2>
        <canvas id="trendChart" height="80"></canvas>
    </div>
    @endif

    {{-- Alerts & Notes --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Alerts --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Alerts</h2>
                <a href="{{ route('kpi.alerts.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">View all →</a>
            </div>
            @if($alerts->isEmpty())
                <p class="text-sm text-gray-400 py-4 text-center">No recent alerts.</p>
            @else
                <div class="space-y-2">
                    @foreach($alerts as $alert)
                        @php
                            $alertBadge = match($alert->severity) {
                                'red'    => 'bg-red-100 text-red-700',
                                'yellow' => 'bg-yellow-100 text-yellow-700',
                                default  => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <div class="flex items-start gap-3 rounded-lg {{ $alert->status === 'unread' ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }} p-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $alertBadge }} shrink-0 mt-0.5">
                                {{ strtoupper($alert->severity) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm text-gray-800 font-medium">{{ $alert->kpiTarget?->kpiDefinition?->name ?? 'KPI' }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ number_format($alert->achievement_pct, 1) }}% achieved · {{ $alert->period_value }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Notes from manager --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Manager Notes</h2>
            </div>
            @if($notes->isEmpty())
                <p class="text-sm text-gray-400 py-4 text-center">No notes yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($notes as $note)
                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-gray-700">{{ $note->author?->name ?? '—' }}</span>
                                @if($note->kpiDefinition)
                                    <span class="text-xs text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">{{ $note->kpiDefinition->name }}</span>
                                @endif
                                <span class="text-xs text-gray-400 ml-auto">{{ $note->created_at->format('d M Y') }}</span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $note->body }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
    const periods = @json($trends['periods'] ?? []);
    // Build composite trend from matrix rows
    const ctx = document.getElementById('trendChart');
    if (ctx && periods.length) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: periods,
                datasets: [{
                    label: 'Overall Achievement %',
                    data: periods.map(() => null), // placeholder — would be server-computed
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#6366f1',
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush
@endsection
