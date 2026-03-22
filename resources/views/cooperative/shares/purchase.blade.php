<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Purchase Shares
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Issue new shares to a cooperative member</p>
            </div>
            <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Shares
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('cooperative.shares.purchase.store') }}" method="POST" class="card p-6 space-y-6"
              x-data="{
                  customerId: '{{ old('customer_id') }}',
                  productId: '{{ old('share_product_id') }}',
                  quantity: {{ old('quantity', 0) }},
                  products: {{ Js::from($products) }},
                  customers: {{ Js::from($customers) }},
                  get selectedProduct() {
                      return this.products.find(p => p.id === this.productId);
                  },
                  get totalAmount() {
                      if (!this.selectedProduct || !this.quantity) return 0;
                      return (parseFloat(this.selectedProduct.par_value) * parseInt(this.quantity)).toFixed(2);
                  },
                  customerAccounts: [],
                  loadAccounts() {
                      if (!this.customerId) { this.customerAccounts = []; return; }
                      fetch('/api/internal/customer-accounts/' + this.customerId)
                          .then(r => r.json())
                          .then(data => this.customerAccounts = data)
                          .catch(() => this.customerAccounts = []);
                  }
              }">
            @csrf

            {{-- Select Customer --}}
            <div>
                <label for="customer_id" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Member / Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customer_id" required x-model="customerId"
                        @change="loadAccounts()"
                        class="form-input w-full">
                    <option value="">-- Select Member --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->customer_number }})
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Select Share Product --}}
            <div>
                <label for="share_product_id" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Share Product <span class="text-red-500">*</span></label>
                <select name="share_product_id" id="share_product_id" required x-model="productId"
                        class="form-input w-full">
                    <option value="">-- Select Share Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('share_product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (Par: {{ number_format($product->par_value, 2) }}{{ $product->min_shares > 1 ? ', Min: ' . $product->min_shares : '' }}{{ $product->max_shares ? ', Max: ' . $product->max_shares : '' }})
                        </option>
                    @endforeach
                </select>
                @error('share_product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Quantity --}}
            <div>
                <label for="quantity" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Number of Shares <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" required
                       min="1" x-model="quantity"
                       class="form-input w-full"
                       placeholder="Enter number of shares to purchase">
                <template x-if="selectedProduct">
                    <p class="text-xs text-bankos-muted mt-1">
                        Min: <span x-text="selectedProduct.min_shares"></span>
                        <template x-if="selectedProduct.max_shares">
                            <span> | Max: <span x-text="selectedProduct.max_shares"></span></span>
                        </template>
                    </p>
                </template>
                @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Total Amount Preview --}}
            <div x-show="totalAmount > 0" class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-blue-800 dark:text-blue-300">Total Purchase Amount</span>
                    <span class="text-xl font-bold text-blue-900 dark:text-blue-200 font-mono" x-text="parseFloat(totalAmount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </div>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    <span x-text="quantity"></span> shares x <span x-text="parseFloat(selectedProduct?.par_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2})"></span> per share
                </p>
            </div>

            {{-- Source Account --}}
            <div>
                <label for="account_id" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Source Account (to debit) <span class="text-red-500">*</span></label>
                <select name="account_id" id="account_id" required class="form-input w-full">
                    <option value="">-- Select customer first --</option>
                    <template x-for="acct in customerAccounts" :key="acct.id">
                        <option :value="acct.id" x-text="acct.account_number + ' — ₦' + parseFloat(acct.available_balance).toLocaleString('en-NG', {minimumFractionDigits: 2})"></option>
                    </template>
                </select>
                <p class="text-xs text-bankos-muted mt-1">The customer's account that will be debited for the share purchase amount.</p>
                @error('account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="2"
                          class="form-input w-full"
                          placeholder="Optional notes about this purchase...">{{ old('notes') }}</textarea>
                @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Are you sure you want to process this share purchase? The customer account will be debited.')">
                    Purchase Shares
                </button>
                <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
