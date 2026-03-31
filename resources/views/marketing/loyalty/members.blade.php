<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.loyalty') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Loyalty Members</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">All customers enrolled in the loyalty program</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Tier</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Earned</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Redeemed</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($members as $member)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $member->customer?->first_name }} {{ $member->customer?->last_name }}
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $tierColors = [
                                    'bronze' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'silver' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                    'gold'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'platinum' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                ];
                                $tc = $tierColors[strtolower($member->current_tier)] ?? 'bg-bankos-bg text-bankos-text dark:bg-bankos-dark-bg dark:text-bankos-dark-text';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tc }}">{{ ucfirst($member->current_tier) }}</span>
                        </td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ number_format($member->total_earned) }}</td>
                        <td class="px-6 py-3 text-right text-bankos-text-sec dark:text-bankos-dark-text-sec">{{ number_format($member->total_redeemed) }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-bankos-text dark:text-bankos-dark-text">{{ number_format($member->current_balance) }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec">{{ $member->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-bankos-muted">No loyalty members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $members->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
