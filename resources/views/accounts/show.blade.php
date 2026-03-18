<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-2">
                    {{ $account->account_name }}
                    @if($account->status === 'active')
                        <span class="badge badge-active text-xs">Active</span>
                    @elseif($account->status === 'frozen')
                        <span class="badge badge-danger text-xs">Frozen</span>
                    @else
                        <span class="badge badge-pending text-xs">{{ ucfirst($account->status) }}</span>
                    @endif
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <span class="font-mono text-bankos-primary font-medium">{{ $account->account_number }}</span>
                    <span>•</span>
                    @if($account->customer)
                    <a href="{{ route('customers.show', $account->customer) }}" class="hover:text-bankos-primary hover:underline">{{ $account->customer->first_name }} {{ $account->customer->last_name }}</a>
                    @else
                    <span class="text-bankos-muted text-xs italic">Unknown Customer</span>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-2">
                @can('transactions.create')
                <a href="{{ route('transactions.create', ['account_number' => $account->account_number]) }}" class="btn btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Post Transaction
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Balance Cards -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="card p-6 border-l-4 border-l-bankos-success bg-gradient-to-br from-white to-green-50/30 dark:from-bankos-dark-bg dark:to-green-900/10">
                <p class="text-sm font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Available Balance</p>
                <h3 class="text-4xl font-bold text-bankos-success tracking-tight">{{ $account->currency }} {{ number_format($account->available_balance, 2) }}</h3>
                <p class="text-xs text-bankos-muted mt-4 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Real-time available funds
                </p>
            </div>
            
            <div class="card p-6 border-l-4 border-l-bankos-primary">
                <p class="text-sm font-semibold text-bankos-text-sec uppercase tracking-wider mb-2">Ledger Balance</p>
                <h3 class="text-3xl font-bold text-bankos-text dark:text-white">{{ $account->currency }} {{ number_format($account->ledger_balance, 2) }}</h3>
                <p class="text-xs text-bankos-muted mt-4 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    Total book balance including uncleared funds
                </p>
            </div>
        </div>

        <!-- Product Specs -->
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4 border-b border-bankos-border dark:border-bankos-dark-border pb-2">Product Information</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Product Name</span>
                    <span class="font-medium font-mono text-bankos-primary">{{ $account->savingsProduct?->code ?? ucfirst($account->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Interest Rate</span>
                    <span class="font-medium text-bankos-success">{{ $account->savingsProduct ? number_format($account->savingsProduct->interest_rate, 2) : '0.00' }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Min. Balance</span>
                    <span class="font-medium">{{ $account->savingsProduct ? number_format($account->savingsProduct->min_balance, 2) : '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-bankos-text-sec">Monthly Mgt Fee</span>
                    <span class="font-medium text-red-500">{{ $account->savingsProduct ? number_format($account->savingsProduct->monthly_fee, 2) : '—' }}</span>
                </div>
            </div>
            
            @can('accounts.edit')
            <div class="mt-6 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <form action="{{ route('accounts.status', $account) }}" method="POST" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $account->status === 'active' ? 'frozen' : 'active' }}">
                    <button type="submit" class="btn {{ $account->status === 'active' ? 'btn-secondary text-red-600' : 'btn-primary' }} w-full text-xs">
                        {{ $account->status === 'active' ? 'Freeze Account' : 'Unfreeze Account' }}
                    </button>
                </form>
            </div>
            @endcan
        </div>
    </div>

    <!-- Account Ledger -->
    <div class="card p-0 overflow-hidden">
        <div class="p-6 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
            <h3 class="font-bold text-lg">Transaction History (Mini-Statement)</h3>
            <button class="text-sm font-medium text-bankos-primary flex items-center gap-1 hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export PDF
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Date & Ref</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold text-right">Debit</th>
                        <th class="px-6 py-4 font-semibold text-right">Credit</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($account->transactions as $txn)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="font-medium">{{ $txn->created_at->format('d M, Y H:i') }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5 font-mono">{{ $txn->reference }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium">{{ $txn->description }}</p>
                            <span class="inline-block mt-1 bg-gray-100 dark:bg-gray-800 text-xs px-2 py-0.5 rounded text-bankos-text-sec uppercase">{{ $txn->type }}</span>
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
                            @else
                                <span class="badge badge-danger">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-muted">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-gray-300 dark:text-gray-600"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                <p class="text-lg font-medium text-bankos-text dark:text-white">No transactions found</p>
                                <p class="text-sm mt-1">This account has no ledger activity yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
