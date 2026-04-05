<x-app-layout>
    <x-slot name="header">Perpetual KYC Monitoring</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Open Events</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $openCount }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">In Review</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $reviewCount }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Resolved</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $resolvedCount }}</p>
            </div>
        </div>

        {{-- Events Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">KYC Events</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Event Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Description</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Action Required</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($events as $e)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3">
                                <span class="font-medium">{{ $e->customer->first_name ?? '' }} {{ $e->customer->last_name ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $e->event_type)) }}</td>
                            <td class="px-4 py-3 text-xs max-w-xs truncate">{{ $e->description }}</td>
                            <td class="px-4 py-3">
                                @php $ac = match($e->action_required) { 'enhanced_due_diligence' => 'bg-red-100 text-red-700', 'sar_filing' => 'bg-red-100 text-red-700', 'account_restriction' => 'bg-orange-100 text-orange-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ac }}">{{ ucfirst(str_replace('_', ' ', $e->action_required)) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php $stc = match($e->status) { 'open' => 'bg-red-100 text-red-700', 'in_review' => 'bg-yellow-100 text-yellow-700', 'resolved' => 'bg-green-100 text-green-700', default => 'bg-purple-100 text-purple-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ ucfirst(str_replace('_', ' ', $e->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $e->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                @if($e->status !== 'resolved')
                                <form method="POST" action="{{ route('compliance-auto.kyc-monitoring.resolve', $e->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">Resolve</button>
                                </form>
                                @else
                                <span class="text-xs text-bankos-muted">{{ $e->resolved_at?->format('M d') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No KYC events found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
