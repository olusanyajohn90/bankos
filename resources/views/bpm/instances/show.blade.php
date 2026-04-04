<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $instance->process->name ?? 'Instance' }}</h2><p class="text-sm text-bankos-text-sec mt-1">Instance: {{ Str::limit($instance->id, 16) }}</p></div>
            <div class="flex gap-2">
                @if($instance->status == 'active')
                <form method="POST" action="{{ route('bpm.instances.advance', $instance->id) }}">@csrf
                    <button type="submit" class="btn btn-primary text-sm">Advance to Next Step</button>
                </form>
                @endif
                <a href="{{ route('bpm.instances', $instance->process_id) }}" class="btn btn-outline text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
        <div class="card p-5"><p class="text-xs text-bankos-muted uppercase">Status</p><span class="badge {{ ['active'=>'badge-green','completed'=>'badge-blue','cancelled'=>'badge-red','on_hold'=>'badge-amber'][$instance->status] ?? 'badge-gray' }} mt-2">{{ ucfirst($instance->status) }}</span></div>
        <div class="card p-5"><p class="text-xs text-bankos-muted uppercase">Current Step</p><h4 class="text-xl font-bold mt-1">{{ $instance->current_step + 1 }} / {{ is_array($instance->process->steps) ? count($instance->process->steps) : '?' }}</h4></div>
        <div class="card p-5"><p class="text-xs text-bankos-muted uppercase">Started</p><h4 class="text-sm font-medium mt-1">{{ $instance->created_at->format('d M Y H:i') }}</h4></div>
        <div class="card p-5"><p class="text-xs text-bankos-muted uppercase">Completed</p><h4 class="text-sm font-medium mt-1">{{ $instance->completed_at ? $instance->completed_at->format('d M Y H:i') : 'In Progress' }}</h4></div>
    </div>

    {{-- Process flow visualization --}}
    @if(is_array($instance->process->steps))
    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Process Flow</h3>
        <div class="flex items-center gap-2 overflow-x-auto pb-4">
            @foreach($instance->process->steps as $idx => $step)
            <div class="flex-shrink-0 w-48 p-4 rounded-lg border-2 {{ $idx < $instance->current_step ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : ($idx == $instance->current_step ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-bankos-border dark:border-bankos-dark-border') }}">
                <p class="text-xs text-bankos-muted">Step {{ $idx + 1 }}</p>
                <p class="font-semibold text-sm mt-1">{{ $step['name'] ?? 'Unnamed' }}</p>
                <span class="badge {{ $idx < $instance->current_step ? 'badge-green' : ($idx == $instance->current_step ? 'badge-blue' : 'badge-gray') }} text-xs mt-2">
                    {{ $idx < $instance->current_step ? 'Done' : ($idx == $instance->current_step ? 'Current' : 'Pending') }}
                </span>
            </div>
            @if(!$loop->last)
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 {{ $idx < $instance->current_step ? 'text-green-500' : 'text-bankos-muted' }}"><polyline points="9 18 15 12 9 6"></polyline></svg>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Step History --}}
    <div class="card p-6">
        <h3 class="text-lg font-semibold mb-4">Step History</h3>
        @if(is_array($instance->step_history) && count($instance->step_history))
        <div class="space-y-3">
            @foreach($instance->step_history as $h)
            <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg">
                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-blue-600">{{ ($h['step'] ?? 0) + 1 }}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium">{{ ucfirst($h['action'] ?? 'Unknown') }}</p>
                    <p class="text-xs text-bankos-muted">{{ $h['notes'] ?? '' }}</p>
                    <p class="text-xs text-bankos-muted mt-1">{{ \Carbon\Carbon::parse($h['timestamp'] ?? now())->format('d M Y H:i') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-bankos-muted text-sm">No history yet.</p>
        @endif
    </div>
</x-app-layout>
