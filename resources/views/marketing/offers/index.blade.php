<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Offers & Coupons</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Create and manage promotional offers and coupon codes</p>
            </div>
            <a href="{{ route('marketing.offers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Offer
            </a>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Coupon Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Dates</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Redemptions</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($offers as $offer)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3">
                            <div class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $offer->name }}</div>
                            @if($offer->description)
                            <div class="text-xs text-bankos-muted mt-0.5">{{ Str::limit($offer->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $typeColors = [
                                    'discount' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'cashback' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'fee_waiver' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'bonus_points' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                ];
                                $typeColor = $typeColors[$offer->offer_type] ?? 'bg-bankos-bg text-bankos-text dark:bg-bankos-dark-bg dark:text-bankos-dark-text';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeColor }}">{{ str_replace('_', ' ', ucfirst($offer->offer_type)) }}</span>
                        </td>
                        <td class="px-6 py-3">
                            @if($offer->coupon_code)
                            <code class="px-2 py-0.5 bg-bankos-bg dark:bg-bankos-dark-bg rounded text-xs font-mono text-bankos-text dark:text-bankos-dark-text">{{ $offer->coupon_code }}</code>
                            @else
                            <span class="text-bankos-muted">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec text-xs">
                            {{ $offer->start_date ? $offer->start_date->format('M d') : '-' }}
                            @if($offer->end_date) - {{ $offer->end_date->format('M d, Y') }} @endif
                        </td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">
                            {{ $offer->redemptions_count }}/{{ $offer->max_redemptions ?? 'unlimited' }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <form action="{{ route('marketing.offers.toggle', $offer->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $offer->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <form action="{{ route('marketing.offers.toggle', $offer->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white text-xs">
                                    {{ $offer->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-bankos-muted">No offers yet. Create your first offer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $offers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
