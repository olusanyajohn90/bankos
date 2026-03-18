<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    Account Statement
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Detailed transaction history</p>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card mb-6 print:hidden">
        <form action="{{ route('reports.account-statement') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full relative">
                <x-input-label for="account_number" :value="__('Account Number')" />
                <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full uppercase" :value="$accountNumber" placeholder="e.g. 1002345678" required />
            </div>
            
            <div class="w-full md:w-48 relative">
                <x-input-label for="start_date" :value="__('Start Date')" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="$startDate" required />
            </div>
            
            <div class="w-full md:w-48 relative">
                <x-input-label for="end_date" :value="__('End Date')" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="$endDate" required />
            </div>
            
            <button type="submit" class="btn btn-primary w-full md:w-auto mt-4 md:mt-0 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                Generate
            </button>
        </form>
    </div>

    @if($account)
    <div class="card p-0 overflow-hidden">
        <!-- Statement Header -->
        <div class="p-8 border-b border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-bankos-primary uppercase tracking-tight">{{ auth()->user()->tenant?->name ?? 'BankOS' }}</h1>
                    <p class="text-xs text-bankos-text-sec mt-1">Generated: {{ now()->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold text-bankos-text">STATEMENT OF ACCOUNT</h2>
                    <p class="text-sm font-medium text-bankos-text-sec mt-1">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8 p-6 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-700">
                <div>
                    <p class="text-xs text-bankos-text-sec mb-1 uppercase tracking-wider font-semibold">Account Name</p>
                    <p class="font-bold text-bankos-text text-lg">{{ $account->customer?->full_name ?? $account->account_name }}</p>
                    @if($account->customer)
                    <p class="text-xs text-bankos-muted mt-1">{{ $account->customer->phone ?? '' }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-bankos-text-sec mb-1 uppercase tracking-wider font-semibold">Account Number</p>
                    <p class="font-mono text-lg font-bold text-bankos-primary">{{ $account->account_number }}</p>
                    <p class="text-xs text-bankos-muted mt-1">{{ ucfirst($account->type) }} Account • {{ $account->currency }}</p>
                </div>
                <div>
                    <p class="text-xs text-bankos-text-sec mb-1 uppercase tracking-wider font-semibold">Opening Balance</p>
                    <p class="font-mono text-lg font-bold text-bankos-text">₦{{ number_format($openingBalance, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-bankos-text-sec mb-1 uppercase tracking-wider font-semibold">Closing Balance</p>
                    <p class="font-mono text-lg font-bold {{ $closingBalance >= 0 ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">₦{{ number_format($closingBalance, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-bankos-dark-bg/50 px-6 py-3 flex justify-end gap-3 print:hidden border-b border-bankos-border dark:border-bankos-dark-border">
            <a href="{{ route('reports.account-statement.download', ['account_number' => $account->account_number, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="btn btn-primary text-xs flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download PDF (Password-Protected)
            </a>
            <button class="btn btn-secondary text-xs flex items-center gap-2" onclick="printStatement()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Statement
            </button>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Value Date</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold">Reference</th>
                        <th class="px-6 py-4 font-semibold text-right text-red-600 dark:text-red-400">Debit (DR)</th>
                        <th class="px-6 py-4 font-semibold text-right text-green-600 dark:text-green-400">Credit (CR)</th>
                        <th class="px-6 py-4 font-semibold text-right border-l border-bankos-border/50 dark:border-bankos-dark-border/50">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <!-- Opening Balance Row -->
                    <tr class="bg-gray-50/50 dark:bg-gray-800/30">
                        <td class="px-6 py-3 text-bankos-text-sec">{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec">-</td>
                        <td class="px-6 py-3 font-medium font-serif italic text-bankos-text-sec">Brought Forward</td>
                        <td class="px-6 py-3 text-bankos-text-sec font-mono text-xs">OPENING-BAL</td>
                        <td class="px-6 py-3 text-right"></td>
                        <td class="px-6 py-3 text-right"></td>
                        <td class="px-6 py-3 text-right font-mono font-bold border-l border-dashed border-gray-200 dark:border-gray-700">₦{{ number_format($openingBalance, 2) }}</td>
                    </tr>

                    <!-- Transactions -->
                    @forelse($transactions as $txn)
                    @php
                        // Amounts are signed: positive = credit (money in), negative = debit (money out)
                        $isCredit = $txn->amount > 0;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3 text-bankos-text-sec">{{ $txn->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec">{{ $txn->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3">
                            <span class="text-bankos-text">{{ $txn->description ?? Str::title(str_replace('_', ' ', $txn->type)) }}</span>
                            @if($txn->status !== 'success')
                                <span class="ml-2 text-[10px] uppercase font-bold text-red-500 {{ $txn->status === 'reversed' ? 'line-through' : '' }}">[{{ $txn->status }}]</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-bankos-muted font-mono text-xs">{{ $txn->reference }}</td>

                        <td class="px-6 py-3 text-right font-mono text-red-600 dark:text-red-400">
                            {{ !$isCredit && $txn->status === 'success' ? number_format(abs($txn->amount), 2) : '' }}
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-green-600 dark:text-green-400">
                            {{ $isCredit && $txn->status === 'success' ? number_format($txn->amount, 2) : '' }}
                        </td>

                        <td class="px-6 py-3 text-right font-mono font-medium border-l border-dashed border-gray-200 dark:border-gray-700">
                            ₦{{ number_format($txn->running_balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-bankos-muted">
                            <p>No transactions found for this period.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6 text-center text-xs text-bankos-muted border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <p>This is a computer-generated document. No signature is required.</p>
        </div>
    </div>
    @elseif(request()->has('account_number'))
    <div class="card p-12 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-bankos-border dark:text-bankos-dark-border"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <h3 class="text-lg font-bold text-bankos-text">Account Not Found</h3>
        <p class="text-bankos-text-sec mt-2 max-w-sm mx-auto">We couldn't find an account matching "{{ $accountNumber }}". Please check the number and try again.</p>
    </div>
    @else
    <div class="card p-12 text-center border-dashed">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-blue-200 dark:text-blue-900"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <h3 class="text-lg font-bold text-bankos-text">Generate Account Statement</h3>
        <p class="text-bankos-text-sec mt-2 max-w-sm mx-auto">Enter an account number and date range above to view and print the transaction history.</p>
    </div>
    @endif

@if($account)
<style>
@media print {
    @page { size: A4 landscape; margin: 12mm; }
    body { font-size: 10px !important; }
    table { font-size: 9px !important; }
    th, td { padding: 4px 8px !important; }
    .overflow-x-auto { overflow: visible !important; }
}
</style>
<script>
function printStatement() {
    const prev = document.title;
    document.title = 'SOA-{{ $account->account_number }}-{{ $startDate }}-to-{{ $endDate }}';
    window.print();
    document.title = prev;
}
</script>
@endif
</x-app-layout>
