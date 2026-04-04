<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Create Risk Limit</h2></div>
            <a href="{{ route('risk-management.limits') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('risk-management.limits.store') }}" class="card p-6 space-y-5">
            @csrf
            <div><label class="label">Limit Type</label>
                <select name="limit_type" class="input w-full" required>
                    <option value="single_obligor">Single Obligor Limit</option>
                    <option value="sector_concentration">Sector Concentration</option>
                    <option value="currency_exposure">Currency Exposure</option>
                    <option value="liquidity_ratio">Liquidity Ratio</option>
                    <option value="capital_adequacy">Capital Adequacy</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div><label class="label">Name</label><input type="text" name="name" value="{{ old('name') }}" class="input w-full" required></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Limit Value (₦)</label><input type="number" step="0.01" name="limit_value" value="{{ old('limit_value') }}" class="input w-full" required></div>
                <div><label class="label">Current Value (₦)</label><input type="number" step="0.01" name="current_value" value="{{ old('current_value', '0') }}" class="input w-full"></div>
            </div>
            <div><label class="label">Warning Threshold (%)</label><input type="number" step="0.01" name="warning_threshold" value="{{ old('warning_threshold', '80') }}" class="input w-full" min="0" max="100"></div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('risk-management.limits') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Limit</button>
            </div>
        </form>
    </div>
</x-app-layout>
