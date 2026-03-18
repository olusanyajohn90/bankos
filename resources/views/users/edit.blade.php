<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    {{ isset($user) ? 'Edit User: ' . $user->name : 'Create New User' }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure staff account, passwords, and security roles</p>
            </div>
        </div>
    </x-slot>

    <div class="card max-w-4xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-bankos-text border-b border-gray-100 dark:border-gray-800 pb-2">Profile Details</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="form-label">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', isset($user) ? explode(' ', $user->name, 2)[0] : '') }}" class="form-input" required>
                        </div>
                        <div>
                            <label for="last_name" class="form-label">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', isset($user) ? (explode(' ', $user->name, 2)[1] ?? '') : '') }}" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" class="form-input" required>
                    </div>

                    <div>
                        <label for="status" class="form-label">Account Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                        </select>
                    </div>

                    <div>
                         <label for="branch_id" class="form-label">Assigned Branch (Optional)</label>
                         <select name="branch_id" id="branch_id" class="form-select">
                             <option value="">-- HQ / All Branches --</option>
                             @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->code }} - {{ $branch->name }}
                                </option>
                             @endforeach
                         </select>
                         <p class="form-hint">Restrict operations to a specific branch if necessary.</p>
                    </div>
                </div>

                <!-- Security -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-bankos-text border-b border-gray-100 dark:border-gray-800 pb-2">Security & Roles</h3>
                    
                    <div>
                        <label for="password" class="form-label">Password {{ isset($user) ? '(Leave blank to keep current)' : '<span class="text-red-500">*</span>' !!}</label>
                        <input type="password" name="password" id="password" class="form-input" {{ isset($user) ? '' : 'required' }}>
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" {{ isset($user) ? '' : 'required' }}>
                    </div>

                    <div>
                        <label class="form-label">Assigned Roles <span class="text-red-500">*</span></label>
                        <div class="mt-2 space-y-2 bg-gray-50 dark:bg-bankos-dark-bg/50 p-3 rounded-lg border border-bankos-border">
                            @php
                                $userRoles = isset($user) ? $user->roles->pluck('name')->toArray() : [];
                            @endphp
                            @foreach($roles as $role)
                                <div class="flex items-center">
                                    <input type="checkbox" name="roles[]" id="role_{{ $role->id }}" value="{{ $role->name }}" 
                                        class="rounded border-gray-300 text-bankos-primary shadow-sm focus:ring-bankos-primary"
                                        {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
                                    <label for="role_{{ $role->id }}" class="ml-2 text-sm text-bankos-text font-medium uppercase tracking-wider">
                                        {{ str_replace('_', ' ', $role->name) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('roles')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                    {{ isset($user) ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
