<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Add Holding to {{ $portfolio->portfolio_name }}</h2>
            </div>
            <a href="{{ route('wealth.portfolios.show', $portfolio->id) }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('wealth.portfolios.store-holding', $portfolio->id) }}" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Asset Type</label>
                    <select name="asset_type" class="input w-full" required>
                        @foreach(['treasury_bill','bond','mutual_fund','equity','money_market','fixed_deposit'] as $t)
                        <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="label">Asset Name</label><input type="text" name="asset_name" value="{{ old('asset_name') }}" class="input w-full" required></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div><label class="label">Asset Code</label><input type="text" name="asset_code" value="{{ old('asset_code') }}" class="input w-full" placeholder="Optional"></div>
                <div><label class="label">Quantity</label><input type="number" step="0.0001" name="quantity" value="{{ old('quantity') }}" class="input w-full" required></div>
                <div><label class="label">Yield Rate (%)</label><input type="number" step="0.01" name="yield_rate" value="{{ old('yield_rate') }}" class="input w-full"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Cost Price</label><input type="number" step="0.0001" name="cost_price" value="{{ old('cost_price') }}" class="input w-full" required></div>
                <div><label class="label">Current Price</label><input type="number" step="0.0001" name="current_price" value="{{ old('current_price') }}" class="input w-full" required></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Purchase Date</label><input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="input w-full" required></div>
                <div><label class="label">Maturity Date</label><input type="date" name="maturity_date" value="{{ old('maturity_date') }}" class="input w-full"></div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('wealth.portfolios.show', $portfolio->id) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Add Holding</button>
            </div>
        </form>
    </div>
</x-app-layout>
