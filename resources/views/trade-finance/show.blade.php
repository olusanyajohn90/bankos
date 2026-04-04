<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $instrument->reference }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ ucfirst(str_replace('_',' ',$instrument->type)) }} - {{ $instrument->beneficiary_name }}</p>
            </div>
            <a href="{{ route('trade-finance.index') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card p-6">
            <h3 class="text-lg font-semibold mb-4 text-bankos-text dark:text-white">Instrument Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Reference</dt><dd class="font-mono font-medium">{{ $instrument->reference }}</dd></div>
                <div><dt class="text-bankos-muted">Type</dt><dd><span class="badge badge-blue">{{ ucfirst(str_replace('_',' ',$instrument->type)) }}</span></dd></div>
                <div><dt class="text-bankos-muted">Customer</dt><dd>{{ $instrument->customer->business_name ?? ($instrument->customer->first_name . ' ' . $instrument->customer->last_name) }}</dd></div>
                <div><dt class="text-bankos-muted">Beneficiary</dt><dd>{{ $instrument->beneficiary_name }}</dd></div>
                <div><dt class="text-bankos-muted">Beneficiary Bank</dt><dd>{{ $instrument->beneficiary_bank ?? 'N/A' }}</dd></div>
                <div><dt class="text-bankos-muted">Amount</dt><dd class="font-bold text-lg">{{ $instrument->currency }} {{ number_format($instrument->amount, 2) }}</dd></div>
                <div><dt class="text-bankos-muted">Issue Date</dt><dd>{{ $instrument->issue_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Expiry Date</dt><dd>{{ $instrument->expiry_date->format('d M Y') }}</dd></div>
                <div><dt class="text-bankos-muted">Commission</dt><dd>{{ number_format($instrument->commission_rate, 2) }}% (₦{{ number_format($instrument->commission_amount, 2) }})</dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd>
                    @php $c = ['draft'=>'badge-gray','issued'=>'badge-green','amended'=>'badge-blue','utilized'=>'badge-purple','expired'=>'badge-red','cancelled'=>'badge-red']; @endphp
                    <span class="badge {{ $c[$instrument->status] ?? 'badge-gray' }}">{{ ucfirst($instrument->status) }}</span>
                </dd></div>
            </dl>
            @if($instrument->purpose)
            <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="text-sm font-semibold text-bankos-muted mb-1">Purpose</h4>
                <p class="text-sm">{{ $instrument->purpose }}</p>
            </div>
            @endif
            @if($instrument->terms)
            <div class="mt-3">
                <h4 class="text-sm font-semibold text-bankos-muted mb-1">Terms</h4>
                <p class="text-sm">{{ $instrument->terms }}</p>
            </div>
            @endif
        </div>
        <div class="space-y-4">
            <div class="card p-6">
                <h3 class="text-lg font-semibold mb-4 text-bankos-text dark:text-white">Actions</h3>
                <form method="POST" action="{{ route('trade-finance.update-status', $instrument->id) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <select name="status" class="input w-full">
                        @foreach(['draft','issued','amended','utilized','expired','cancelled'] as $s)
                        <option value="{{ $s }}" {{ $instrument->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary w-full">Update Status</button>
                </form>
            </div>
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-bankos-muted mb-3">Document Checklist</h3>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-gray-300"></span> Application form</li>
                    <li class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-gray-300"></span> Proforma invoice</li>
                    <li class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-gray-300"></span> Bill of lading</li>
                    <li class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-gray-300"></span> Insurance certificate</li>
                    <li class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-gray-300"></span> Form M / Form A</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
