<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $assessment->title }}</h2><p class="text-sm text-bankos-text-sec mt-1">{{ ucfirst(str_replace('_',' ',$assessment->risk_type)) }} Risk Assessment</p></div>
            <a href="{{ route('risk-management.assessments') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card p-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Risk Type</dt><dd>{{ ucfirst(str_replace('_',' ',$assessment->risk_type)) }}</dd></div>
                <div><dt class="text-bankos-muted">Severity</dt><dd>@php $sc=['low'=>'badge-green','medium'=>'badge-amber','high'=>'badge-red','critical'=>'badge-red']; @endphp<span class="badge {{ $sc[$assessment->severity] ?? 'badge-gray' }}">{{ ucfirst($assessment->severity) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd><span class="badge {{ $assessment->status=='open' ? 'badge-amber' : 'badge-green' }}">{{ ucfirst($assessment->status) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Exposure</dt><dd class="font-bold">{{ $assessment->exposure_amount ? '₦'.number_format($assessment->exposure_amount, 0) : 'N/A' }}</dd></div>
                <div><dt class="text-bankos-muted">Assigned To</dt><dd>{{ $assessment->assignee->name ?? 'Unassigned' }}</dd></div>
                <div><dt class="text-bankos-muted">Created By</dt><dd>{{ $assessment->creator->name ?? 'N/A' }}</dd></div>
            </dl>
            @if($assessment->description)<div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border"><h4 class="text-sm font-semibold text-bankos-muted mb-1">Description</h4><p class="text-sm">{{ $assessment->description }}</p></div>@endif
            @if($assessment->mitigation_plan)<div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border"><h4 class="text-sm font-semibold text-bankos-muted mb-1">Mitigation Plan</h4><p class="text-sm">{{ $assessment->mitigation_plan }}</p></div>@endif
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-muted mb-3">Risk Metrics</h3>
            @if($assessment->metrics && is_array($assessment->metrics))
                @foreach($assessment->metrics as $k => $v)
                <div class="flex justify-between text-sm mb-2"><span class="text-bankos-muted">{{ ucfirst(str_replace('_',' ',$k)) }}</span><span class="font-bold">{{ $v }}</span></div>
                @endforeach
            @else
            <p class="text-sm text-bankos-muted">No metrics recorded.</p>
            @endif
        </div>
    </div>
</x-app-layout>
