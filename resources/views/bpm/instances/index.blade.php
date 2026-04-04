<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Instances: {{ $process->name }}</h2></div>
            <div class="flex gap-2">
                <a href="{{ route('bpm.instances.create', $process->id) }}" class="btn btn-primary text-sm">Start Instance</a>
                <a href="{{ route('bpm.processes.show', $process->id) }}" class="btn btn-outline text-sm">Process Detail</a>
            </div>
        </div>
    </x-slot>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>ID</th><th>Subject</th><th>Current Step</th><th>Status</th><th>Initiated By</th><th>Started</th><th></th></tr></thead>
            <tbody>
            @forelse($instances as $i)
                <tr>
                    <td class="font-mono text-xs">{{ Str::limit($i->id, 8) }}</td>
                    <td>{{ $i->subject_type ? ucfirst($i->subject_type) . ' #' . $i->subject_id : 'N/A' }}</td>
                    <td>Step {{ $i->current_step + 1 }}</td>
                    <td><span class="badge {{ ['active'=>'badge-green','completed'=>'badge-blue','cancelled'=>'badge-red','on_hold'=>'badge-amber'][$i->status] ?? 'badge-gray' }}">{{ ucfirst($i->status) }}</span></td>
                    <td>{{ $i->initiator->name ?? 'N/A' }}</td>
                    <td>{{ $i->created_at->format('d M Y H:i') }}</td>
                    <td><a href="{{ route('bpm.instances.show', $i->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No instances.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $instances->links() }}</div>
</x-app-layout>
