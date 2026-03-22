<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ $customer->first_name }} {{ $customer->last_name }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Share portfolio for {{ $customer->customer_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cooperative.shares.purchase') }}" class="btn btn-primary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Purchase Shares
                </a>
                <a href="{{ route('cooperative.shares.members') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to Members
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Total Shares Held</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($summary->total_shares) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-400"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Total Equity Value</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($summary->total_value, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600 dark:text-green-400"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Share Holdings --}}
    <div class="card p-0 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">Share Holdings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Product</th>
                        <th class="px-5 py-4 font-semibold">Quantity</th>
                        <th class="px-5 py-4 font-semibold">Par Value</th>
                        <th class="px-5 py-4 font-semibold">Total Value</th>
                        <th class="px-5 py-4 font-semibold">Certificate</th>
                        <th class="px-5 py-4 font-semibold">Purchase Date</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($holdings as $holding)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-bankos-dark-bg/30 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('cooperative.shares.products.show', $holding->share_product_id) }}" class="text-sm text-bankos-primary hover:underline font-medium">
                                    {{ $holding->product_name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-semibold">{{ number_format($holding->quantity) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-muted font-mono">{{ number_format($holding->par_value, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-mono font-semibold">{{ number_format($holding->total_value, 2) }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-muted">{{ $holding->certificate_number ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-muted">{{ \Carbon\Carbon::parse($holding->purchase_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                @if($holding->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">Active</span>
                                @elseif($holding->status === 'redeemed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-400">Redeemed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">Transferred</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($holding->status === 'active' && $holding->certificate_number)
                                    <a href="{{ route('cooperative.shares.certificate', $holding->id) }}" class="text-bankos-primary hover:underline text-sm" target="_blank">Certificate</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-bankos-muted">This member has no share holdings.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Date</th>
                        <th class="px-5 py-4 font-semibold">Reference</th>
                        <th class="px-5 py-4 font-semibold">Product</th>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">Qty</th>
                        <th class="px-5 py-4 font-semibold">Unit Price</th>
                        <th class="px-5 py-4 font-semibold">Amount</th>
                        <th class="px-5 py-4 font-semibold">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($transactions as $txn)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-bankos-dark-bg/30 transition-colors">
                            <td class="px-5 py-4 text-sm text-bankos-muted">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-text dark:text-bankos-dark-text">{{ $txn->reference }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">{{ $txn->product_name }}</td>
                            <td class="px-5 py-4">
                                @if($txn->type === 'purchase')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">Purchase</span>
                                @elseif($txn->type === 'redemption')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400">Redemption</span>
                                @elseif($txn->type === 'dividend')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400">Dividend</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $txn->type)) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">{{ number_format($txn->quantity) }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-muted">{{ number_format($txn->unit_price, 2) }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-text dark:text-bankos-dark-text font-semibold">{{ number_format($txn->amount, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-muted">{{ Str::limit($txn->notes, 40) ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-bankos-muted">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
