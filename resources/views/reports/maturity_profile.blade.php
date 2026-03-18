<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Maturity Profile</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Loan and term-deposit maturities bucketed by time horizon — as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-end mb-6 print:hidden">
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- Maturity Buckets --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        @foreach($buckets as $key => $bucket)
        @php
            $loanAmt    = $bucket['loans']->sum('outstanding_balance');
            $depositAmt = $bucket['deposits']->sum('available_balance');
        @endphp
        <div class="card p-5 {{ ($key === '0_30') ? 'border-red-300 dark:border-red-800' : '' }}">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">{{ $bucket['label'] }}</p>
            <div class="mt-2 space-y-1">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-bankos-muted">Loans</span>
                    <span class="font-bold text-bankos-primary text-sm">{{ $bucket['loans']->count() }} · ₦{{ number_format($loanAmt, 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-bankos-muted">Deposits</span>
                    <span class="font-bold text-emerald-600 text-sm">{{ $bucket['deposits']->count() }} · ₦{{ number_format($depositAmt, 0) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Detail tables per bucket --}}
    @foreach($buckets as $key => $bucket)
    @if($bucket['loans']->isNotEmpty() || $bucket['deposits']->isNotEmpty())
    <div class="card p-0 overflow-hidden shadow-md mb-6">
        <div class="px-6 py-3 border-b border-bankos-border dark:border-bankos-dark-border {{ $key === '0_30' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-bankos-dark-bg/50' }}">
            <h3 class="text-sm font-semibold {{ $key === '0_30' ? 'text-red-700 dark:text-red-400' : 'text-bankos-text' }}">{{ $bucket['label'] }}</h3>
        </div>

        @if($bucket['loans']->isNotEmpty())
        <div class="px-6 pt-3 pb-1">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Loans Maturing</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-bankos-border text-xs uppercase text-bankos-text-sec bg-bankos-light dark:bg-bankos-dark-bg/80">
                        <th class="px-6 py-2 font-semibold">Borrower / Loan</th>
                        <th class="px-6 py-2 font-semibold text-right">Outstanding</th>
                        <th class="px-6 py-2 font-semibold text-right">Maturity Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($bucket['loans'] as $loan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-2">
                            <p class="font-medium text-bankos-text">{{ $loan->customer?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $loan->loan_number }}</p>
                        </td>
                        <td class="px-6 py-2 text-right font-mono font-semibold text-bankos-primary">₦{{ number_format($loan->outstanding_balance, 2) }}</td>
                        <td class="px-6 py-2 text-right text-bankos-text-sec text-xs">{{ $loan->expected_maturity_date ? \Carbon\Carbon::parse($loan->expected_maturity_date)->format('d M Y') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($bucket['deposits']->isNotEmpty())
        <div class="px-6 pt-3 pb-1 {{ $bucket['loans']->isNotEmpty() ? 'border-t border-dashed border-bankos-border' : '' }}">
            <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Term Deposits Maturing</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-bankos-border text-xs uppercase text-bankos-text-sec bg-bankos-light dark:bg-bankos-dark-bg/80">
                        <th class="px-6 py-2 font-semibold">Account / Customer</th>
                        <th class="px-6 py-2 font-semibold">Product</th>
                        <th class="px-6 py-2 font-semibold text-right">Balance</th>
                        <th class="px-6 py-2 font-semibold text-right">Maturity Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($bucket['deposits'] as $account)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-2">
                            <p class="font-medium text-bankos-text">{{ $account->customer?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $account->account_number }}</p>
                        </td>
                        <td class="px-6 py-2 text-xs text-bankos-text-sec">{{ $account->savingsProduct?->name ?? '—' }}</td>
                        <td class="px-6 py-2 text-right font-mono font-semibold text-emerald-600">₦{{ number_format($account->available_balance, 2) }}</td>
                        <td class="px-6 py-2 text-right text-bankos-text-sec text-xs">{{ $account->savingsProduct?->maturity_date ? \Carbon\Carbon::parse($account->savingsProduct->maturity_date)->format('d M Y') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif
    @endforeach
</x-app-layout>
