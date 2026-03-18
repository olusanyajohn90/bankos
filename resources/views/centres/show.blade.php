<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('centres.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $centre->name }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-1">
                        @if($centre->meeting_day){{ ucfirst($centre->meeting_day) }}s @endif
                        @if($centre->meeting_location)· {{ $centre->meeting_location }}@endif
                    </p>
                </div>
            </div>
            <a href="{{ route('centres.edit', $centre) }}" class="btn btn-secondary">Edit Centre</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <!-- Groups in this Centre -->
    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="px-6 py-4 border-b border-bankos-border flex justify-between items-center">
            <h3 class="font-semibold text-bankos-text">Groups ({{ $centre->groups->count() }})</h3>
            <a href="{{ route('groups.create') }}" class="btn btn-primary text-sm">+ New Group</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Group Name</th>
                        <th class="px-6 py-3 font-semibold">Loan Officer</th>
                        <th class="px-6 py-3 font-semibold">Members</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border">
                    @forelse($centre->groups as $group)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-3 font-semibold text-bankos-text">{{ $group->name }}</td>
                        <td class="px-6 py-3 text-bankos-text">{{ $group->loanOfficer?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-bankos-primary font-semibold">{{ $group->members->count() }}</td>
                        <td class="px-6 py-3">
                            @if($group->status === 'active')
                                <span class="badge badge-active text-[10px] uppercase">Active</span>
                            @elseif($group->status === 'dissolved')
                                <span class="badge bg-gray-100 text-gray-600 text-[10px] uppercase">Dissolved</span>
                            @else
                                <span class="badge badge-inactive text-[10px] uppercase bg-red-100 text-red-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('groups.show', $group) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-bankos-text-sec">No groups in this centre yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
