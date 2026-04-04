<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Book FX Deal</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Create a spot, forward or swap FX transaction</p>
            </div>
            <a href="{{ route('treasury.fx-deals') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('treasury.fx-deals.store') }}" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Deal Type</label>
                    <select name="deal_type" class="input w-full" required>
                        <option value="spot">Spot</option>
                        <option value="forward">Forward</option>
                        <option value="swap">Swap</option>
                    </select>
                </div>
                <div>
                    <label class="label">Direction</label>
                    <select name="direction" class="input w-full" required>
                        <option value="buy">Buy</option>
                        <option value="sell">Sell</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Currency Pair</label>
                    <select name="currency_pair" class="input w-full" required>
                        <option value="USD/NGN">USD/NGN</option>
                        <option value="EUR/NGN">EUR/NGN</option>
                        <option value="GBP/NGN">GBP/NGN</option>
                        <option value="EUR/USD">EUR/USD</option>
                    </select>
                    @error('currency_pair') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Counterparty</label>
                    <input type="text" name="counterparty" value="{{ old('counterparty') }}" class="input w-full" placeholder="Optional">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Amount (Base Currency)</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="input w-full" required>
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Rate</label>
                    <input type="number" step="0.000001" name="rate" value="{{ old('rate') }}" class="input w-full" required>
                    @error('rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Trade Date</label>
                    <input type="date" name="trade_date" value="{{ old('trade_date', date('Y-m-d')) }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Settlement Date</label>
                    <input type="date" name="settlement_date" value="{{ old('settlement_date') }}" class="input w-full" required>
                    @error('settlement_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('treasury.fx-deals') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Book Deal</button>
            </div>
        </form>
    </div>
</x-app-layout>
