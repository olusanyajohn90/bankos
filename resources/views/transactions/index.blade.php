<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Global Ledger') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Real-time view of all financial movements</p>
            </div>
            
            @can('transactions.create')
            <a href="{{ route('transactions.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Post Transaction
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex flex-col sm:flex-row gap-4 justify-between sm:items-center">
            
            <form action="{{ route('transactions.index') }}" method="GET" class="flex gap-4 items-center">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10 w-full text-sm" placeholder="Search reference, account...">
                </div>

                <select name="type" class="form-select text-sm py-2" onchange="this.form.submit()">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposits</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawals</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfers</option>
                    <option value="disbursement" {{ request('type') == 'disbursement' ? 'selected' : '' }}>Loan Disbursal</option>
                    <option value="repayment" {{ request('type') == 'repayment' ? 'selected' : '' }}>Loan Repayment</option>
                </select>
                
                @if(request('search') || (request('type') && request('type') != 'all'))
                    <a href="{{ route('transactions.index') }}" class="text-sm text-bankos-primary hover:underline">Clear</a>
                @endif
            </form>

            <button class="text-sm font-medium text-bankos-primary flex items-center gap-1 hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Date & Ref</th>
                        <th class="px-6 py-4 font-semibold">Account & Description</th>
                        <th class="px-6 py-4 font-semibold text-right">Debit</th>
                        <th class="px-6 py-4 font-semibold text-right">Credit</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-medium">{{ $txn->created_at->format('d M, Y H:i:s') }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5 font-mono">{{ $txn->reference }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block bg-gray-100 dark:bg-gray-800 text-xs px-2 py-0.5 rounded text-bankos-text-sec uppercase font-mono mb-1">{{ $txn->type }}</span>
                            @if($txn->account)
                            <a href="{{ route('accounts.show', $txn->account) }}" class="font-bold text-bankos-primary block hover:underline">{{ $txn->account->account_number }} · {{ $txn->account->account_name }}</a>
                            @else
                            <span class="text-bankos-muted text-xs italic">No Account</span>
                            @endif
                            <p class="text-xs text-bankos-muted mt-1 truncate max-w-xs">{{ $txn->description }}</p>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-bankos-text dark:text-gray-300">
                            {{ $txn->amount < 0 || in_array($txn->type, ['withdrawal', 'transfer_out']) ? number_format(abs($txn->amount), 2) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-bankos-success">
                            {{ $txn->amount > 0 || in_array($txn->type, ['deposit', 'transfer_in']) ? '+' . number_format(abs($txn->amount), 2) : '-' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($txn->status === 'success')
                                <span class="badge badge-active">Completed</span>
                            @elseif($txn->status === 'pending')
                                <span class="badge badge-pending">Pending</span>
                            @elseif($txn->status === 'reversed')
                                <span class="badge bg-purple-100 text-purple-800">Reversed</span>
                            @else
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No transactions recorded yet.</p>
                            @can('transactions.create')
                            <a href="{{ route('transactions.create') }}" class="btn btn-primary text-sm">Post First Transaction</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
