<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Cash Positions</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Historical daily cash positions</p>
            </div>
            <a href="{{ route('cash-management.create') }}" class="btn btn-primary text-sm">Record Position</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="input input-sm mt-1">
        </div>
        <div>
            <label class="text-xs font-medium text-bankos-text-sec">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="input input-sm mt-1">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead>
                <tr><th>Date</th><th>Opening</th><th>Inflows</th><th>Outflows</th><th>Closing</th><th>Vault Cash</th><th>Nostro</th><th>Prepared By</th></tr>
            </thead>
            <tbody>
            @forelse($positions as $p)
                <tr>
                    <td class="font-medium">{{ $p->position_date->format('d M Y') }}</td>
                    <td>₦{{ number_format($p->opening_balance, 0) }}</td>
                    <td class="text-green-600">₦{{ number_format($p->total_inflows, 0) }}</td>
                    <td class="text-red-600">₦{{ number_format($p->total_outflows, 0) }}</td>
                    <td class="font-bold">₦{{ number_format($p->closing_balance, 0) }}</td>
                    <td>₦{{ number_format($p->vault_cash, 0) }}</td>
                    <td>₦{{ number_format($p->nostro_balance, 0) }}</td>
                    <td>{{ $p->preparer->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-8 text-bankos-muted">No positions recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $positions->links() }}</div>
</x-app-layout>
