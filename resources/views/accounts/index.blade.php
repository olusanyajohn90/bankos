<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Accounts Directory') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage customer current, savings, and fixed accounts</p>
            </div>
            
            @can('accounts.create')
            <a href="{{ route('customers.index') }}" class="btn btn-primary">
                Open New Account
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <form action="{{ route('accounts.index') }}" method="GET" class="flex w-full sm:w-80 items-center">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10 w-full" placeholder="Search account number, name...">
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Account Details</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Product</th>
                        <th class="px-6 py-4 font-semibold text-right">Available Balance</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary">{{ $account->account_number }}</p>
                            <p class="text-xs text-bankos-muted mt-1 uppercase">{{ $account->account_name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($account->customer)
                            <a href="{{ route('customers.show', $account->customer) }}" class="font-medium text-bankos-text dark:text-white hover:text-bankos-primary">
                                {{ $account->customer->first_name }} {{ $account->customer->last_name }}
                            </a>
                            @else
                            <span class="text-bankos-muted text-xs italic">Unknown Customer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-pending">{{ $account->savingsProduct?->code ?? $account->type }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="font-bold text-bankos-text dark:text-white">{{ $account->currency }} {{ number_format($account->available_balance, 2) }}</p>
                            <p class="text-[10px] text-bankos-muted uppercase tracking-widest mt-0.5">Ledger: {{ number_format($account->ledger_balance, 2) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($account->status === 'active')
                                <span class="badge badge-active flex items-center w-max gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full bg-bankos-success"></div> Active
                                </span>
                            @elseif($account->status === 'frozen')
                                <span class="badge badge-danger">Frozen</span>
                            @elseif($account->status === 'dormant')
                                <span class="badge badge-pending">Dormant</span>
                            @else
                                <span class="badge bg-gray-200 hover:bg-gray-300 text-gray-800">Closed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('accounts.show', $account) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View Ledger</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No accounts found.</p>
                            @can('accounts.create')
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary text-sm">Find Customer to Open Account</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $accounts->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
