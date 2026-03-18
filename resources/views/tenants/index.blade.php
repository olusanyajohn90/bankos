<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Institutions (Tenants)') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Super Admin only: Manage multi-tenant banking institutions</p>
            </div>
            
            @if(auth()->user()->hasRole('super_admin'))
            <a href="{{ route('tenants.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline><path d="M12 2v2"></path><path d="M12 8v2"></path></svg>
                New Institution
            </a>
            @endif
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Tenant ID / Name</th>
                        <th class="px-6 py-4 font-semibold">Institution Code</th>
                        <th class="px-6 py-4 font-semibold">Domain Map</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($tenants as $tenant)
                    <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-text text-lg">{{ $tenant->name }}</p>
                            <p class="text-[10px] text-bankos-text-sec mt-0.5 font-mono">{{ $tenant->id }}</p>
                            @if(session('tenant_id') === $tenant->id)
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">Currently Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-bankos-primary font-bold tracking-widest bg-gray-50 border border-gray-200 px-2.5 py-1 rounded dark:bg-bankos-dark-bg/50 dark:border-bankos-dark-border">
                                {{ $tenant->institution_code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->domain)
                                <a href="https://{{ $tenant->domain }}" target="_blank" class="text-bankos-primary hover:underline font-mono">{{ $tenant->domain }}</a>
                            @else
                                <span class="text-xs text-bankos-text-sec italic">No domain mapped</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->status === 'active')
                                <span class="badge badge-active uppercase tracking-wider text-[10px]">Active</span>
                            @else
                                <span class="badge badge-inactive uppercase tracking-wider text-[10px] bg-red-100 text-red-700">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('tenants.edit', $tenant) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm transition-colors">Edit</a>
                                
                                @if(session('tenant_id') !== $tenant->id)
                                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete this institution? This will cascade and delete EVERYTHING associated with it.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">Delete DB</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec">
                            <p class="mb-4 font-medium text-bankos-text">No tenants found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tenants->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
