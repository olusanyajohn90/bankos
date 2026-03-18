<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Bank Branches') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage physical and virtual branches</p>
            </div>
            
            @can('branches.create')
            <a href="{{ route('branches.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Branch
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Branch Name</th>
                        <th class="px-6 py-4 font-semibold">Code/Routing</th>
                        <th class="px-6 py-4 font-semibold">Location</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($branches as $branch)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('branches.show', $branch) }}"
                               class="font-bold text-bankos-primary hover:underline">{{ $branch->name }}</a>
                            @if($branch->manager)
                                <p class="text-xs text-bankos-text-sec mt-1">Manager: {{ $branch->manager->name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-mono text-bankos-primary font-bold">{{ $branch->code }}</span>
                                @if($branch->routing_number)
                                <span class="text-xs text-bankos-text-sec font-mono">Routing: {{ $branch->routing_number }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-bankos-text font-medium">{{ $branch->city }}</p>
                            @if($branch->local_government)
                                <p class="text-xs text-bankos-text-sec mt-0.5">{{ $branch->local_government }}, {{ $branch->state }}</p>
                            @else
                                <p class="text-xs text-bankos-text-sec mt-0.5">{{ $branch->state }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($branch->status === 'active')
                                <span class="badge badge-active uppercase tracking-wider text-[10px]">Active</span>
                            @else
                                <span class="badge badge-inactive uppercase tracking-wider text-[10px] bg-red-100 text-red-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('branches.edit')
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('branches.edit', $branch) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm transition-colors">Edit</a>
                                <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('Delete this branch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">Delete</button>
                                </form>
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="mb-4 font-medium text-bankos-text">No branches configured.</p>
                                @can('branches.create')
                                <a href="{{ route('branches.create') }}" class="btn btn-primary shadow-sm hover:-translate-y-0.5 transition-transform">Create First Branch</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($branches->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $branches->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
