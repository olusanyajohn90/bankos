<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $report->report_name }}</h2><p class="text-sm text-bankos-text-sec mt-1">{{ strtoupper(str_replace('_',' ',$report->report_type)) }} - {{ $report->period }}</p></div>
            <div class="flex gap-2">
                @if(in_array($report->status, ['pending','draft']))
                <form method="POST" action="{{ route('regulatory.submit', $report->id) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-primary text-sm">Mark as Submitted</button></form>
                @endif
                <a href="{{ route('regulatory.index') }}" class="btn btn-outline text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card p-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Report Type</dt><dd class="font-medium">{{ strtoupper(str_replace('_',' ',$report->report_type)) }}</dd></div>
                <div><dt class="text-bankos-muted">Period</dt><dd>{{ $report->period }}</dd></div>
                <div><dt class="text-bankos-muted">Due Date</dt><dd class="{{ $report->due_date->isPast() && in_array($report->status,['pending','draft']) ? 'text-red-600 font-bold' : '' }}">{{ $report->due_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd>@php $sc=['pending'=>'badge-amber','draft'=>'badge-blue','submitted'=>'badge-green','accepted'=>'badge-green','rejected'=>'badge-red']; @endphp<span class="badge {{ $sc[$report->status] ?? 'badge-gray' }}">{{ ucfirst($report->status) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Submitted Date</dt><dd>{{ $report->submitted_date ? $report->submitted_date->format('d M Y') : 'Not yet' }}</dd></div>
                <div><dt class="text-bankos-muted">Prepared By</dt><dd>{{ $report->preparer->name ?? 'N/A' }}</dd></div>
                <div><dt class="text-bankos-muted">Approved By</dt><dd>{{ $report->approver->name ?? 'N/A' }}</dd></div>
            </dl>
            @if($report->notes)<div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border"><h4 class="text-sm font-semibold text-bankos-muted mb-1">Notes</h4><p class="text-sm">{{ $report->notes }}</p></div>@endif
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Generate Report Data</h3>
            <p class="text-sm text-bankos-muted mb-3">Auto-generate report data from system records.</p>
            <div class="space-y-2">
                @foreach(['cbn_returns','ndic_premium','nfiu_ctr','prudential_guidelines'] as $type)
                <a href="{{ route('regulatory.generate', $type) }}" class="block btn btn-outline btn-sm w-full text-left" target="_blank">Generate {{ strtoupper(str_replace('_',' ',$type)) }}</a>
                @endforeach
            </div>
            @if($report->report_data)
            <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="text-sm font-semibold text-bankos-muted mb-2">Report Data</h4>
                <pre class="text-xs bg-gray-100 dark:bg-gray-800 p-3 rounded overflow-auto max-h-48">{{ json_encode($report->report_data, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
