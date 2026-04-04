<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Performance: {{ $portfolio->portfolio_name }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ $portfolio->customer->first_name ?? '' }} {{ $portfolio->customer->last_name ?? '' }}</p>
            </div>
            <a href="{{ route('wealth.portfolios.show', $portfolio->id) }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Total Cost</p>
            <h3 class="text-2xl font-bold mt-2">₦{{ number_format($totalCost, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Market Value</p>
            <h3 class="text-2xl font-bold mt-2">₦{{ number_format($totalMarketValue, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-{{ $totalPnl >= 0 ? 'green' : 'red' }}-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Unrealized P&L</p>
            <h3 class="text-2xl font-bold mt-2 {{ $totalPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($totalPnl, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Return</p>
            <h3 class="text-2xl font-bold mt-2 {{ $returnPct >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($returnPct, 2) }}%</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Avg Yield</p>
            <h3 class="text-2xl font-bold mt-2">{{ number_format($avgYield, 2) }}%</h3>
        </div>
    </div>

    <div class="card overflow-hidden">
        <h3 class="p-4 text-lg font-semibold border-b border-bankos-border dark:border-bankos-dark-border">Holding Performance</h3>
        <table class="bankos-table w-full text-sm">
            <thead><tr><th>Asset</th><th>Type</th><th>Qty</th><th>Cost</th><th>Current</th><th>P&L</th><th>Return %</th></tr></thead>
            <tbody>
            @foreach($holdings as $h)
                @php $hCost = $h->quantity * $h->cost_price; $hPnl = $h->market_value - $hCost; $hReturn = $hCost > 0 ? ($hPnl/$hCost)*100 : 0; @endphp
                <tr>
                    <td class="font-medium">{{ $h->asset_name }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$h->asset_type)) }}</td>
                    <td>{{ number_format($h->quantity, 2) }}</td>
                    <td>₦{{ number_format($hCost, 0) }}</td>
                    <td>₦{{ number_format($h->market_value, 0) }}</td>
                    <td class="font-medium {{ $hPnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($hPnl, 0) }}</td>
                    <td class="{{ $hReturn >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($hReturn, 2) }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
