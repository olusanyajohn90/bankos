<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Start: {{ $process->name }}</h2></div>
            <a href="{{ route('bpm.processes.show', $process->id) }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('bpm.instances.store', $process->id) }}" class="card p-6 space-y-5">
            @csrf
            <div><label class="label">Subject Type (optional)</label>
                <select name="subject_type" class="input w-full"><option value="">None</option><option value="customer">Customer</option><option value="loan">Loan</option><option value="account">Account</option></select>
            </div>
            <div><label class="label">Subject ID (optional)</label><input type="text" name="subject_id" class="input w-full" placeholder="e.g. Customer ID"></div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('bpm.processes.show', $process->id) }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Start Process</button>
            </div>
        </form>
    </div>
</x-app-layout>
