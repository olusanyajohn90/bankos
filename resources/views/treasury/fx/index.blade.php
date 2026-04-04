<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">FX Deals</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Foreign exchange spot, forward and swap deals</p>
            </div>
            <a href="{{ route('treasury.fx-deals.create') }}" class="btn btn-primary text-sm">Book FX Deal</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Status</label>
            <select name="status" class="input input-sm mt-1">
                <option value="">All</option>
                @foreach(['pending','settled','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Deal Type</label>
            <select name="deal_type" class="input input-sm mt-1">
                <option value="">All</option>
                @foreach(['spot','forward','swap'] as $t)
                <option value="{{ $t }}" {{ request('deal_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Direction</label>
            <select name="direction" class="input input-sm mt-1">
                <option value="">All</option>
                <option value="buy" {{ request('direction') == 'buy' ? 'selected' : '' }}>Buy</option>
                <option value="sell" {{ request('direction') == 'sell' ? 'selected' : '' }}>Sell</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('treasury.fx-deals') }}" class="btn btn-outline btn-sm">Reset</a>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>Direction</th>
                    <th>Pair</th>
                    <th>Amount</th>
                    <th>Rate</th>
                    <th>Settlement</th>
                    <th>Counter Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($deals as $d)
                <tr>
                    <td class="font-mono text-sm">{{ $d->reference }}</td>
                    <td><span class="badge badge-purple">{{ ucfirst($d->deal_type) }}</span></td>
                    <td><span class="badge {{ $d->direction == 'buy' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($d->direction) }}</span></td>
                    <td class="font-medium">{{ $d->currency_pair }}</td>
                    <td>{{ number_format($d->amount, 2) }}</td>
                    <td>{{ number_format($d->rate, 4) }}</td>
                    <td>{{ $d->settlement_date->format('d M Y') }}</td>
                    <td class="font-medium">₦{{ number_format($d->counter_amount, 2) }}</td>
                    <td>
                        @php $colors = ['pending'=>'badge-amber','settled'=>'badge-green','cancelled'=>'badge-red']; @endphp
                        <span class="badge {{ $colors[$d->status] ?? 'badge-gray' }}">{{ ucfirst($d->status) }}</span>
                    </td>
                    <td><a href="{{ route('treasury.fx-deals.show', $d->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center py-8 text-bankos-muted">No FX deals found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deals->links() }}</div>
</x-app-layout>
