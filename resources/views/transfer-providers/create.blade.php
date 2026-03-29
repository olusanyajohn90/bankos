<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('transfer-providers.index') }}"
               class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Add Transfer Provider</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure a new interbank transfer service provider</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('transfer-providers.store') }}" class="card p-6 space-y-6">
            @csrf

            {{-- Provider Details --}}
            <div class="border-b border-bankos-border pb-4">
                <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4">Provider Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Name --}}
                    <div class="flex flex-col gap-1">
                        <label for="name" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="e.g. NIBSS NIP, Paystack, Flutterwave"
                               class="form-input @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Code --}}
                    <div class="flex flex-col gap-1">
                        <label for="code" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Code *</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}"
                               placeholder="e.g. nip, paystack, flutterwave"
                               class="form-input font-mono @error('code') border-red-500 @enderror" required>
                        <span class="text-xs text-bankos-muted">Lowercase, alphanumeric, hyphens and underscores only</span>
                        @error('code')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Provider Class --}}
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <label for="provider_class" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Driver Class *</label>
                        <input type="text" id="provider_class" name="provider_class" value="{{ old('provider_class') }}"
                               placeholder="e.g. App\Services\TransferProviders\NipProvider"
                               class="form-input font-mono text-sm @error('provider_class') border-red-500 @enderror" required>
                        @error('provider_class')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Config (JSON) --}}
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <label for="config" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Configuration (JSON)</label>
                        <textarea id="config" name="config" rows="4"
                                  placeholder='{"api_key": "...", "secret_key": "...", "base_url": "..."}'
                                  class="form-input font-mono text-sm @error('config') border-red-500 @enderror">{{ old('config') }}</textarea>
                        <span class="text-xs text-bankos-muted">API keys, endpoints, and other provider-specific settings (encrypted at application level)</span>
                        @error('config')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Routing & Priority --}}
            <div class="border-b border-bankos-border pb-4">
                <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4">Routing & Priority</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Priority --}}
                    <div class="flex flex-col gap-1">
                        <label for="priority" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Priority</label>
                        <input type="number" id="priority" name="priority" value="{{ old('priority', 0) }}"
                               min="0" class="form-input @error('priority') border-red-500 @enderror">
                        <span class="text-xs text-bankos-muted">Higher = preferred for routing</span>
                        @error('priority')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Active --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Active</label>
                        <label class="flex items-center gap-2 mt-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm text-bankos-text">Enable this provider</span>
                        </label>
                    </div>

                    {{-- Default --}}
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Default</label>
                        <label class="flex items-center gap-2 mt-2">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-bankos-primary focus:ring-bankos-primary">
                            <span class="text-sm text-bankos-text">Set as default provider</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Amount Limits --}}
            <div class="border-b border-bankos-border pb-4">
                <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4">Amount Limits</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Min Amount --}}
                    <div class="flex flex-col gap-1">
                        <label for="min_amount" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Minimum Amount</label>
                        <input type="number" id="min_amount" name="min_amount" value="{{ old('min_amount', 0) }}"
                               min="0" step="0.01" class="form-input @error('min_amount') border-red-500 @enderror">
                        @error('min_amount')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Max Amount --}}
                    <div class="flex flex-col gap-1">
                        <label for="max_amount" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Maximum Amount</label>
                        <input type="number" id="max_amount" name="max_amount" value="{{ old('max_amount') }}"
                               min="0" step="0.01" placeholder="Leave blank for unlimited"
                               class="form-input @error('max_amount') border-red-500 @enderror">
                        @error('max_amount')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Fee Structure --}}
            <div class="pb-2">
                <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4">Fee Structure</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Flat Fee --}}
                    <div class="flex flex-col gap-1">
                        <label for="flat_fee" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Flat Fee</label>
                        <input type="number" id="flat_fee" name="flat_fee" value="{{ old('flat_fee', 0) }}"
                               min="0" step="0.01" class="form-input @error('flat_fee') border-red-500 @enderror">
                        @error('flat_fee')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Percentage Fee --}}
                    <div class="flex flex-col gap-1">
                        <label for="percentage_fee" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Percentage Fee</label>
                        <input type="number" id="percentage_fee" name="percentage_fee" value="{{ old('percentage_fee', 0) }}"
                               min="0" max="1" step="0.0001" class="form-input @error('percentage_fee') border-red-500 @enderror">
                        <span class="text-xs text-bankos-muted">Decimal (e.g. 0.005 = 0.5%)</span>
                        @error('percentage_fee')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Fee Cap --}}
                    <div class="flex flex-col gap-1">
                        <label for="fee_cap" class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Fee Cap</label>
                        <input type="number" id="fee_cap" name="fee_cap" value="{{ old('fee_cap') }}"
                               min="0" step="0.01" placeholder="No cap"
                               class="form-input @error('fee_cap') border-red-500 @enderror">
                        <span class="text-xs text-bankos-muted">Maximum fee charged per transfer</span>
                        @error('fee_cap')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Create Provider</button>
                <a href="{{ route('transfer-providers.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
