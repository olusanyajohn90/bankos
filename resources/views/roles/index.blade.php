<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Roles & Permissions') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage user roles and granular access control</p>
            </div>
            
            <a href="{{ route('roles.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Custom Role
            </a>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Role Name</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold">Users Assigned</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($roles as $role)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-text">{{ ucwords(str_replace('_', ' ', $role->name)) }}</p>
                            @if(count($role->permissions) > 0)
                                <p class="text-xs text-bankos-text-sec mt-1 truncate max-w-sm">{{ count($role->permissions) }} permissions assigned</p>
                            @else
                                <p class="text-xs text-red-500 mt-1">No permissions assigned</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if(is_null($role->tenant_id))
                                <span class="badge uppercase tracking-wider text-[10px] bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-300">System Standard</span>
                            @else
                                <span class="badge uppercase tracking-wider text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Custom Role</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-bankos-text">{{ $role->users()->count() }} users</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-3">
                                @if(is_null($role->tenant_id))
                                    <span class="text-gray-400 text-sm italic">Locked</span>
                                @else
                                    <a href="{{ route('roles.edit', $role) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm transition-colors">Edit</a>
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this custom role? This will severely affect users currently assigned to it.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-bankos-text-sec">
                            <p class="font-medium text-bankos-text">No roles found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($roles->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
