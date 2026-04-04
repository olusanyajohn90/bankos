<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Trade Finance Instruments</h2>
                <p class="text-sm text-bankos-text-sec mt-1">LCs, guarantees, bills for collection</p>
            </div>
            <a href="{{ route('trade-finance.create') }}" class="btn btn-primary text-sm">New Instrument</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Type</label>
            <select name="type" class="input input-sm mt-1">
                <option value="">All</option>
                @foreach(['letter_of_credit','bank_guarantee','bill_for_collection','invoice_discounting'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Status</label>
            <select name="status" class="input input-sm mt-1">
                <option value="">All</option>
                @foreach(['draft','issued','amended','utilized','expired','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="input input-sm mt-1" placeholder="Reference or beneficiary">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead>
                <tr><th>Reference</th><th>Type</th><th>Customer</th><th>Beneficiary</th><th>Amount</th><th>Expiry</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($instruments as $i)
                <tr>
                    <td class="font-mono text-sm">{{ $i->reference }}</td>
                    <td><span class="badge badge-blue">{{ ucfirst(str_replace('_',' ',$i->type)) }}</span></td>
                    <td>{{ $i->customer->business_name ?? ($i->customer->first_name . ' ' . $i->customer->last_name) }}</td>
                    <td>{{ $i->beneficiary_name }}</td>
                    <td class="font-medium">{{ $i->currency }} {{ number_format($i->amount, 2) }}</td>
                    <td>{{ $i->expiry_date->format('d M Y') }}</td>
                    <td>
                        @php $colors = ['draft'=>'badge-gray','issued'=>'badge-green','amended'=>'badge-blue','utilized'=>'badge-purple','expired'=>'badge-red','cancelled'=>'badge-red']; @endphp
                        <span class="badge {{ $colors[$i->status] ?? 'badge-gray' }}">{{ ucfirst($i->status) }}</span>
                    </td>
                    <td><a href="{{ route('trade-finance.show', $i->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-8 text-bankos-muted">No instruments found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $instruments->links() }}</div>
</x-app-layout>
