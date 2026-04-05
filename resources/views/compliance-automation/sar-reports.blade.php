<x-app-layout>
    <x-slot name="header">SAR/STR Reports</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Generate SAR --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Generate SAR/STR Report</h3>
            <div class="flex gap-3 items-end">
                <form method="POST" id="sarForm" class="flex gap-3 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs text-bankos-muted mb-1">Select Customer</label>
                        <select id="sarCustomer" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2 w-64">
                            <option value="">Choose customer...</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->customer_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="generateSar()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Generate SAR</button>
                </form>
            </div>
        </div>

        {{-- Reports Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Reference</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Amount</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($reports as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->reference }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $r->report_type }}</span></td>
                            <td class="px-4 py-3">{{ $r->customer->first_name ?? '' }} {{ $r->customer->last_name ?? '' }}</td>
                            <td class="px-4 py-3 font-mono">NGN {{ number_format($r->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $r->suspicion_category ?? 'N/A')) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $sc = match($r->status) {
                                        'filed' => 'bg-green-100 text-green-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'pending_review' => 'bg-yellow-100 text-yellow-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc }}">{{ strtoupper(str_replace('_', ' ', $r->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $r->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('compliance-auto.sar.show', $r->id) }}" class="text-bankos-primary hover:underline text-xs font-medium">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-bankos-muted">No SAR/STR reports found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function generateSar() {
            const customerId = document.getElementById('sarCustomer').value;
            if (!customerId) { alert('Please select a customer.'); return; }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url("compliance-automation/sar") }}/' + customerId + '/create';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
    @endpush
</x-app-layout>
