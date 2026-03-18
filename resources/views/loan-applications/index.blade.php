<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Portal Loan Applications</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Review and action loan applications submitted via the customer portal</p>
        </div>
    </x-slot>

    {{-- KPI tiles --}}
    @php
    $counts = $statusCounts;
    $tiles = [
        ['label'=>'Pending Review','key'=>'pending',  'color'=>'text-amber-600','bg'=>'bg-amber-50'],
        ['label'=>'Approved',      'key'=>'approved', 'color'=>'text-blue-600', 'bg'=>'bg-blue-50'],
        ['label'=>'Converted',     'key'=>'converted','color'=>'text-green-600','bg'=>'bg-green-50'],
        ['label'=>'Rejected',      'key'=>'rejected', 'color'=>'text-red-600',  'bg'=>'bg-red-50'],
    ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($tiles as $t)
        <div class="{{ $t['bg'] }} rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $t['label'] }}</p>
            <p class="text-3xl font-black {{ $t['color'] }}">{{ $counts[$t['key']] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    <div class="card p-0 overflow-hidden">
        {{-- Filters --}}
        <div class="p-4 border-b border-bankos-border bg-gray-50/50 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
            <form action="{{ route('loan-applications.index') }}" method="GET" class="flex gap-3 items-center flex-wrap w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference, customer..." class="form-input text-sm w-64">
                <select name="status" onchange="this.form.submit()" class="form-select text-sm py-2">
                    <option value="">All Statuses</option>
                    @foreach(['pending','under_review','approved','rejected','converted','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary text-sm">Filter</button>
                @if(request('search') || request('status'))
                <a href="{{ route('loan-applications.index') }}" class="text-sm text-bankos-primary hover:underline">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-bankos-border">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Amount</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Tenor</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($applications as $app)
                    @php
                    $sc = ['pending'=>'bg-amber-100 text-amber-700','under_review'=>'bg-blue-100 text-blue-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','converted'=>'bg-purple-100 text-purple-700','cancelled'=>'bg-gray-100 text-gray-500'][$app->status] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $app->reference }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $app->customer_name }}</p>
                            <p class="text-xs text-gray-400">{{ $app->customer_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 capitalize">{{ str_replace('_',' ',$app->loan_type) }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">₦{{ number_format($app->requested_amount, 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ $app->requested_tenor_months }}m</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $sc }}">{{ strtoupper(str_replace('_',' ',$app->status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($app->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('loan-applications.show', $app->id) }}" class="text-xs font-semibold text-bankos-primary hover:underline">Review →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 text-sm">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-bankos-border">
            {{ $applications->links() }}
        </div>
    </div>
</x-app-layout>
