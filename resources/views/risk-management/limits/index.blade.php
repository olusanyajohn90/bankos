<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Risk Limits</h2><p class="text-sm text-bankos-text-sec mt-1">Limit thresholds and utilization tracking</p></div>
            <a href="{{ route('risk-management.limits.create') }}" class="btn btn-primary text-sm">New Limit</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div><label class="text-xs font-medium text-bankos-text-sec">Status</label><select name="status" class="input input-sm mt-1"><option value="">All</option>@foreach(['within_limit','warning','breached'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Name</th><th>Type</th><th>Limit</th><th>Current</th><th>Utilization</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($limits as $l)
                <tr>
                    <td class="font-medium">{{ $l->name }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$l->limit_type)) }}</td>
                    <td>₦{{ number_format($l->limit_value, 0) }}</td>
                    <td>₦{{ number_format($l->current_value, 0) }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $l->status == 'breached' ? 'bg-red-500' : ($l->status == 'warning' ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ min($l->utilization_pct, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold">{{ number_format($l->utilization_pct, 1) }}%</span>
                        </div>
                    </td>
                    <td><span class="badge {{ $l->status == 'breached' ? 'badge-red' : ($l->status == 'warning' ? 'badge-amber' : 'badge-green') }}">{{ ucfirst(str_replace('_',' ',$l->status)) }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('risk-management.limits.update', $l->id) }}" class="flex gap-1">
                            @csrf @method('PATCH')
                            <input type="number" step="0.01" name="current_value" value="{{ $l->current_value }}" class="input input-sm w-28">
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No limits configured.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $limits->links() }}</div>
</x-app-layout>
