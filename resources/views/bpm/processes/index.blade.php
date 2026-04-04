<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Process Definitions</h2></div>
            <a href="{{ route('bpm.processes.create') }}" class="btn btn-primary text-sm">New Process</a>
        </div>
    </x-slot>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Name</th><th>Category</th><th>Steps</th><th>Instances</th><th>Avg Time</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($processes as $p)
                <tr>
                    <td class="font-medium">{{ $p->name }}</td>
                    <td><span class="badge badge-blue">{{ ucfirst(str_replace('_',' ',$p->category)) }}</span></td>
                    <td>{{ is_array($p->steps) ? count($p->steps) : 0 }}</td>
                    <td>{{ $p->total_instances }}</td>
                    <td>{{ $p->avg_completion_hours ? $p->avg_completion_hours.'h' : 'N/A' }}</td>
                    <td><span class="badge {{ $p->is_active ? 'badge-green' : 'badge-gray' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="flex gap-2">
                        <a href="{{ route('bpm.processes.show', $p->id) }}" class="text-bankos-primary text-sm hover:underline">View</a>
                        <a href="{{ route('bpm.instances', $p->id) }}" class="text-indigo-600 text-sm hover:underline">Instances</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No processes defined.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $processes->links() }}</div>
</x-app-layout>
