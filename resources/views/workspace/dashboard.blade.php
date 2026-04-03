<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Internal Workspace</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Chat, documents, support tickets, calendar & team overview</p>
            </div>
        </div>
    </x-slot>

    {{-- Row 1: Chat & Messaging --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Conversations</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalConversations) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $activeChannels }} channels</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Messages Today</p>
                    <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ number_format($messagesToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $myUnreadMessages }} unread for you</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Documents</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalDocuments) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $pendingReviewDocs }} pending review &middot; {{ $recentlyUploaded }} new (7d)</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">My Doc Actions</p>
                    <p class="text-2xl font-extrabold {{ $myDocActions > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }} mt-1">{{ number_format($myDocActions) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Pending approval/signing</p>
        </div>
    </div>

    {{-- Row 2: Support & Calendar --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Open Tickets</p>
                    <p class="text-2xl font-extrabold {{ $openTickets > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">{{ number_format($openTickets) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $resolvedToday }} resolved today &middot; {{ $myTickets }} assigned to me</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Avg Resolution</p>
                    <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $avgResolutionHours }}h</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">Last 30 days average</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Events Today</p>
                    <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ number_format($eventsToday) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $eventsThisWeek }} this week</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Team Online</p>
                    <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($onlineUsers) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <p class="text-xs text-bankos-text-sec mt-2">of {{ $totalUsers }} total users</p>
        </div>
    </div>

    {{-- Row 3: Pending Tasks & My Tasks --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
        <div class="card p-5 border-l-4 border-amber-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">My Pending Tasks</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($totalPendingTasks) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">From chat task assignments</p>
        </div>
        <div class="card p-5 border-l-4 border-blue-400">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Unread Messages</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($myUnreadMessages) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Across all conversations</p>
        </div>
    </div>

    {{-- Charts & Lists --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Message Trend --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Messages (Last 14 Days)</h3>
            <canvas id="messageTrendChart" height="200"></canvas>
        </div>

        {{-- Tickets by Status --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Support Tickets by Status</h3>
            <canvas id="ticketStatusChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Documents by Status --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Documents by Status</h3>
            <canvas id="docStatusChart" height="200"></canvas>
        </div>

        {{-- Upcoming Events --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Upcoming Events</h3>
            <div class="space-y-3">
                @forelse($upcomingEvents as $event)
                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-xs font-bold">
                        {{ \Carbon\Carbon::parse($event->start_at)->format('d') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text truncate">{{ $event->title ?? 'Untitled Event' }}</p>
                        <p class="text-xs text-bankos-text-sec">{{ \Carbon\Carbon::parse($event->start_at)->format('D, d M \a\t H:i') }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No upcoming events.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Announcements & Tasks --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Announcements --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Recent Announcements</h3>
            <div class="space-y-3">
                @forelse($announcements as $ann)
                <div class="p-3 rounded-lg {{ ($ann->is_pinned ?? false) ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' : 'bg-gray-50 dark:bg-bankos-dark-bg' }}">
                    <div class="flex items-center gap-2">
                        @if($ann->is_pinned ?? false)
                        <svg class="w-3.5 h-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                        @endif
                        <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $ann->title ?? 'Announcement' }}</p>
                    </div>
                    <p class="text-xs text-bankos-text-sec mt-1">{{ \Illuminate\Support\Str::limit($ann->body ?? '', 100) }}</p>
                    <p class="text-xs text-bankos-text-sec mt-1">{{ \Carbon\Carbon::parse($ann->created_at)->diffForHumans() }}</p>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No announcements.</p>
                @endforelse
            </div>
        </div>

        {{-- Active Tasks --}}
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">My Active Tasks</h3>
            <div class="space-y-2">
                @forelse($activeTasks as $task)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text truncate">{{ $task->title ?? 'Untitled' }}</p>
                        @if($task->due_at ?? null)
                        <p class="text-xs {{ \Carbon\Carbon::parse($task->due_at)->isPast() ? 'text-red-600 font-semibold' : 'text-bankos-text-sec' }}">
                            Due: {{ \Carbon\Carbon::parse($task->due_at)->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                </div>
                @empty
                <p class="text-sm text-bankos-text-sec text-center py-4">No pending tasks.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('messageTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($messageTrend->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))) !!},
                datasets: [{
                    label: 'Messages',
                    data: {!! json_encode($messageTrend->pluck('total')) !!},
                    borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true, tension: 0.3, pointRadius: 3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('ticketStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($ticketsByStatus->keys()) !!},
                datasets: [{
                    data: {!! json_encode($ticketsByStatus->values()) !!},
                    backgroundColor: ['#ef4444', '#f59e0b', '#8b5cf6', '#22c55e', '#6b7280', '#3b82f6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('docStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($docsByStatus->keys()) !!},
                datasets: [{
                    data: {!! json_encode($docsByStatus->values()) !!},
                    backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e', '#ef4444', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endpush
</x-app-layout>
