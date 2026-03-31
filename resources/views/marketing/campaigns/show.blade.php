<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('marketing.campaigns') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $campaign->name }}</h1>
                    <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">{{ $campaign->description ?? 'No description' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array($campaign->status, ['draft', 'scheduled', 'paused']))
                <form action="{{ route('marketing.campaigns.send', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        Send Now
                    </button>
                </form>
                @endif
                @if($campaign->status === 'sending')
                <form action="{{ route('marketing.campaigns.pause', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                        Pause
                    </button>
                </form>
                @endif
                <form action="{{ route('marketing.campaigns.duplicate', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border text-sm font-medium rounded-lg hover:bg-bankos-bg dark:hover:bg-bankos-dark-bg transition-colors text-bankos-text dark:text-bankos-dark-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        Duplicate
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    {{-- Campaign Info --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div>
                <p class="text-xs text-bankos-muted mb-1">Status</p>
                @include('marketing._status-badge', ['status' => $campaign->status])
            </div>
            <div>
                <p class="text-xs text-bankos-muted mb-1">Channel</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $campaign->channel === 'sms' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($campaign->channel === 'email' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                    {{ strtoupper($campaign->channel) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-bankos-muted mb-1">Type</p>
                <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text capitalize">{{ str_replace('_', ' ', $campaign->type) }}</p>
            </div>
            <div>
                <p class="text-xs text-bankos-muted mb-1">Segment</p>
                <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $campaign->segment?->name ?? 'All Customers' }}</p>
            </div>
            <div>
                <p class="text-xs text-bankos-muted mb-1">Created</p>
                <p class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $campaign->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-bankos-muted mb-1">Sent At</p>
                <p class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $campaign->sent_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        @php
        $stats = [
            ['label' => 'Total', 'value' => $campaign->total_recipients, 'color' => 'blue'],
            ['label' => 'Sent', 'value' => $campaign->sent_count, 'color' => 'indigo'],
            ['label' => 'Delivered', 'value' => $campaign->delivered_count, 'color' => 'green'],
            ['label' => 'Opened', 'value' => $campaign->opened_count, 'color' => 'purple'],
            ['label' => 'Clicked', 'value' => $campaign->clicked_count, 'color' => 'amber'],
            ['label' => 'Converted', 'value' => $campaign->converted_count, 'color' => 'emerald'],
            ['label' => 'Failed', 'value' => $campaign->failed_count, 'color' => 'red'],
        ];
        @endphp
        @foreach($stats as $stat)
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4 text-center">
            <p class="text-xs text-bankos-muted mb-1">{{ $stat['label'] }}</p>
            <p class="text-xl font-bold text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400">{{ number_format($stat['value']) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Delivery Funnel --}}
    @if($campaign->total_recipients > 0)
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Delivery Funnel</h3>
        @php
        $total = max($campaign->total_recipients, 1);
        $funnelData = [
            ['label' => 'Recipients', 'value' => $campaign->total_recipients, 'pct' => 100, 'color' => 'bg-blue-500'],
            ['label' => 'Sent', 'value' => $campaign->sent_count, 'pct' => round(($campaign->sent_count / $total) * 100, 1), 'color' => 'bg-indigo-500'],
            ['label' => 'Delivered', 'value' => $campaign->delivered_count, 'pct' => round(($campaign->delivered_count / $total) * 100, 1), 'color' => 'bg-green-500'],
            ['label' => 'Opened', 'value' => $campaign->opened_count, 'pct' => round(($campaign->opened_count / $total) * 100, 1), 'color' => 'bg-purple-500'],
            ['label' => 'Clicked', 'value' => $campaign->clicked_count, 'pct' => round(($campaign->clicked_count / $total) * 100, 1), 'color' => 'bg-amber-500'],
            ['label' => 'Converted', 'value' => $campaign->converted_count, 'pct' => round(($campaign->converted_count / $total) * 100, 1), 'color' => 'bg-emerald-500'],
        ];
        @endphp
        <div class="space-y-3">
            @foreach($funnelData as $step)
            <div class="flex items-center gap-4">
                <div class="w-24 text-sm text-bankos-text dark:text-bankos-dark-text">{{ $step['label'] }}</div>
                <div class="flex-1 bg-bankos-bg dark:bg-bankos-dark-bg rounded-full h-6 overflow-hidden">
                    <div class="{{ $step['color'] }} h-full rounded-full flex items-center justify-end px-2 text-xs text-white font-medium transition-all" style="width: {{ max($step['pct'], 2) }}%">
                        {{ $step['pct'] }}%
                    </div>
                </div>
                <div class="w-20 text-right text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ number_format($step['value']) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recipients Table --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recipients</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Sent At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Delivered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($recipients as $recipient)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $recipient->customer?->full_name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $recipient->channel_address }}</td>
                        <td class="px-6 py-3">
                            @php
                            $rStatusClasses = match($recipient->status) {
                                'queued'       => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                'sent'         => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'delivered'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'opened'       => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                'clicked'      => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'converted'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'failed'       => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                'unsubscribed' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                default        => 'bg-gray-100 text-gray-700',
                            };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $rStatusClasses }}">{{ ucfirst($recipient->status) }}</span>
                        </td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $recipient->sent_at?->format('d M H:i') ?? '-' }}</td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $recipient->delivered_at?->format('d M H:i') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-bankos-muted">No recipients for this campaign.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recipients->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $recipients->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
