<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Share Capital
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage cooperative share products, member equity, and dividends</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cooperative.shares.members') }}"
                   class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    Members
                </a>
                <a href="{{ route('cooperative.shares.purchase') }}"
                   class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Purchase Shares
                </a>
                <a href="{{ route('cooperative.shares.products.create') }}"
                   class="btn btn-primary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Share Product
                </a>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Total Share Capital --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Total Share Capital</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalShareCapital, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600 dark:text-blue-400"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>

        {{-- Members with Shares --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Members with Shares</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($membersWithShares) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600 dark:text-green-400"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
            </div>
        </div>

        {{-- Active Products --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Share Products</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalProducts) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600 dark:text-purple-400"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                </div>
            </div>
        </div>

        {{-- Avg Shares per Member --}}
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-bankos-text-sec dark:text-bankos-dark-text-sec">Avg Shares / Member</p>
                    <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $avgSharesPerMember }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 dark:text-amber-400"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Share Products Table --}}
    <div class="card p-0 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">Share Products</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Product Name</th>
                        <th class="px-5 py-4 font-semibold">Par Value</th>
                        <th class="px-5 py-4 font-semibold">Shares Issued</th>
                        <th class="px-5 py-4 font-semibold">Total Value</th>
                        <th class="px-5 py-4 font-semibold">Members</th>
                        <th class="px-5 py-4 font-semibold">Dividend Rate</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-bankos-dark-bg/30 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-xs text-bankos-muted mt-0.5">{{ Str::limit($product->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-mono">{{ number_format($product->par_value, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">{{ number_format($product->total_shares_issued) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-mono">{{ number_format($product->total_value_issued, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">{{ number_format($product->member_count) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">
                                {{ $product->dividend_rate ? number_format($product->dividend_rate, 2) . '%' : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($product->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">Active</span>
                                @elseif($product->status === 'suspended')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400">Suspended</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-400">Closed</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('cooperative.shares.products.show', $product->id) }}" class="text-bankos-primary hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-bankos-muted">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted/50"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                    <p>No share products yet.</p>
                                    <a href="{{ route('cooperative.shares.products.create') }}" class="text-bankos-primary hover:underline text-sm">Create your first share product</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Share Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Date</th>
                        <th class="px-5 py-4 font-semibold">Reference</th>
                        <th class="px-5 py-4 font-semibold">Member</th>
                        <th class="px-5 py-4 font-semibold">Product</th>
                        <th class="px-5 py-4 font-semibold">Type</th>
                        <th class="px-5 py-4 font-semibold">Qty</th>
                        <th class="px-5 py-4 font-semibold">Amount</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($recentTransactions as $txn)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-bankos-dark-bg/30 transition-colors">
                            <td class="px-5 py-4 text-sm text-bankos-muted">{{ \Carbon\Carbon::parse($txn->created_at)->format('M d, Y') }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-text dark:text-bankos-dark-text">{{ $txn->reference }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('cooperative.shares.members.show', $txn->customer_id) }}" class="text-sm text-bankos-primary hover:underline">
                                    {{ $txn->first_name }} {{ $txn->last_name }}
                                </a>
                                <div class="text-xs text-bankos-muted">{{ $txn->customer_number }}</div>
                            </td>
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
                            <td class="px-5 py-4 text-sm font-mono text-bankos-text dark:text-bankos-dark-text">{{ number_format($txn->amount, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">{{ ucfirst($txn->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-bankos-muted">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
