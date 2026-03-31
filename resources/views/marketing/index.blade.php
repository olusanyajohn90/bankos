<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Marketing Dashboard</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Campaign performance and customer engagement overview</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('marketing.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    New Campaign
                </a>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600 dark:text-blue-400" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Total Campaigns</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalCampaigns) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-600 dark:text-green-400" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Active Campaigns</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($activeCampaigns) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-600 dark:text-purple-400" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Recipients Reached</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalSent) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-600 dark:text-amber-400" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Delivery Rate</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $deliveryRate }}%</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600 dark:text-rose-400" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <div>
                    <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec">Cross-sell Opps</p>
                    <p class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($crossSellCount) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Campaigns --}}
        <div class="lg:col-span-2 bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Campaigns</h3>
                <a href="{{ route('marketing.campaigns') }}" class="text-sm text-bankos-primary hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Channel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Recipients</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Sent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($recentCampaigns as $campaign)
                        <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-6 py-3">
                                <a href="{{ route('marketing.campaigns.show', $campaign->id) }}" class="font-medium text-bankos-primary hover:underline">{{ $campaign->name }}</a>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $campaign->channel === 'sms' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($campaign->channel === 'email' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                                    {{ strtoupper($campaign->channel) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @include('marketing._status-badge', ['status' => $campaign->status])
                            </td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->total_recipients) }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->sent_count) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-bankos-muted">No campaigns yet. Create your first campaign to get started.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions + Top Segments --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('marketing.campaigns.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-bankos-bg dark:bg-bankos-dark-bg hover:bg-bankos-light dark:hover:bg-bankos-dark-border transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-bankos-primary" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">New Campaign</span>
                    </a>
                    <a href="{{ route('marketing.segments.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-bankos-bg dark:bg-bankos-dark-bg hover:bg-bankos-light dark:hover:bg-bankos-dark-border transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-bankos-primary" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line></svg>
                        <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">New Segment</span>
                    </a>
                    <form action="{{ route('marketing.cross-sells.generate') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-bankos-bg dark:bg-bankos-dark-bg hover:bg-bankos-light dark:hover:bg-bankos-dark-border transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-bankos-primary" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">Generate Cross-sells</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Top Segments</h3>
                    <a href="{{ route('marketing.segments') }}" class="text-sm text-bankos-primary hover:underline">View all</a>
                </div>
                <div class="space-y-3">
                    @forelse($topSegments as $segment)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $segment->name }}</span>
                        <span class="text-sm font-semibold text-bankos-muted">{{ number_format($segment->cached_count) }}</span>
                    </div>
                    @empty
                    <p class="text-sm text-bankos-muted">No segments created yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
