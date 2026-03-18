<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Loan Product Performance</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Portfolio quality and volume breakdown by loan product</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-end mb-6 print:hidden">
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Products</p>
            <p class="text-2xl font-extrabold text-bankos-text mt-1">{{ $productStats->count() }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Disbursed</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">₦{{ number_format($productStats->sum('total_disbursed'), 0) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Outstanding</p>
            <p class="text-2xl font-extrabold text-bankos-text mt-1">₦{{ number_format($productStats->sum('total_outstanding'), 0) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Active Loans</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($productStats->sum('active_loans')) }}</p>
        </div>
    </div>

    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Performance by Product</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Product</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Loans</th>
                        <th class="px-6 py-3 font-semibold text-right">Active</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Disbursed</th>
                        <th class="px-6 py-3 font-semibold text-right">Outstanding</th>
                        <th class="px-6 py-3 font-semibold text-right">Avg. Loan Size</th>
                        <th class="px-6 py-3 font-semibold text-right">PAR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($productStats as $row)
                    @php $parColor = $row['par_ratio'] > 10 ? 'text-red-600' : ($row['par_ratio'] > 5 ? 'text-amber-600' : 'text-emerald-600'); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-bankos-text">{{ $row['product']->name }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $row['product']->code }}</p>
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-bankos-text-sec">{{ number_format($row['total_loans']) }}</td>
                        <td class="px-6 py-4 text-right font-mono text-emerald-600 font-semibold">{{ number_format($row['active_loans']) }}</td>
                        <td class="px-6 py-4 text-right font-mono text-bankos-text">₦{{ number_format($row['total_disbursed'], 0) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-semibold text-bankos-primary">₦{{ number_format($row['total_outstanding'], 0) }}</td>
                        <td class="px-6 py-4 text-right font-mono text-bankos-text-sec">₦{{ number_format($row['avg_loan_size'], 0) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold {{ $parColor }}">{{ number_format($row['par_ratio'], 1) }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-bankos-muted">No loan products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
