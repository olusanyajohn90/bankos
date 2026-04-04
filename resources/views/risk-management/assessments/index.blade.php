<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Risk Assessments</h2></div>
            <a href="{{ route('risk-management.assessments.create') }}" class="btn btn-primary text-sm">New Assessment</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div><label class="text-xs font-medium text-bankos-text-sec">Type</label><select name="risk_type" class="input input-sm mt-1"><option value="">All</option>@foreach(['credit','liquidity','market','operational','concentration'] as $t)<option value="{{ $t }}" {{ request('risk_type')==$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach</select></div>
        <div><label class="text-xs font-medium text-bankos-text-sec">Severity</label><select name="severity" class="input input-sm mt-1"><option value="">All</option>@foreach(['low','medium','high','critical'] as $s)<option value="{{ $s }}" {{ request('severity')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <div><label class="text-xs font-medium text-bankos-text-sec">Status</label><select name="status" class="input input-sm mt-1"><option value="">All</option>@foreach(['open','mitigated','accepted','closed'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Title</th><th>Type</th><th>Severity</th><th>Exposure</th><th>Status</th><th>Assigned To</th><th></th></tr></thead>
            <tbody>
            @forelse($assessments as $a)
                <tr>
                    <td class="font-medium">{{ Str::limit($a->title, 40) }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$a->risk_type)) }}</td>
                    <td>@php $sc=['low'=>'badge-green','medium'=>'badge-amber','high'=>'badge-red','critical'=>'badge-red']; @endphp<span class="badge {{ $sc[$a->severity] ?? 'badge-gray' }}">{{ ucfirst($a->severity) }}</span></td>
                    <td>{{ $a->exposure_amount ? '₦'.number_format($a->exposure_amount, 0) : 'N/A' }}</td>
                    <td><span class="badge {{ $a->status=='open' ? 'badge-amber' : ($a->status=='mitigated' ? 'badge-green' : 'badge-gray') }}">{{ ucfirst($a->status) }}</span></td>
                    <td>{{ $a->assignee->name ?? 'Unassigned' }}</td>
                    <td><a href="{{ route('risk-management.assessments.show', $a->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No assessments.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $assessments->links() }}</div>
</x-app-layout>
