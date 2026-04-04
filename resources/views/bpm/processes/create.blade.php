<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Define New Process</h2></div>
            <a href="{{ route('bpm.processes') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('bpm.processes.store') }}" class="card p-6 space-y-5">
            @csrf
            <div><label class="label">Process Name</label><input type="text" name="name" value="{{ old('name') }}" class="input w-full" required></div>
            <div><label class="label">Description</label><textarea name="description" class="input w-full" rows="2">{{ old('description') }}</textarea></div>
            <div><label class="label">Category</label>
                <select name="category" class="input w-full" required>
                    @foreach(['account_opening','loan_processing','kyc_verification','dispute_resolution','document_approval','custom'] as $c)
                    <option value="{{ $c }}">{{ ucfirst(str_replace('_',' ',$c)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Steps (JSON)</label>
                <textarea name="steps" class="input w-full font-mono text-sm" rows="8" required>{{ old('steps', json_encode([
                    ['name' => 'Initiation', 'type' => 'task', 'config' => ['description' => 'Start the process']],
                    ['name' => 'Review', 'type' => 'approval', 'config' => ['role' => 'manager']],
                    ['name' => 'Completion', 'type' => 'notification', 'config' => ['message' => 'Process completed']],
                ], JSON_PRETTY_PRINT)) }}</textarea>
                <p class="text-xs text-bankos-muted mt-1">Define process steps as JSON array. Each step: {name, type: "approval|task|notification|condition", config: {...}}</p>
                @error('steps') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('bpm.processes') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Process</button>
            </div>
        </form>
    </div>
</x-app-layout>
