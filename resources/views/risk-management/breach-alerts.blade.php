<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Breach Alerts</h2><p class="text-sm text-bankos-text-sec mt-1">Limits in warning or breached status</p></div>
            <a href="{{ route('risk-management.dashboard') }}" class="btn btn-outline text-sm">Back to Dashboard</a>
        </div>
    </x-slot>

    @if($breached->count())
    <div class="space-y-4">
        @foreach($breached as $l)
        <div class="card p-5 border-l-4 {{ $l->status == 'breached' ? 'border-l-red-500' : 'border-l-amber-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-bankos-text dark:text-white">{{ $l->name }}</h3>
                    <p class="text-sm text-bankos-muted">{{ ucfirst(str_replace('_',' ',$l->limit_type)) }}</p>
                </div>
                <span class="badge {{ $l->status == 'breached' ? 'badge-red' : 'badge-amber' }}">{{ ucfirst(str_replace('_',' ',$l->status)) }}</span>
            </div>
            <div class="mt-3 grid grid-cols-4 gap-4 text-sm">
                <div><span class="text-bankos-muted">Limit:</span> <span class="font-bold">₦{{ number_format($l->limit_value, 0) }}</span></div>
                <div><span class="text-bankos-muted">Current:</span> <span class="font-bold">₦{{ number_format($l->current_value, 0) }}</span></div>
                <div><span class="text-bankos-muted">Utilization:</span> <span class="font-bold {{ $l->status == 'breached' ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($l->utilization_pct, 1) }}%</span></div>
                <div><span class="text-bankos-muted">Warning at:</span> <span class="font-bold">{{ $l->warning_threshold }}%</span></div>
            </div>
            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div class="h-3 rounded-full {{ $l->status == 'breached' ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ min($l->utilization_pct, 100) }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card p-8 text-center"><p class="text-bankos-muted">No breach alerts. All limits within thresholds.</p></div>
    @endif
</x-app-layout>
