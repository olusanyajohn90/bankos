<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">NDIC Depositors Report</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Nigerian Deposit Insurance Corporation — Monthly Return</p>
            </div>
            <a href="{{ route('compliance.dashboard') }}" class="btn btn-secondary">Back to Compliance</a>
        </div>
    </x-slot>

    {{-- ─── Download Bar ─────────────────────────────────────────────── --}}
    <div class="card p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold">Report generated: {{ now()->format('d F Y, H:i') }}</p>
            <p class="text-xs text-bankos-muted">Institution: {{ auth()->user()->tenant?->name ?? config('app.name') }}</p>
        </div>
        <a href="{{ route('compliance.ndic.download') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </a>
    </div>

    {{-- ─── Summary Totals ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="card p-5 border-t-4 border-t-bankos-primary">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Depositors</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-primary">{{ number_format($totals['depositors']) }}</h3>
        </div>
        <div class="card p-5 border-t-4 border-t-bankos-success">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Deposits (₦)</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-success">
                @if ($totals['balance'] >= 1_000_000_000)
                    {{ number_format($totals['balance'] / 1_000_000_000, 2) }}B
                @elseif ($totals['balance'] >= 1_000_000)
                    {{ number_format($totals['balance'] / 1_000_000, 2) }}M
                @else
                    {{ number_format($totals['balance'], 2) }}
                @endif
            </h3>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Account Types</p>
            <h3 class="text-2xl font-bold mt-1">{{ $data->count() }}</h3>
        </div>
    </div>

    {{-- ─── Summary by Account Type ──────────────────────────────────── --}}
    <div class="card p-0 overflow-hidden mb-6">
        <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-bold text-base">Deposit Summary by Account Type</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Account Type</th>
                        <th class="px-6 py-4 font-semibold text-right">No. of Depositors</th>
                        <th class="px-6 py-4 font-semibold text-right">Total Balance (₦)</th>
                        <th class="px-6 py-4 font-semibold text-right">% of Total</th>
                        <th class="px-6 py-4 font-semibold text-right">Avg. per Depositor (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($data as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-semibold capitalize">{{ str_replace('_', ' ', $row->type) }}</td>
                        <td class="px-6 py-4 text-right font-mono">{{ number_format($row->depositor_count) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-bold">{{ number_format($row->total_balance, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            @if ($totals['balance'] > 0)
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                        <div class="bg-bankos-primary h-1.5 rounded-full" style="width: {{ round($row->total_balance / $totals['balance'] * 100, 1) }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ round($row->total_balance / $totals['balance'] * 100, 1) }}%</span>
                                </div>
                            @else
                                <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-bankos-muted">
                            {{ $row->depositor_count > 0 ? number_format($row->total_balance / $row->depositor_count, 2) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-bankos-muted">No active accounts found.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if ($data->isNotEmpty())
                <tfoot>
                    <tr class="bg-bankos-primary/5 dark:bg-bankos-primary/10 font-bold text-sm">
                        <td class="px-6 py-4">TOTAL</td>
                        <td class="px-6 py-4 text-right font-mono">{{ number_format($totals['depositors']) }}</td>
                        <td class="px-6 py-4 text-right font-mono text-bankos-primary">₦{{ number_format($totals['balance'], 2) }}</td>
                        <td class="px-6 py-4 text-right">100%</td>
                        <td class="px-6 py-4 text-right font-mono">
                            {{ $totals['depositors'] > 0 ? number_format($totals['balance'] / $totals['depositors'], 2) : '—' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ─── NDIC Coverage Notice ─────────────────────────────────────── --}}
    <div class="rounded-xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4 text-sm text-blue-800 dark:text-blue-300">
        <p class="font-semibold mb-1">NDIC Coverage Reminder</p>
        <p>NDIC insured maximum per depositor: <strong>₦500,000</strong> (as per current NDIC guidelines).
           Verify this data against your core banking system before submission to NDIC.</p>
    </div>
</x-app-layout>
