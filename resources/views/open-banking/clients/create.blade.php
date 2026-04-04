<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Register API Client</h2></div>
            <a href="{{ route('open-banking.clients') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('open-banking.clients.store') }}" class="card p-6 space-y-5">
            @csrf
            <div><label class="label">Application Name</label><input type="text" name="name" value="{{ old('name') }}" class="input w-full" required>@error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Description</label><textarea name="description" class="input w-full" rows="2">{{ old('description') }}</textarea></div>
            <div><label class="label">Webhook URL</label><input type="url" name="webhook_url" value="{{ old('webhook_url') }}" class="input w-full" placeholder="https://"></div>
            <div><label class="label">Rate Limit (requests/min)</label><input type="number" name="rate_limit_per_minute" value="{{ old('rate_limit_per_minute', '60') }}" class="input w-full" min="1" max="10000"></div>
            <p class="text-xs text-bankos-muted">Client ID and secret will be auto-generated. The secret will only be shown once after creation.</p>
            <div class="flex justify-end gap-3">
                <a href="{{ route('open-banking.clients') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Client</button>
            </div>
        </form>
    </div>
</x-app-layout>
