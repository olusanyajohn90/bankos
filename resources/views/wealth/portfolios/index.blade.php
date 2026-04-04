<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Investment Portfolios</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Customer investment portfolios</p>
            </div>
            <a href="{{ route('wealth.portfolios.create') }}" class="btn btn-primary text-sm">New Portfolio</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Status</label>
            <select name="status" class="input input-sm mt-1"><option value="">All</option><option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option><option value="closed" {{ request('status')=='closed'?'selected':'' }}>Closed</option></select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Risk Profile</label>
            <select name="risk_profile" class="input input-sm mt-1"><option value="">All</option>@foreach(['conservative','moderate','aggressive'] as $r)<option value="{{ $r }}" {{ request('risk_profile')==$r?'selected':'' }}>{{ ucfirst($r) }}</option>@endforeach</select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="input input-sm mt-1" placeholder="Portfolio name">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Portfolio Name</th><th>Customer</th><th>Risk Profile</th><th>Value</th><th>Cost</th><th>P&L</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($portfolios as $p)
                <tr>
                    <td class="font-medium">{{ $p->portfolio_name }}</td>
                    <td>{{ $p->customer->business_name ?? ($p->customer->first_name . ' ' . $p->customer->last_name) }}</td>
                    <td><span class="badge {{ ['conservative'=>'badge-green','moderate'=>'badge-amber','aggressive'=>'badge-red'][$p->risk_profile] ?? 'badge-gray' }}">{{ ucfirst($p->risk_profile) }}</span></td>
                    <td class="font-bold">₦{{ number_format($p->total_value, 0) }}</td>
                    <td>₦{{ number_format($p->total_cost, 0) }}</td>
                    <td class="font-medium {{ $p->unrealized_pnl >= 0 ? 'text-green-600' : 'text-red-600' }}">₦{{ number_format($p->unrealized_pnl, 0) }}</td>
                    <td><span class="badge {{ $p->status=='active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($p->status) }}</span></td>
                    <td><a href="{{ route('wealth.portfolios.show', $p->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-8 text-bankos-muted">No portfolios found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $portfolios->links() }}</div>
</x-app-layout>
