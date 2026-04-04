<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">FX Deal: {{ $deal->reference }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ ucfirst($deal->deal_type) }} - {{ $deal->currency_pair }}</p>
            </div>
            <a href="{{ route('treasury.fx-deals') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="card p-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Reference</dt><dd class="font-mono font-medium">{{ $deal->reference }}</dd></div>
                <div><dt class="text-bankos-muted">Deal Type</dt><dd><span class="badge badge-purple">{{ ucfirst($deal->deal_type) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Direction</dt><dd><span class="badge {{ $deal->direction == 'buy' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($deal->direction) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Currency Pair</dt><dd class="font-bold text-lg">{{ $deal->currency_pair }}</dd></div>
                <div><dt class="text-bankos-muted">Amount (Base)</dt><dd class="font-medium">{{ number_format($deal->amount, 2) }}</dd></div>
                <div><dt class="text-bankos-muted">Rate</dt><dd class="font-bold">{{ number_format($deal->rate, 6) }}</dd></div>
                <div><dt class="text-bankos-muted">Counter Amount</dt><dd class="font-bold text-lg text-green-600">₦{{ number_format($deal->counter_amount, 2) }}</dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd>
                    @php $colors = ['pending'=>'badge-amber','settled'=>'badge-green','cancelled'=>'badge-red']; @endphp
                    <span class="badge {{ $colors[$deal->status] ?? 'badge-gray' }}">{{ ucfirst($deal->status) }}</span>
                </dd></div>
                <div><dt class="text-bankos-muted">Trade Date</dt><dd>{{ $deal->trade_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Settlement Date</dt><dd>{{ $deal->settlement_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Counterparty</dt><dd>{{ $deal->counterparty ?? 'N/A' }}</dd></div>
                <div><dt class="text-bankos-muted">Dealer</dt><dd>{{ $deal->dealer->name ?? 'N/A' }}</dd></div>
            </dl>
        </div>
    </div>
</x-app-layout>
