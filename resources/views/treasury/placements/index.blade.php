<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Treasury Placements</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Money market placements and borrowings</p>
            </div>
            <a href="{{ route('treasury.placements.create') }}" class="btn btn-primary text-sm">New Placement</a>
        </div>
    </x-slot>

    {{-- Filters --}}
    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Status</label>
            <select name="status" class="input input-sm mt-1">
                <option value="">All</option>
                @foreach(['active','matured','liquidated','rolled_over'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Type</label>
            <select name="type" class="input input-sm mt-1">
                <option value="">All</option>
                <option value="placement" {{ request('type') == 'placement' ? 'selected' : '' }}>Placement</option>
                <option value="borrowing" {{ request('type') == 'borrowing' ? 'selected' : '' }}>Borrowing</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="input input-sm mt-1">
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="input input-sm mt-1">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('treasury.placements') }}" class="btn btn-outline btn-sm">Reset</a>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>Counterparty</th>
                    <th>Principal</th>
                    <th>Rate</th>
                    <th>Start</th>
                    <th>Maturity</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($placements as $p)
                <tr>
                    <td class="font-mono text-sm">{{ $p->reference }}</td>
                    <td><span class="badge {{ $p->type == 'placement' ? 'badge-blue' : 'badge-amber' }}">{{ ucfirst($p->type) }}</span></td>
                    <td>{{ $p->counterparty }}</td>
                    <td class="font-medium">₦{{ number_format($p->principal, 2) }}</td>
                    <td>{{ number_format($p->interest_rate, 2) }}%</td>
                    <td>{{ $p->start_date->format('d M Y') }}</td>
                    <td>{{ $p->maturity_date->format('d M Y') }}</td>
                    <td>
                        @php $colors = ['active'=>'badge-green','matured'=>'badge-blue','liquidated'=>'badge-red','rolled_over'=>'badge-purple']; @endphp
                        <span class="badge {{ $colors[$p->status] ?? 'badge-gray' }}">{{ ucfirst(str_replace('_',' ',$p->status)) }}</span>
                    </td>
                    <td><a href="{{ route('treasury.placements.show', $p->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-8 text-bankos-muted">No placements found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $placements->links() }}</div>
</x-app-layout>
