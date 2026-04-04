<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Placement: {{ $placement->reference }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ ucfirst($placement->type) }} with {{ $placement->counterparty }}</p>
            </div>
            <a href="{{ route('treasury.placements') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card p-6">
            <h3 class="text-lg font-semibold mb-4 text-bankos-text dark:text-white">Placement Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Reference</dt><dd class="font-mono font-medium">{{ $placement->reference }}</dd></div>
                <div><dt class="text-bankos-muted">Type</dt><dd><span class="badge {{ $placement->type == 'placement' ? 'badge-blue' : 'badge-amber' }}">{{ ucfirst($placement->type) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Counterparty</dt><dd class="font-medium">{{ $placement->counterparty }}</dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd><span class="badge badge-green">{{ ucfirst(str_replace('_',' ',$placement->status)) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Principal</dt><dd class="font-bold text-lg">₦{{ number_format($placement->principal, 2) }}</dd></div>
                <div><dt class="text-bankos-muted">Interest Rate</dt><dd class="font-bold">{{ number_format($placement->interest_rate, 2) }}% p.a.</dd></div>
                <div><dt class="text-bankos-muted">Start Date</dt><dd>{{ $placement->start_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Maturity Date</dt><dd>{{ $placement->maturity_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Tenor</dt><dd>{{ $placement->tenor_days }} days</dd></div>
                <div><dt class="text-bankos-muted">Days to Maturity</dt><dd class="{{ $placement->maturity_date->isPast() ? 'text-red-600 font-bold' : '' }}">{{ $placement->maturity_date->isPast() ? 'MATURED' : now()->diffInDays($placement->maturity_date) . ' days' }}</dd></div>
                <div><dt class="text-bankos-muted">Expected Interest</dt><dd class="font-medium text-green-600">₦{{ number_format($placement->expected_interest, 2) }}</dd></div>
                <div><dt class="text-bankos-muted">Accrued Interest</dt><dd class="font-medium">₦{{ number_format($placement->accrued_interest, 2) }}</dd></div>
            </dl>
            @if($placement->notes)
            <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="text-sm font-semibold text-bankos-muted mb-2">Notes</h4>
                <p class="text-sm text-bankos-text-sec">{{ $placement->notes }}</p>
            </div>
            @endif
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4 text-bankos-text dark:text-white">Summary</h3>
            <div class="space-y-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-xs text-bankos-muted uppercase">Total at Maturity</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">₦{{ number_format($placement->principal + $placement->expected_interest, 2) }}</p>
                </div>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-bankos-muted">Created by</span><span>{{ $placement->creator->name ?? 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-bankos-muted">Created</span><span>{{ $placement->created_at->format('d M Y H:i') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
