<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Regulatory Reports</h2></div>
            <a href="{{ route('regulatory.create') }}" class="btn btn-primary text-sm">New Report</a>
        </div>
    </x-slot>

    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div><label class="text-xs font-medium text-bankos-text-sec">Type</label><select name="report_type" class="input input-sm mt-1"><option value="">All</option>@foreach(['cbn_returns','ndic_premium','nfiu_ctr','nfiu_str','prudential_guidelines'] as $t)<option value="{{ $t }}" {{ request('report_type')==$t?'selected':'' }}>{{ strtoupper(str_replace('_',' ',$t)) }}</option>@endforeach</select></div>
        <div><label class="text-xs font-medium text-bankos-text-sec">Status</label><select name="status" class="input input-sm mt-1"><option value="">All</option>@foreach(['pending','draft','submitted','accepted','rejected'] as $s)<option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select></div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Report Name</th><th>Type</th><th>Period</th><th>Due Date</th><th>Status</th><th>Prepared By</th><th></th></tr></thead>
            <tbody>
            @forelse($reports as $r)
                <tr>
                    <td class="font-medium">{{ $r->report_name }}</td>
                    <td>{{ strtoupper(str_replace('_',' ',$r->report_type)) }}</td>
                    <td>{{ $r->period }}</td>
                    <td class="{{ $r->due_date->isPast() && in_array($r->status,['pending','draft']) ? 'text-red-600 font-bold' : '' }}">{{ $r->due_date->format('d M Y') }}</td>
                    <td>@php $sc=['pending'=>'badge-amber','draft'=>'badge-blue','submitted'=>'badge-green','accepted'=>'badge-green','rejected'=>'badge-red']; @endphp<span class="badge {{ $sc[$r->status] ?? 'badge-gray' }}">{{ ucfirst($r->status) }}</span></td>
                    <td>{{ $r->preparer->name ?? 'N/A' }}</td>
                    <td><a href="{{ route('regulatory.show', $r->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No reports found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reports->links() }}</div>
</x-app-layout>
