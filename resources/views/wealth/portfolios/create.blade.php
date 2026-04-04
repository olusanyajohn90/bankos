<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Create Investment Portfolio</h2>
            </div>
            <a href="{{ route('wealth.portfolios') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('wealth.portfolios.store') }}" class="card p-6 space-y-5">
            @csrf
            <div>
                <label class="label">Customer</label>
                <select name="customer_id" class="input w-full" required>
                    <option value="">Select customer</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->business_name ?? ($c->first_name . ' ' . $c->last_name) }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Portfolio Name</label>
                <input type="text" name="portfolio_name" value="{{ old('portfolio_name') }}" class="input w-full" required placeholder="e.g. Conservative Growth Fund">
                @error('portfolio_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">Risk Profile</label>
                <select name="risk_profile" class="input w-full" required>
                    <option value="conservative">Conservative</option>
                    <option value="moderate" selected>Moderate</option>
                    <option value="aggressive">Aggressive</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('wealth.portfolios') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Portfolio</button>
            </div>
        </form>
    </div>
</x-app-layout>
