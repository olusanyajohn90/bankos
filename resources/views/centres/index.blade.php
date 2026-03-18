<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Centres</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage meeting centres for group lending</p>
            </div>
            <a href="{{ route('centres.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Centre
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Centre Name</th>
                        <th class="px-6 py-4 font-semibold">Branch</th>
                        <th class="px-6 py-4 font-semibold">Meeting Schedule</th>
                        <th class="px-6 py-4 font-semibold">Groups</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($centres as $centre)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-text">{{ $centre->name }}</p>
                            @if($centre->code)<p class="text-xs text-bankos-text-sec font-mono mt-0.5">{{ $centre->code }}</p>@endif
                        </td>
                        <td class="px-6 py-4 text-bankos-text">{{ $centre->branch?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($centre->meeting_day)
                                <p class="text-bankos-text capitalize">{{ $centre->meeting_day }}</p>
                                @if($centre->meeting_time)<p class="text-xs text-bankos-text-sec">{{ \Carbon\Carbon::parse($centre->meeting_time)->format('g:i A') }}</p>@endif
                                @if($centre->meeting_location)<p class="text-xs text-bankos-text-sec truncate max-w-[180px]">{{ $centre->meeting_location }}</p>@endif
                            @else
                                <span class="text-bankos-text-sec">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-bankos-primary">{{ $centre->groups->count() }}</span>
                            <span class="text-bankos-text-sec text-xs ml-1">groups</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($centre->status === 'active')
                                <span class="badge badge-active uppercase tracking-wider text-[10px]">Active</span>
                            @else
                                <span class="badge badge-inactive uppercase tracking-wider text-[10px] bg-red-100 text-red-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('centres.show', $centre) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                                <a href="{{ route('centres.edit', $centre) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">Edit</a>
                                <form action="{{ route('centres.destroy', $centre) }}" method="POST" onsubmit="return confirm('Delete this centre?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="font-medium text-bankos-text">No centres configured yet.</p>
                                <a href="{{ route('centres.create') }}" class="btn btn-primary shadow-sm">Create First Centre</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($centres->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30">{{ $centres->links() }}</div>
        @endif
    </div>
</x-app-layout>
