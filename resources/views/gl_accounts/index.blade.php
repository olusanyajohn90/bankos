<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('General Ledger (GL) Accounts') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage the chart of accounts for the institution</p>
            </div>
            
            @can('gl.create')
            <a href="{{ route('gl-accounts.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New GL Account
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Account Number</th>
                        <th class="px-6 py-4 font-semibold">Name</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold text-right">Balance</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border font-mono">
                    @forelse($glAccounts as $account)
                    <tr class="hover:bg-purple-50/30 dark:hover:bg-purple-900/10 transition-colors">
                        <td class="px-6 py-4 font-bold text-bankos-primary">
                            {{ $account->account_number }}
                            @if($account->level > 1)
                                <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded ml-2 font-sans">Level {{ $account->level }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-sans text-bankos-text">
                            @if($account->parent)
                                <span class="text-bankos-text-sec text-xs mr-1">{{ $account->parent->account_number }} &rsaquo;</span>
                            @endif
                            <span class="font-medium">{{ $account->name }}</span>
                        </td>
                        <td class="px-6 py-4 font-sans">
                            @php
                                $color = match($account->category) {
                                    'Asset' => 'bg-green-100 text-green-700',
                                    'Liability' => 'bg-red-100 text-red-700',
                                    'Equity' => 'bg-indigo-100 text-indigo-700',
                                    'Revenue' => 'bg-blue-100 text-blue-700',
                                    'Expense' => 'bg-orange-100 text-orange-700',
                                    default => 'bg-gray-200 hover:bg-gray-300 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                {{ $account->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold {{ $account->balance < 0 ? 'text-red-500' : 'text-bankos-text' }}">
                            {{ number_format($account->balance, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-sans">
                            @can('gl.edit')
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('gl-accounts.edit', $account) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm transition-colors">Edit</a>
                                <form action="{{ route('gl-accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Delete this GL account? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">Delete</button>
                                </form>
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec font-sans">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                                </svg>
                                <p class="mb-4 font-medium text-bankos-text">Chart of Accounts is empty.</p>
                                @can('gl.create')
                                <a href="{{ route('gl-accounts.create') }}" class="btn btn-primary shadow-sm hover:-translate-y-0.5 transition-transform">Create GL Account</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($glAccounts->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $glAccounts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
