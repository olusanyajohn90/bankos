<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    NFIU Currency Transaction Report (CTR)
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Transactions ≥ ₦5,000,000 — Nigerian Financial Intelligence Unit</p>
            </div>
            <a href="{{ route('compliance.dashboard') }}" class="btn btn-secondary">Back to Compliance</a>
        </div>
    </x-slot>

    {{-- ─── Date Range Picker ────────────────────────────────────────── --}}
    <div class="card p-4 mb-6">
        <form action="{{ route('compliance.nfiu-ctr') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" max="{{ now()->toDateString() }}">
            </div>
            <div>
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" max="{{ now()->toDateString() }}">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('compliance.nfiu-ctr.download', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
               class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download CSV
            </a>
        </form>
    </div>

    {{-- ─── Summary Bar ──────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center {{ $transactions->total() > 0 ? 'border-t-4 border-t-accent-crimson' : '' }}">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Reportable Txns</p>
            <h3 class="text-2xl font-bold mt-1 {{ $transactions->total() > 0 ? 'text-accent-crimson' : '' }}">
                {{ number_format($transactions->total()) }}
            </h3>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Value</p>
            <h3 class="text-2xl font-bold mt-1">
                ₦{{ number_format($transactions->sum('amount') / 1_000_000, 2) }}M
            </h3>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Unique Accounts</p>
            <h3 class="text-2xl font-bold mt-1">{{ $transactions->pluck('account_id')->unique()->count() }}</h3>
        </div>
        <div class="card p-4 text-center">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Period</p>
            <p class="text-sm font-bold mt-1">{{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
        </div>
    </div>

    {{-- ─── CTR Table ────────────────────────────────────────────────── --}}
    <div class="card p-0 overflow-hidden">
        <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
            <h3 class="font-bold text-base">Large Transaction Register</h3>
            @if ($transactions->total() > 0)
                <span class="badge badge-danger">{{ $transactions->total() }} record(s) require NFIU filing</span>
            @else
                <span class="badge badge-active">No reportable transactions</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-5 py-3 font-semibold">Reference</th>
                        <th class="px-5 py-3 font-semibold">Account</th>
                        <th class="px-5 py-3 font-semibold">Customer</th>
                        <th class="px-5 py-3 font-semibold">BVN</th>
                        <th class="px-5 py-3 font-semibold text-right">Amount (₦)</th>
                        <th class="px-5 py-3 font-semibold">Type</th>
                        <th class="px-5 py-3 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($transactions as $txn)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-3 text-xs text-bankos-muted whitespace-nowrap">
                            {{ $txn->created_at->format('d M Y') }}<br>
                            <span class="text-[10px]">{{ $txn->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs font-bold text-bankos-primary">{{ $txn->reference }}</td>
                        <td class="px-5 py-3">
                            <p class="font-medium">{{ $txn->account?->account_number ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted capitalize">{{ $txn->account?->type ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $customer = $txn->account?->customer;
                                $fullName = $customer
                                    ? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''))
                                    : null;
                            @endphp
                            <p class="font-medium">{{ $fullName ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted">{{ $customer?->phone ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs">
                            @if ($customer?->bvn)
                                {{ $customer->bvn }}
                            @else
                                <span class="badge badge-danger text-[10px]">No BVN</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-mono font-bold text-accent-crimson">
                            ₦{{ number_format($txn->amount, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="badge {{ $txn->type === 'credit' ? 'badge-active' : 'badge-danger' }} capitalize">
                                {{ $txn->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-bankos-muted text-xs max-w-xs truncate" title="{{ $txn->description }}">
                            {{ $txn->description }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="text-bankos-muted">
                                <svg class="mx-auto mb-3 w-10 h-10 opacity-30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="font-medium text-bankos-success">No transactions ≥ ₦5,000,000 for the selected period.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($transactions->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 font-bold text-sm">
                        <td colspan="5" class="px-5 py-3 text-right text-bankos-muted">Page total:</td>
                        <td class="px-5 py-3 text-right font-mono text-accent-crimson">
                            ₦{{ number_format($transactions->sum('amount'), 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if ($transactions->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    {{-- ─── NFIU Filing Notice ───────────────────────────────────────── --}}
    @if ($transactions->total() > 0)
    <div class="mt-6 rounded-xl border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-800 dark:text-red-300">
        <p class="font-bold mb-1">Action Required: NFIU CTR Filing</p>
        <p>
            {{ $transactions->total() }} transaction(s) must be filed with the NFIU within 7 days of occurrence.
            Download the CSV above and submit via the NFIU GoAML portal or your authorized reporting channel.
        </p>
    </div>
    @endif
</x-app-layout>
