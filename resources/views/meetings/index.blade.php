<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('groups.show', $group) }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Meetings — {{ $group->name }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-1">All recorded meetings and collections</p>
                </div>
            </div>
            <a href="{{ route('groups.meetings.create', $group) }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Schedule Meeting
            </a>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Location</th>
                        <th class="px-6 py-4 font-semibold">Conducted By</th>
                        <th class="px-6 py-4 font-semibold">Attendance</th>
                        <th class="px-6 py-4 font-semibold">Collected</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($meetings as $meeting)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-text">{{ $meeting->meeting_date->format('d M Y') }}</p>
                            @if($meeting->meeting_time)<p class="text-xs text-bankos-text-sec">{{ \Carbon\Carbon::parse($meeting->meeting_time)->format('g:i A') }}</p>@endif
                        </td>
                        <td class="px-6 py-4 text-bankos-text">{{ $meeting->location ?? '—' }}</td>
                        <td class="px-6 py-4 text-bankos-text">{{ $meeting->conductedBy ? $meeting->conductedBy->name : '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="text-bankos-primary font-semibold">{{ $meeting->present_count }}</span>
                            <span class="text-bankos-text-sec text-xs">/ {{ $meeting->attendances_count }}</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-bankos-text">₦{{ number_format($meeting->total_collected, 0) }}</td>
                        <td class="px-6 py-4">
                            @if($meeting->status === 'completed')
                                <span class="badge badge-active text-[10px] uppercase tracking-wider">Completed</span>
                            @elseif($meeting->status === 'cancelled')
                                <span class="badge bg-red-100 text-red-700 text-[10px] uppercase tracking-wider">Cancelled</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider">Scheduled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('groups.meetings.show', [$group, $meeting]) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">
                                {{ $meeting->status === 'scheduled' ? 'Record' : 'View' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <p class="font-medium text-bankos-text">No meetings scheduled yet.</p>
                            <a href="{{ route('groups.meetings.create', $group) }}" class="btn btn-primary shadow-sm mt-3 inline-block">Schedule First Meeting</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($meetings->hasPages())
        <div class="p-4 border-t border-bankos-border bg-gray-50/30">{{ $meetings->links() }}</div>
        @endif
    </div>
</x-app-layout>
