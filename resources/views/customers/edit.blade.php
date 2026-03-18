<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Edit Customer Profile
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <a href="{{ route('customers.index') }}" class="hover:text-bankos-primary">Customers</a>
                    <span>/</span>
                    <a href="{{ route('customers.show', $customer) }}" class="hover:text-bankos-primary">{{ $customer->customer_number }}</a>
                    <span>/</span>
                    <span class="text-bankos-text dark:text-white font-medium">Edit</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="card p-8">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 text-red-600 p-4 rounded-lg flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <div>
                        <h4 class="font-bold text-sm">Please fix the following errors</h4>
                        <ul class="text-xs list-disc list-inside mt-1 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="pb-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <h3 class="font-bold text-base text-bankos-text dark:text-white">Contact Details</h3>
                    <p class="text-xs text-bankos-text-sec mt-1">Basic profile fields. For KYC/identity changes use the KYC tab.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $customer->first_name) }}"
                               class="form-input w-full @error('first_name') border-red-500 @enderror"
                               placeholder="John" required>
                        @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Last Name (Surname) <span class="text-red-500">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $customer->last_name) }}"
                               class="form-input w-full @error('last_name') border-red-500 @enderror"
                               placeholder="Doe" required>
                        @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                               class="form-input w-full @error('phone') border-red-500 @enderror"
                               placeholder="+234..." required>
                        @error('phone')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                               class="form-input w-full @error('email') border-red-500 @enderror"
                               placeholder="john.doe@example.com">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        <p class="text-xs text-bankos-muted mt-1">Required for portal access.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
