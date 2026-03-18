@extends('layouts.app')

@section('title', 'KPI Setup — Targets')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">KPI Setup</h1>
            <p class="text-sm text-gray-500 mt-0.5">Set performance targets per staff, team, or branch</p>
        </div>
    </div>

    @include('kpi.setup._tabs', ['active' => 'targets'])

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Period selector --}}
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <label class="text-sm font-medium text-gray-700">Period:</label>
        <select name="period_type" onchange="this.form.submit()" class="form-input text-sm py-1.5">
            <option value="monthly"   {{ $periodType === 'monthly'   ? 'selected' : '' }}>Monthly</option>
            <option value="quarterly" {{ $periodType === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
            <option value="yearly"    {{ $periodType === 'yearly'    ? 'selected' : '' }}>Yearly</option>
        </select>
        <input type="text" name="period" value="{{ $period }}" class="form-input text-sm py-1.5 w-32" placeholder="2025-03">
        <button type="submit" class="btn btn-secondary text-sm">Load</button>
    </form>

    {{-- Add target form --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Set Target</h2>
        <form method="POST" action="{{ route('kpi.targets.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">KPI <span class="text-red-500">*</span></label>
                <select name="kpi_id" class="form-input w-full" required>
                    @foreach($kpis as $kpi)
                        <option value="{{ $kpi->id }}">{{ $kpi->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Target Type <span class="text-red-500">*</span></label>
                <select name="target_type" id="targetTypeSelect" class="form-input w-full" required
                        onchange="updateTargetRef(this.value)">
                    <option value="individual">Individual (Staff)</option>
                    <option value="team">Team</option>
                    <option value="branch">Branch</option>
                    <option value="tenant">Tenant-wide</option>
                </select>
            </div>
            <div id="targetRefWrap">
                <label class="block text-xs font-semibold text-gray-500 mb-1">For (Staff/Team/Branch)</label>
                <select name="target_ref_id" id="targetRefSelect" class="form-input w-full">
                    <option value="">— Select Staff —</option>
                    @foreach($staff as $sp)
                        <option value="{{ $sp->user_id }}" data-type="individual">{{ $sp->user->name ?? '—' }}</option>
                    @endforeach
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" data-type="team">{{ $team->name }}</option>
                    @endforeach
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" data-type="branch">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Period Type</label>
                <select name="period_type" class="form-input w-full">
                    <option value="monthly"   {{ $periodType === 'monthly'   ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ $periodType === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="yearly"    {{ $periodType === 'yearly'    ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Period Value</label>
                <input type="text" name="period_value" value="{{ $period }}" class="form-input w-full" placeholder="2025-03">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Target Value <span class="text-red-500">*</span></label>
                <input type="number" name="target_value" step="0.01" min="0" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Alert Threshold (%)</label>
                <input type="number" name="alert_threshold_pct" value="70" min="1" max="99" class="form-input w-full">
                <p class="text-xs text-gray-400 mt-0.5">Alert fires when actual < this % of target</p>
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="btn btn-primary">Save Target</button>
            </div>
        </form>
    </div>

    {{-- Existing targets --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Current Targets — {{ $period }}</h2>
            <span class="text-xs text-gray-400">{{ $targets->total() }} target(s)</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">KPI</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500">Scope</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-500">Target</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Alert %</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-500">Achievement</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($targets as $target)
                    @php $pct = $target->achievement_pct; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $target->kpiDefinition?->name ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $target->period_type }} · {{ $target->period_value }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            <span class="capitalize">{{ $target->target_type }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-800">
                            {{ number_format($target->target_value, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $target->alert_threshold_pct }}%</td>
                        <td class="px-4 py-3 text-center">
                            @if($pct !== null)
                                @php
                                    $sev = $target->severity;
                                    $cls = match($sev) { 'green' => 'bg-emerald-100 text-emerald-800', 'yellow' => 'bg-yellow-100 text-yellow-800', 'red' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-500' };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $cls }}">{{ number_format($pct,1) }}%</span>
                            @else
                                <span class="text-xs text-gray-400">No actual yet</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('kpi.targets.destroy', $target) }}"
                                  onsubmit="return confirm('Remove this target?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            No targets set for {{ $period }}. Use the form above to add targets.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $targets->links() }}</div>
</div>

<script>
    function updateTargetRef(type) {
        const sel = document.getElementById('targetRefSelect');
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (opt.dataset.type === type || type === 'tenant') ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', () => updateTargetRef('individual'));
</script>
@endsection
