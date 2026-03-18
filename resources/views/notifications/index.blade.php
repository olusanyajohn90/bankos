<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Notification Log</h2>
                <p class="text-sm text-bankos-text-sec mt-1">All outbound notifications sent to customers</p>
            </div>
            <a href="{{ route('notifications.templates') }}" class="btn btn-secondary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Manage Templates
            </a>
        </div>
    </x-slot>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <select name="channel" class="form-input w-auto text-sm">
            <option value="">All Channels</option>
            @foreach($channels as $ch)
                <option value="{{ $ch }}" {{ request('channel') == $ch ? 'selected' : '' }}>{{ ucfirst($ch) }}</option>
            @endforeach
        </select>
        <select name="event" class="form-input w-auto text-sm">
            <option value="">All Events</option>
            @foreach($events as $key => $label)
                <option value="{{ $key }}" {{ request('event') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input w-auto text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending','sent','failed','delivered'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['channel','event','status']))
            <a href="{{ route('notifications.index') }}" class="btn btn-secondary text-sm">Clear</a>
        @endif
    </form>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Channel</th>
                        <th class="px-6 py-4 font-semibold">Recipient</th>
                        <th class="px-6 py-4 font-semibold">Event</th>
                        <th class="px-6 py-4 font-semibold">Message</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($logs as $log)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4">
                            @php
                                $chIcon = match($log->channel) {
                                    'sms'       => '💬',
                                    'whatsapp'  => '📱',
                                    'email'     => '📧',
                                    'push'      => '🔔',
                                    default     => '📨',
                                };
                            @endphp
                            <span class="badge uppercase tracking-wider text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $chIcon }} {{ $log->channel }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-mono text-bankos-text text-xs">{{ $log->recipient }}</p>
                            @if($log->customer)
                                <p class="text-xs text-bankos-text-sec mt-0.5">{{ $log->customer->first_name }} {{ $log->customer->last_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-bankos-text">{{ $events[$log->event] ?? $log->event }}</td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-bankos-text truncate text-xs">{{ $log->message }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->status === 'sent' || $log->status === 'delivered')
                                <span class="badge badge-active text-[10px] uppercase tracking-wider">{{ $log->status }}</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-red-100 text-red-700 text-[10px] uppercase tracking-wider">Failed</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-bankos-text-sec text-xs">
                            {{ $log->sent_at ? $log->sent_at->format('d M Y H:i') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="font-medium text-bankos-text">No notifications sent yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-4 border-t border-bankos-border bg-gray-50/30">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
