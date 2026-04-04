<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Record Cash Position</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Enter daily cash position data</p>
            </div>
            <a href="{{ route('cash-management.positions') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('cash-management.store') }}" class="card p-6 space-y-5">
            @csrf
            <div>
                <label class="label">Position Date</label>
                <input type="date" name="position_date" value="{{ old('position_date', date('Y-m-d')) }}" class="input w-full" required>
                @error('position_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Opening Balance (₦)</label>
                    <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance') }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Closing (auto-calculated)</label>
                    <input type="text" class="input w-full bg-gray-50 dark:bg-gray-800" disabled placeholder="Opening + Inflows - Outflows">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Total Inflows (₦)</label>
                    <input type="number" step="0.01" name="total_inflows" value="{{ old('total_inflows', '0') }}" class="input w-full" required>
                </div>
                <div>
                    <label class="label">Total Outflows (₦)</label>
                    <input type="number" step="0.01" name="total_outflows" value="{{ old('total_outflows', '0') }}" class="input w-full" required>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Vault Cash (₦)</label>
                    <input type="number" step="0.01" name="vault_cash" value="{{ old('vault_cash', '0') }}" class="input w-full">
                </div>
                <div>
                    <label class="label">Nostro Balance (₦)</label>
                    <input type="number" step="0.01" name="nostro_balance" value="{{ old('nostro_balance', '0') }}" class="input w-full">
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('cash-management.positions') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Position</button>
            </div>
        </form>
    </div>
</x-app-layout>
