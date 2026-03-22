<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ $product->name }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Share product details and member holdings</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cooperative.shares.purchase') }}" class="btn btn-primary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Purchase Shares
                </a>
                <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Product Details + Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Product Info --}}
        <div class="card p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Product Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-bankos-muted">Par Value</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text font-mono">{{ number_format($product->par_value, 2) }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Dividend Rate</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $product->dividend_rate ? number_format($product->dividend_rate, 2) . '%' : 'Discretionary' }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Min Shares</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ number_format($product->min_shares) }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Max Shares</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $product->max_shares ? number_format($product->max_shares) : 'Unlimited' }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Transferable</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $product->transferable ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Redeemable</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $product->redeemable ? 'Yes' : 'No' }}</p>
                </div>
                <div>
                    <p class="text-bankos-muted">Status</p>
                    <p>
                        @if($product->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">Active</span>
                        @elseif($product->status === 'suspended')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400">Suspended</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-400">Closed</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-bankos-muted">Created</p>
                    <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ \Carbon\Carbon::parse($product->created_at)->format('M d, Y') }}</p>
                </div>
            </div>
            @if($product->description)
                <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <p class="text-bankos-muted text-sm">{{ $product->description }}</p>
                </div>
            @endif
        </div>

        {{-- Summary Stats --}}
        <div class="space-y-4">
            <div class="card p-6">
                <p class="text-sm text-bankos-muted">Total Shares Issued</p>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($summary->total_shares) }}</p>
            </div>
            <div class="card p-6">
                <p class="text-sm text-bankos-muted">Total Value</p>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($summary->total_value, 2) }}</p>
            </div>
            <div class="card p-6">
                <p class="text-sm text-bankos-muted">Members Holding</p>
                <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($summary->member_count) }}</p>
            </div>
        </div>
    </div>

    {{-- Member Holdings Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="text-lg font-semibold text-bankos-text dark:text-bankos-dark-text">Member Holdings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Member</th>
                        <th class="px-5 py-4 font-semibold">Customer #</th>
                        <th class="px-5 py-4 font-semibold">Quantity</th>
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
                                <a href="{{ route('cooperative.shares.members.show', $holding->customer_id) }}" class="text-sm text-bankos-primary hover:underline font-medium">
                                    {{ $holding->first_name }} {{ $holding->last_name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-sm text-bankos-muted font-mono">{{ $holding->customer_number }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text">{{ number_format($holding->quantity) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-mono">{{ number_format($holding->total_value, 2) }}</td>
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
                            <td colspan="8" class="px-5 py-12 text-center text-bankos-muted">No members hold this share product yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($holdings->hasPages())
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $holdings->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
