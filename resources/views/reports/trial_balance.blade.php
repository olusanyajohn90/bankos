<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-3">
                    Trial Balance
                    @if($isBalanced)
                        <span class="badge badge-success text-[10px] uppercase font-bold tracking-widest"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="inline mr-0.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Balanced</span>
                    @else
                        <span class="badge badge-danger text-[10px] uppercase font-bold tracking-widest"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="inline mr-0.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Out of Balance</span>
                    @endif
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Summary of all General Ledger account balances as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <div class="flex items-center gap-2 text-sm text-bankos-text-sec">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Generated: {{ now()->format('Y-m-d H:i:s') }}
            </div>
            <button class="btn btn-secondary text-xs flex items-center gap-2" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print PDF
            </button>
        </div>

        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-bankos-border dark:border-bankos-dark-border tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Account Code</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50 w-2/5">Account Name</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Category</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Debit (NGN)</th>
                        <th class="px-6 py-4 font-bold text-right">Credit (NGN)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($glAccounts as $account)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $account->level === 1 ? 'font-bold bg-gray-50/50 dark:bg-gray-800/30' : '' }}">
                        <td class="px-6 py-3 font-mono text-xs border-r border-dashed border-gray-200 dark:border-gray-800 text-bankos-text-sec">
                            {{ $account->account_number }}
                        </td>
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="{{ $account->level > 1 ? 'ml-4 text-bankos-text-sec' : 'text-bankos-text' }}">
                                {{ $account->name }}
                            </span>
                        </td>
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800 text-xs">
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-bankos-text-sec uppercase tracking-wider">
                                {{ $account->category }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-3 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800 {{ $account->debit_balance > 0 ? 'text-bankos-text font-medium' : 'text-gray-300 dark:text-gray-700' }}">
                            {{ $account->debit_balance > 0 ? number_format($account->debit_balance, 2) : '-' }}
                        </td>
                        <td class="px-6 py-3 text-right font-mono {{ $account->credit_balance > 0 ? 'text-bankos-text font-medium' : 'text-gray-300 dark:text-gray-700' }}">
                            {{ $account->credit_balance > 0 ? number_format($account->credit_balance, 2) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-t-2 border-bankos-text dark:border-bankos-dark-text text-bankos-text font-bold text-base">
                        <td colspan="3" class="px-6 py-5 text-right uppercase tracking-wider text-sm border-r border-bankos-border/50 dark:border-bankos-dark-border/50">
                            Totals
                        </td>
                        <td class="px-6 py-5 text-right font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 {{ $isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            ₦ {{ number_format($totalDebits, 2) }}
                        </td>
                        <td class="px-6 py-5 text-right font-mono {{ $isBalanced ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            ₦ {{ number_format($totalCredits, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
