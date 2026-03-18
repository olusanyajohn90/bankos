<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('roles.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Create Custom Role
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Define a new functional role by selecting specific permissions</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-6 md:p-8 max-w-5xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ route('roles.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Role Basics -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-bankos-text border-b border-gray-100 dark:border-gray-800 pb-2">Role Name</h3>
                
                <div class="max-w-md">
                    <label for="name" class="form-label">Role Title <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required placeholder="e.g. Call Center Agent">
                    <p class="form-hint">Name this role based on the job function.</p>
                </div>
            </div>

            <!-- Permissions Mapping -->
            <div class="space-y-4">
                <div class="flex justify-between items-end border-b border-gray-100 dark:border-gray-800 pb-2">
                    <h3 class="text-lg font-semibold text-bankos-text">Assign Permissions</h3>
                    <p class="text-xs text-bankos-text-sec">Check the boxes for actions this role can perform.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-4">
                    @foreach($permissions as $group => $groupPermissions)
                        <div class="bg-gray-50/50 dark:bg-bankos-dark-bg/20 p-4 rounded-lg border border-gray-100 dark:border-gray-800">
                            <h4 class="font-semibold text-bankos-primary uppercase text-xs tracking-wider mb-3">{{ $group }}</h4>
                            <div class="space-y-2">
                                @foreach($groupPermissions as $permission)
                                    <label class="flex items-start gap-3 cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="w-4 h-4 text-bankos-primary bg-white border-gray-300 rounded focus:ring-bankos-primary dark:focus:ring-bankos-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 rounded-sm transition-colors"
                                            {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-bankos-primary transition-colors">
                                                {{ ucwords(str_replace(['.', '_'], ' ', $permission->name)) }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-500 font-mono">{{ $permission->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                    Create Role
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
