<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    General Ledger Movements
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Review internal double-entry postings for the period {{ \Carbon\Carbon::parse($startDate)->format('d M y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.gl-movements') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <div class="h-6 w-px bg-bankos-border dark:bg-bankos-dark-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <div class="h-6 w-px bg-bankos-border dark:bg-bankos-dark-border"></div>
            <div class="flex items-center gap-2">
                <select name="gl_account_id" class="form-select text-sm border-none shadow-none focus:ring-bankos-primary max-w-[200px]">
                    <option value="">All Accounts</option>
                    @foreach($glAccounts as $account)
                        <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                            {{ $account->account_number }} - {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- Movement Ledger -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">GL Posting Ledger</h3>
            <div class="flex items-center gap-4 text-sm font-mono text-bankos-text-sec">
                <span>Total Debits: <strong class="text-bankos-text">₦{{ number_format($totalDebits, 2) }}</strong></span>
                <span class="h-4 w-px bg-bankos-border"></span>
                <span>Total Credits: <strong class="text-bankos-text">₦{{ number_format($totalCredits, 2) }}</strong></span>
            </div>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-bankos-border dark:border-bankos-dark-border tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Date</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">GL Account</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Description / Ref</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Debit (₦)</th>
                        <th class="px-6 py-4 font-bold text-right">Credit (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($postings as $posting)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800 font-medium text-bankos-text whitespace-nowrap">
                            {{ $posting->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-medium text-bankos-text">{{ $posting->glAccount?->name ?? '—' }}</span>
                                <span class="text-xs text-bankos-text-sec font-mono mt-0.5">{{ $posting->glAccount?->account_number }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-medium text-bankos-text line-clamp-1" title="{{ $posting->description }}">{{ $posting->description }}</span>
                                @if($posting->transaction)
                                    <span class="text-xs text-bankos-text-sec font-mono mt-0.5">Txn Ref: {{ $posting->transaction->reference }}</span>
                                @else
                                    <span class="text-xs text-bankos-text-sec italic mt-0.5">Manual Journal Entry</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800 {{ $posting->debit_amount > 0 ? 'text-bankos-text font-bold' : 'text-gray-300 dark:text-gray-700' }}">
                            {{ $posting->debit_amount > 0 ? number_format($posting->debit_amount, 2) : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right font-mono {{ $posting->credit_amount > 0 ? 'text-bankos-text font-bold' : 'text-gray-300 dark:text-gray-700' }}">
                            {{ $posting->credit_amount > 0 ? number_format($posting->credit_amount, 2) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            No GL postings found for the specified criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($postings->count() > 0)
                <tfoot>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-t border-bankos-text dark:border-bankos-dark-text text-bankos-text font-bold text-base">
                        <td colspan="3" class="px-6 py-4 uppercase tracking-wider text-sm border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-right">
                            Total Period Movements
                        </td>
                        <td class="px-6 py-4 text-right font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 border-double">
                            ₦{{ number_format($totalDebits, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono border-double">
                            ₦{{ number_format($totalCredits, 2) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
