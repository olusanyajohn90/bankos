<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Campaigns</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Manage and monitor your marketing campaigns</p>
            </div>
            <a href="{{ route('marketing.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Campaign
            </a>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">Status</label>
                <select name="status" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All</option>
                    @foreach(['draft','scheduled','sending','sent','paused','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">Channel</label>
                <select name="channel" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All</option>
                    <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
            </div>
            <button type="submit" class="px-4 py-2 bg-bankos-primary text-white text-sm rounded-lg hover:bg-bankos-primary/90">Filter</button>
            <a href="{{ route('marketing.campaigns') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Recipients</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Sent</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Delivered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3">
                            <a href="{{ route('marketing.campaigns.show', $campaign->id) }}" class="font-medium text-bankos-primary hover:underline">{{ $campaign->name }}</a>
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec capitalize">{{ str_replace('_', ' ', $campaign->type) }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $campaign->channel === 'sms' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ($campaign->channel === 'email' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400') }}">
                                {{ strtoupper($campaign->channel) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">@include('marketing._status-badge', ['status' => $campaign->status])</td>
                        <td class="px-6 py-3 text-right">{{ number_format($campaign->total_recipients) }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($campaign->sent_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($campaign->delivered_count) }}</td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $campaign->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(in_array($campaign->status, ['draft', 'scheduled', 'paused']))
                                <form action="{{ route('marketing.campaigns.send', $campaign->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400" title="Send">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('marketing.campaigns.duplicate', $campaign->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white" title="Duplicate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-2">No campaigns found.</p>
                            <a href="{{ route('marketing.campaigns.create') }}" class="text-bankos-primary hover:underline">Create your first campaign</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
