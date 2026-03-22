<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Redeem Shares
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Process share redemption for a cooperative member</p>
            </div>
            <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Shares
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('cooperative.shares.redeem.store') }}" method="POST" class="card p-6 space-y-6"
              x-data="{
                  selectedId: '{{ old('member_share_id') }}',
                  holdings: {{ Js::from($holdings) }},
                  customerAccounts: [],
                  get selectedHolding() {
                      return this.holdings.find(h => h.id === this.selectedId);
                  },
                  loadAccounts() {
                      if (!this.selectedHolding) { this.customerAccounts = []; return; }
                      fetch('/api/internal/customer-accounts/' + this.selectedHolding.customer_id)
                          .then(r => r.json())
                          .then(data => this.customerAccounts = data)
                          .catch(() => this.customerAccounts = []);
                  }
              }">
            @csrf

            {{-- Select Share Holding --}}
            <div>
                <label for="member_share_id" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Share Holding to Redeem <span class="text-red-500">*</span></label>
                <select name="member_share_id" id="member_share_id" required x-model="selectedId"
                        @change="loadAccounts()"
                        class="form-input w-full">
                    <option value="">-- Select Share Holding --</option>
                    @foreach($holdings as $holding)
                        <option value="{{ $holding->id }}" {{ old('member_share_id') == $holding->id ? 'selected' : '' }}>
                            {{ $holding->first_name }} {{ $holding->last_name }} ({{ $holding->customer_number }}) - {{ $holding->product_name }}: {{ number_format($holding->quantity) }} shares @ {{ number_format($holding->par_value, 2) }} = {{ number_format($holding->total_value, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('member_share_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Selected Holding Details --}}
            <template x-if="selectedHolding">
                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 space-y-2">
                    <h4 class="font-semibold text-amber-900 dark:text-amber-200 text-sm">Redemption Summary</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-amber-700 dark:text-amber-400">Member</p>
                            <p class="font-medium text-amber-900 dark:text-amber-200" x-text="selectedHolding.first_name + ' ' + selectedHolding.last_name"></p>
                        </div>
                        <div>
                            <p class="text-amber-700 dark:text-amber-400">Product</p>
                            <p class="font-medium text-amber-900 dark:text-amber-200" x-text="selectedHolding.product_name"></p>
                        </div>
                        <div>
                            <p class="text-amber-700 dark:text-amber-400">Shares</p>
                            <p class="font-medium text-amber-900 dark:text-amber-200" x-text="parseInt(selectedHolding.quantity).toLocaleString()"></p>
                        </div>
                        <div>
                            <p class="text-amber-700 dark:text-amber-400">Amount to Credit</p>
                            <p class="font-bold text-amber-900 dark:text-amber-200 text-lg font-mono" x-text="parseFloat(selectedHolding.total_value).toLocaleString(undefined, {minimumFractionDigits: 2})"></p>
                        </div>
                    </div>
                    @if(isset($holding->certificate_number))
                        <p class="text-xs text-amber-600 dark:text-amber-400">Certificate: <span x-text="selectedHolding.certificate_number"></span></p>
                    @endif
                </div>
            </template>

            {{-- Destination Account --}}
            <div>
                <label for="account_id" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Destination Account (to credit) <span class="text-red-500">*</span></label>
                <select name="account_id" id="account_id" required class="form-input w-full">
                    <option value="">-- Select holding first --</option>
                    <template x-for="acct in customerAccounts" :key="acct.id">
                        <option :value="acct.id" x-text="acct.account_number + ' — ₦' + parseFloat(acct.available_balance).toLocaleString('en-NG', {minimumFractionDigits: 2})"></option>
                    </template>
                </select>
                <p class="text-xs text-bankos-muted mt-1">The customer's account that will be credited with the redemption amount.</p>
                @error('account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="2"
                          class="form-input w-full"
                          placeholder="Reason for redemption...">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <button type="submit" class="btn btn-primary bg-red-600 hover:bg-red-700"
                        onclick="return confirm('Are you sure you want to redeem these shares? This action cannot be undone. The customer account will be credited.')">
                    Redeem Shares
                </button>
                <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        @if($holdings->isEmpty())
            <div class="card p-8 text-center mt-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-bankos-muted/50 mb-3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p class="text-bankos-muted">No redeemable share holdings found.</p>
                <p class="text-sm text-bankos-muted mt-1">Members need active shares in a redeemable product to process redemptions.</p>
            </div>
        @endif
    </div>
</x-app-layout>
