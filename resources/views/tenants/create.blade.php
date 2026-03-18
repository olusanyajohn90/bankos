<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tenants.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    {{ isset($tenant) ? 'Edit Institution: ' . $tenant->name : 'Onboard New Institution' }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure tenant database routing and branding</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-6 md:p-8 max-w-2xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ isset($tenant) ? route('tenants.update', $tenant) : route('tenants.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($tenant))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="name" class="form-label">Institution Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name ?? '') }}" class="form-input" required placeholder="e.g. Acme Microfinance Bank">
                </div>

                <div>
                    <label for="institution_code" class="form-label">Institution Code (Login Pin) <span class="text-red-500">*</span></label>
                    <input type="text" name="institution_code" id="institution_code" value="{{ old('institution_code', $tenant->institution_code ?? '') }}" class="form-input font-mono tracking-widest text-lg py-3" required {{ isset($tenant) ? 'readonly' : '' }} placeholder="e.g. 101010">
                    <p class="form-hint">This code is required for users of this institution to log in.</p>
                </div>

                <div>
                    <label for="domain" class="form-label">Mapped Custom Domain (Optional)</label>
                    <input type="text" name="domain" id="domain" value="{{ old('domain', $tenant->domain ?? '') }}" class="form-input font-mono" placeholder="app.acmebank.com">
                    <p class="form-hint">Allows users to bypass the institution code if they access the app from this domain.</p>
                </div>

                @if(isset($tenant))
                <div>
                    <label for="status" class="form-label">System Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" {{ old('status', $tenant->status ?? 'active') === 'active' ? 'selected' : '' }}>Active - Institution running normally</option>
                        <option value="inactive" {{ old('status', $tenant->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive - Institution access suspended</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                    {{ isset($tenant) ? 'Save Institution' : 'Onboard Institution' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
