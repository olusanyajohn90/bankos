<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $process->name }}</h2><p class="text-sm text-bankos-text-sec mt-1">{{ ucfirst(str_replace('_',' ',$process->category)) }}</p></div>
            <div class="flex gap-2">
                <a href="{{ route('bpm.instances.create', $process->id) }}" class="btn btn-primary text-sm">Start Instance</a>
                <a href="{{ route('bpm.instances', $process->id) }}" class="btn btn-outline text-sm">View Instances</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-3 gap-5 mb-8">
        <div class="card p-5 text-center"><p class="text-xs text-bankos-muted uppercase">Active</p><h4 class="text-xl font-bold mt-1 text-green-600">{{ $instanceStats['active'] }}</h4></div>
        <div class="card p-5 text-center"><p class="text-xs text-bankos-muted uppercase">Completed</p><h4 class="text-xl font-bold mt-1 text-blue-600">{{ $instanceStats['completed'] }}</h4></div>
        <div class="card p-5 text-center"><p class="text-xs text-bankos-muted uppercase">Cancelled</p><h4 class="text-xl font-bold mt-1 text-red-600">{{ $instanceStats['cancelled'] }}</h4></div>
    </div>

    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Process Flow</h3>
        @if(is_array($process->steps))
        <div class="flex items-center gap-2 overflow-x-auto pb-4">
            @foreach($process->steps as $idx => $step)
            <div class="flex-shrink-0 w-48 p-4 rounded-lg border-2 {{ $idx == 0 ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-bankos-border dark:border-bankos-dark-border' }}">
                <p class="text-xs text-bankos-muted">Step {{ $idx + 1 }}</p>
                <p class="font-semibold text-sm mt-1">{{ $step['name'] ?? 'Unnamed' }}</p>
                <span class="badge badge-blue text-xs mt-2">{{ ucfirst($step['type'] ?? 'task') }}</span>
            </div>
            @if(!$loop->last)
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-bankos-muted"><polyline points="9 18 15 12 9 6"></polyline></svg>
            @endif
            @endforeach
        </div>
        @else
        <p class="text-bankos-muted">No steps defined.</p>
        @endif
    </div>

    @if($process->description)
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-bankos-muted mb-2">Description</h3>
        <p class="text-sm">{{ $process->description }}</p>
    </div>
    @endif
</x-app-layout>
