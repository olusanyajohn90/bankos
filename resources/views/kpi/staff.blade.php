@extends('layouts.app')

@section('title', ($staffProfile->user->name ?? 'Staff') . ' — KPI Dashboard')

@section('content')
@php
    $composite = $kpiMatrix['composite_pct'];
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

    $periods = [];
    for ($i = 5; $i >= 0; $i--) {
        $periods[] = now()->subMonths($i)->format('Y-m');
    }
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <nav class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                <a href="{{ route('kpi.hq') }}" class="hover:text-indigo-600">Performance</a>
                <span>/</span>
                @if($staffProfile->branch)
                    <a href="{{ route('kpi.branch', $staffProfile->branch) }}" class="hover:text-indigo-600">
                        {{ $staffProfile->branch->name }}
                    </a>
                    <span>/</span>
                @endif
                @if($staffProfile->team)
                    <a href="{{ route('kpi.team', $staffProfile->team) }}" class="hover:text-indigo-600">
                        {{ $staffProfile->team->name }}
                    </a>
                    <span>/</span>
                @endif
                <span class="text-gray-600">{{ $staffProfile->user->name ?? '—' }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $staffProfile->user->name ?? '—' }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $staffProfile->job_title ?? '—' }}
                @if($staffProfile->orgDepartment) · {{ $staffProfile->orgDepartment->name }} @endif
                @if($staffProfile->branch) · {{ $staffProfile->branch->name }} @endif
                @if($staffProfile->employee_number)
                    <span class="ml-2 text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $staffProfile->employee_number }}</span>
                @endif
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

    {{-- KPI Breakdown --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">KPI Breakdown — {{ $period }}</h2>

        @if(empty($kpiMatrix['rows']) || collect($kpiMatrix['rows'])->every(fn($r) => $r['actual'] === null))
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
                                    {{ ucwords(str_replace('_', ' ', $row['kpi']->category)) }}
                                    &middot; {{ $row['kpi']->unit }}
                                    @if($row['kpi']->direction === 'lower_better')
                                        <span class="text-blue-500">(lower is better)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if($row['actual'] !== null)
                                    <span class="text-sm font-bold text-gray-800">
                                        @if(in_array($row['kpi']->unit, ['currency', 'ngn']))
                                            ₦{{ number_format($row['actual'], 0) }}
                                            @if($row['target'] !== null)
                                                <span class="text-gray-400 font-normal text-xs">/ ₦{{ number_format($row['target'], 0) }}</span>
                                            @endif
                                        @else
                                            {{ number_format($row['actual'], 1) }}
                                            @if($row['target'] !== null)
                                                <span class="text-gray-400 font-normal text-xs">/ {{ number_format($row['target'], 1) }}</span>
                                            @endif
                                        @endif
                                    </span>
                                @endif
                                @if($pct !== null)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge }}">
                                        {{ number_format($pct, 1) }}%
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">No target</span>
                                @endif
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-500"
                                 style="width:{{ $barW }}%;background:{{ $color }}"></div>
                        </div>
                        @if($row['target'] !== null)
                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>0</span>
                                <span>Target: {{ in_array($row['kpi']->unit, ['currency', 'ngn']) ? '₦'.number_format($row['target'],0) : number_format($row['target'],1) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Manager Notes + Leave a Note --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Manager Notes</h2>
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

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Leave a Note</h2>
            <form method="POST" action="{{ route('kpi.notes.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="subject_type" value="staff_profile">
                <input type="hidden" name="subject_id" value="{{ $staffProfile->id }}">
                <input type="hidden" name="period_value" value="{{ $period }}">
                <textarea name="body" rows="3" placeholder="Add a performance note for {{ $staffProfile->user->name ?? 'this staff member' }}…"
                          class="form-input w-full resize-none" required></textarea>
                <div class="flex items-center gap-3">
                    <select name="kpi_id" class="form-input text-sm flex-1">
                        <option value="">General note</option>
                        @foreach($kpis as $kpi)
                            <option value="{{ $kpi->id }}">{{ $kpi->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary text-sm whitespace-nowrap">Add Note</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
