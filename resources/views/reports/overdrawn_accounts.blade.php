<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center text-red-600">
                    Overdrawn Accounts Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Active customer accounts with a current balance below zero.</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <div class="bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-4 py-2 rounded-lg text-sm font-medium border border-red-200 dark:border-red-800">
            Current Overdrawn Exposure: <strong>₦{{ number_format($totalOverdrawn, 2) }}</strong>
        </div>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- Accounts Table -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md border-t-4 border-t-red-600">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Overdrawn Account Listings</h3>
            <span class="text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/30 px-2.5 py-1 rounded-full">{{ $overdrawnAccounts->count() }} Accounts</span>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-red-200 dark:border-red-900 tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer Name</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Account Details</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Contact Info</th>
                        <th class="px-6 py-4 font-bold text-right text-red-600">Current Balance (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($overdrawnAccounts as $account)
                    <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-colors">
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300 flex items-center justify-center font-bold text-xs">
                                    {{ substr($account->customer?->first_name ?? '?', 0, 1) }}{{ substr($account->customer?->last_name ?? '', 0, 1) }}
                                </div>
                                @if($account->customer)
                                <a href="{{ route('customers.show', $account->customer->id) }}" class="font-medium text-bankos-primary hover:underline">
                                    {{ $account->customer->first_name }} {{ $account->customer->last_name }}
                                </a>
                                @else
                                <span class="text-bankos-muted text-xs italic">Unknown</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-mono text-bankos-text">{{ $account->account_number }}</span>
                                <span class="text-xs text-bankos-text-sec mt-0.5">{{ $account->savingsProduct?->name ?? 'Standard Account' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="text-bankos-text">{{ $account->customer?->phone }}</span>
                                <span class="text-xs text-bankos-text-sec mt-0.5" title="{{ $account->customer?->email }}">{{ \Illuminate\Support\Str::limit($account->customer?->email ?? '', 20) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-red-600 dark:text-red-400 bg-red-50/20 dark:bg-red-900/5">
                            {{ number_format($account->available_balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-green-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">Zero Overdrawn Accounts!</p>
                                <p class="text-sm mt-1">No active customer accounts currently have a negative balance.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
