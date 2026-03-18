<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Users & Roles') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage system administrators, bank staff, and their permissions</p>
            </div>
            
            @can('users.create')
            <a href="{{ route('users.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                New User
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Roles</th>
                        <th class="px-6 py-4 font-semibold">Branch</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-bankos-primary/10 text-bankos-primary flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-bankos-text">{{ $user->name }}</p>
                                    <p class="text-xs text-bankos-text-sec mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($user->roles as $role)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $role->name === 'super_admin' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-200 text-gray-700' }}">
                                        {{ str_replace('_', ' ', $role->name) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-bankos-text-sec italic">No roles assigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->branch)
                                <span class="font-medium text-bankos-text">{{ $user->branch->name }}</span>
                            @else
                                <span class="text-gray-400 dark:text-gray-600 text-xs italic">HQ / All Branches</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->status === 'active')
                                <span class="badge badge-active uppercase tracking-wider text-[10px]">Active</span>
                            @else
                                <span class="badge badge-inactive uppercase tracking-wider text-[10px] bg-red-100 text-red-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('users.edit')
                            <div class="flex justify-end items-center gap-3">
                                @if(auth()->user()->hasRole('super_admin') || !$user->hasRole('super_admin'))
                                    <a href="{{ route('users.edit', $user) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm transition-colors">Edit</a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user account?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">Delete</button>
                                    </form>
                                    @endif
                                @else
                                    <span class="text-xs text-bankos-text-sec italic" title="You cannot edit Super Admins">Restricted</span>
                                @endif
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec">
                            <p class="mb-4 font-medium text-bankos-text">No users found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
