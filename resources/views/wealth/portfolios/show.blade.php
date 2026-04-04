<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $portfolio->portfolio_name }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ $portfolio->customer->business_name ?? ($portfolio->customer->first_name . ' ' . $portfolio->customer->last_name) }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('wealth.portfolios.add-holding', $portfolio->id) }}" class="btn btn-primary text-sm">Add Holding</a>
                <a href="{{ route('wealth.portfolios.performance', $portfolio->id) }}" class="btn btn-outline text-sm">Performance</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Market Value</p>
            <h3 class="text-2xl font-bold mt-2">₦{{ number_format($portfolio->total_value, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-indigo-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Total Cost</p>
            <h3 class="text-2xl font-bold mt-2">₦{{ number_format($portfolio->total_cost, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-{{ $portfolio->unrealized_pnl >= 0 ? 'green' : 'red' }}-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Unrealized P&L</p>
            <h3 class="text-2xl font-bold mt-2 {{ $portfolio->unrealized_pnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($portfolio->unrealized_pnl, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase">Risk Profile</p>
            <h3 class="text-xl font-bold mt-2">{{ ucfirst($portfolio->risk_profile) }}</h3>
        </div>
    </div>

    {{-- Allocation chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Asset Allocation</h3>
            <canvas id="holdingChart" height="250"></canvas>
        </div>
        <div class="lg:col-span-2 card p-6">
            <h3 class="text-lg font-semibold mb-4">Holdings</h3>
            <table class="bankos-table w-full text-sm">
                <thead><tr><th>Asset</th><th>Type</th><th>Qty</th><th>Cost</th><th>Current</th><th>Value</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($portfolio->holdings->sortByDesc('market_value') as $h)
                    <tr>
                        <td class="font-medium">{{ $h->asset_name }}</td>
                        <td><span class="badge badge-blue">{{ ucfirst(str_replace('_',' ',$h->asset_type)) }}</span></td>
                        <td>{{ number_format($h->quantity, 2) }}</td>
                        <td>₦{{ number_format($h->cost_price, 2) }}</td>
                        <td>₦{{ number_format($h->current_price, 2) }}</td>
                        <td class="font-bold">₦{{ number_format($h->market_value, 0) }}</td>
                        <td><span class="badge {{ $h->status=='active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($h->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-bankos-muted">No holdings yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const byType = {};
        @foreach($portfolio->holdings->where('status','active') as $h)
        byType['{{ ucfirst(str_replace("_"," ",$h->asset_type)) }}'] = (byType['{{ ucfirst(str_replace("_"," ",$h->asset_type)) }}'] || 0) + {{ $h->market_value }};
        @endforeach
        new Chart(document.getElementById('holdingChart'), {
            type: 'doughnut',
            data: { labels: Object.keys(byType), datasets: [{ data: Object.values(byType), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'] }] },
            options: { responsive: true }
        });
    });
    </script>
</x-app-layout>
