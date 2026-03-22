<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Create Share Product
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Define a new share class for your cooperative</p>
            </div>
            <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Shares
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('cooperative.shares.products.store') }}" method="POST" class="card p-6 space-y-6">
            @csrf

            {{-- Product Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Product Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="form-input w-full"
                       placeholder="e.g. Ordinary Shares, Preference Shares">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="form-input w-full"
                          placeholder="Brief description of this share class...">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Par Value --}}
            <div>
                <label for="par_value" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Par Value (Price per Share) <span class="text-red-500">*</span></label>
                <input type="number" name="par_value" id="par_value" value="{{ old('par_value') }}" required
                       step="0.01" min="0.01"
                       class="form-input w-full"
                       placeholder="100.00">
                @error('par_value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Min / Max Shares --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="min_shares" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Minimum Shares <span class="text-red-500">*</span></label>
                    <input type="number" name="min_shares" id="min_shares" value="{{ old('min_shares', 1) }}" required
                           min="1"
                           class="form-input w-full">
                    @error('min_shares') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="max_shares" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Maximum Shares</label>
                    <input type="number" name="max_shares" id="max_shares" value="{{ old('max_shares') }}"
                           min="1"
                           class="form-input w-full"
                           placeholder="Leave blank for unlimited">
                    @error('max_shares') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Dividend Rate --}}
            <div>
                <label for="dividend_rate" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Annual Dividend Rate (%)</label>
                <input type="number" name="dividend_rate" id="dividend_rate" value="{{ old('dividend_rate') }}"
                       step="0.0001" min="0" max="100"
                       class="form-input w-full"
                       placeholder="e.g. 5.0000 for preference shares">
                <p class="text-xs text-bankos-muted mt-1">Leave blank for ordinary shares where dividends are discretionary.</p>
                @error('dividend_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Toggles --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="transferable" value="0">
                    <input type="checkbox" name="transferable" id="transferable" value="1"
                           {{ old('transferable') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-bankos-primary focus:ring-bankos-primary">
                    <label for="transferable" class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">Transferable between members</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="redeemable" value="0">
                    <input type="checkbox" name="redeemable" id="redeemable" value="1"
                           {{ old('redeemable', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-bankos-primary focus:ring-bankos-primary">
                    <label for="redeemable" class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">Redeemable by members</label>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <button type="submit" class="btn btn-primary">
                    Create Share Product
                </button>
                <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
