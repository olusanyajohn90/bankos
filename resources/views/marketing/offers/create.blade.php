<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.offers') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Create Offer</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Set up a new promotional offer or coupon</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{
        offerType: 'discount',
        discountType: 'percentage',
        discountValue: '',
        cashbackAmount: '',
        bonusPoints: '',
        feeType: ''
    }" class="max-w-3xl space-y-6">
        <form action="{{ route('marketing.offers.store') }}" method="POST">
            @csrf

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Offer Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Name *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. New Year Loan Discount">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Optional description"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Offer Type *</label>
                        <select name="offer_type" x-model="offerType" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="discount">Discount</option>
                            <option value="cashback">Cashback</option>
                            <option value="fee_waiver">Fee Waiver</option>
                            <option value="bonus_points">Bonus Points</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Coupon Code</label>
                        <input type="text" name="coupon_code" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2 uppercase" placeholder="e.g. NEWYEAR2026">
                        @error('coupon_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Dynamic Config based on type --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Offer Configuration</h3>

                <div x-show="offerType === 'discount'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Discount Type</label>
                        <select name="offer_config[discount_type]" x-model="discountType" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1" x-text="discountType === 'percentage' ? 'Discount (%)' : 'Discount Amount'"></label>
                        <input type="number" name="offer_config[discount_value]" step="0.01" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    </div>
                </div>

                <div x-show="offerType === 'cashback'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Cashback Amount</label>
                        <input type="number" name="offer_config[cashback_amount]" step="0.01" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Minimum Transaction</label>
                        <input type="number" name="offer_config[min_transaction]" step="0.01" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    </div>
                </div>

                <div x-show="offerType === 'fee_waiver'">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Fee Type to Waive</label>
                    <select name="offer_config[fee_type]" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="maintenance">Account Maintenance Fee</option>
                        <option value="transfer">Transfer Fee</option>
                        <option value="loan_processing">Loan Processing Fee</option>
                        <option value="atm">ATM Fee</option>
                    </select>
                </div>

                <div x-show="offerType === 'bonus_points'">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Bonus Points</label>
                    <input type="number" name="offer_config[bonus_points]" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                </div>
            </div>

            {{-- Targeting & Limits --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Targeting & Limits</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Target Segment</label>
                        <select name="segment_id" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            <option value="">All Customers</option>
                            @foreach($segments as $seg)
                            <option value="{{ $seg->id }}">{{ $seg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Max Redemptions</label>
                        <input type="number" name="max_redemptions" min="1" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Unlimited if empty">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Start Date</label>
                        <input type="date" name="start_date" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">End Date</label>
                        <input type="date" name="end_date" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('marketing.offers') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90">Create Offer</button>
            </div>
        </form>
    </div>
</x-app-layout>
