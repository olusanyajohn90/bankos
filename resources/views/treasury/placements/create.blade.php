<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">New Treasury Placement</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Create a money market placement or borrowing</p>
            </div>
            <a href="{{ route('treasury.placements') }}" class="btn btn-outline text-sm">Back to List</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('treasury.placements.store') }}" class="card p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Type</label>
                    <select name="type" class="input w-full" required>
                        <option value="placement" {{ old('type') == 'placement' ? 'selected' : '' }}>Placement</option>
                        <option value="borrowing" {{ old('type') == 'borrowing' ? 'selected' : '' }}>Borrowing</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Counterparty</label>
                    <input type="text" name="counterparty" value="{{ old('counterparty') }}" class="input w-full" required placeholder="Bank or institution name">
                    @error('counterparty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Principal Amount (₦)</label>
                    <input type="number" step="0.01" name="principal" value="{{ old('principal') }}" class="input w-full" required>
                    @error('principal') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Interest Rate (% p.a.)</label>
                    <input type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate') }}" class="input w-full" required>
                    @error('interest_rate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="input w-full" required>
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Maturity Date</label>
                    <input type="date" name="maturity_date" value="{{ old('maturity_date') }}" class="input w-full" required>
                    @error('maturity_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="label">Notes</label>
                <textarea name="notes" class="input w-full" rows="3">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('treasury.placements') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Placement</button>
            </div>
        </form>
    </div>
</x-app-layout>
