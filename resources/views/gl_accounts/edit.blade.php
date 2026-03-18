<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('gl-accounts.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    {{ isset($glAccount) ? 'Edit GL Account: ' . $glAccount->name : 'Create GL Account' }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure chart of accounts entity</p>
            </div>
        </div>
    </x-slot>

    <div class="card max-w-2xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ isset($glAccount) ? route('gl-accounts.update', $glAccount) : route('gl-accounts.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($glAccount))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="form-label">Account Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $glAccount->name ?? '') }}" class="form-input" required placeholder="e.g. Cash in Vault">
                </div>

                <div>
                    <label for="account_number" class="form-label">Account Number (GL Code) <span class="text-red-500">*</span></label>
                    <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $glAccount->account_number ?? '') }}" class="form-input font-mono tracking-widest" required placeholder="1000">
                </div>

                <div>
                    <label for="category" class="form-label">Category <span class="text-red-500">*</span></label>
                    <select name="category" id="category" class="form-select" required>
                        @foreach(['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $glAccount->category ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="level" class="form-label">Hierarchy Level <span class="text-red-500">*</span></label>
                    <select name="level" id="level" class="form-select" required>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('level', $glAccount->level ?? '') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                        @endfor
                    </select>
                    <p class="form-hint">Level 1 for headers, Level 2+ for transactional accounts.</p>
                </div>

                <div>
                     <label for="parent_id" class="form-label">Parent Account</label>
                     <select name="parent_id" id="parent_id" class="form-select">
                         <option value="">-- No Parent (Root Account) --</option>
                         @foreach($parents ?? [] as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $glAccount->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->account_number }} - {{ $parent->name }}
                            </option>
                         @endforeach
                     </select>
                </div>

                <div class="md:col-span-2">
                     <label for="branch_id" class="form-label">Linked Branch (Optional)</label>
                     <select name="branch_id" id="branch_id" class="form-select">
                         <option value="">-- Applies to all branches --</option>
                         @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $glAccount->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->code }} - {{ $branch->name }}
                            </option>
                         @endforeach
                     </select>
                     <p class="form-hint">Restrict this GL account tracking to a specific physical branch.</p>
                </div>
            </div>

            <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border flex justify-end gap-3">
                <a href="{{ route('gl-accounts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                    {{ isset($glAccount) ? 'Update GL Account' : 'Create GL Account' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
