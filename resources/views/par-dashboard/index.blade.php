<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">PAR Dashboard — Portfolio at Risk</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Real-time loan portfolio health and delinquency analysis</p>
        </div>
    </x-slot>

    {{-- Top KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Gross Portfolio</p>
            <p class="text-2xl font-black text-gray-900">₦{{ number_format($totalPortfolio / 1000000, 1) }}M</p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Total PAR Amount</p>
            <p class="text-2xl font-black text-red-600">₦{{ number_format($totalParAmount / 1000000, 1) }}M</p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">PAR Ratio</p>
            <p class="text-2xl font-black {{ $parRatio <= 5 ? 'text-green-600' : ($parRatio <= 10 ? 'text-amber-600' : 'text-red-600') }}">
                {{ number_format($parRatio, 1) }}%
            </p>
            <p class="text-xs {{ $parRatio <= 5 ? 'text-green-500' : ($parRatio <= 10 ? 'text-amber-500' : 'text-red-500') }} mt-1">
                {{ $parRatio <= 5 ? 'Healthy' : ($parRatio <= 10 ? 'Watch' : 'Critical') }}
            </p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Benchmark</p>
            <p class="text-2xl font-black text-blue-600">5%</p>
            <p class="text-xs text-gray-400 mt-1">CBN / MFB target</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- PAR Buckets --}}
        <div class="lg:col-span-2 card">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Delinquency Aging Buckets</h3>
            <div class="space-y-3">
                @foreach($parData as $bucket)
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-sm font-semibold text-gray-700">{{ $bucket['label'] }}</span>
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-gray-400">{{ $bucket['count'] }} loans</span>
                            <span class="text-sm font-bold text-gray-900">₦{{ number_format($bucket['amount'] / 1000, 0) }}K</span>
                            <span class="text-xs font-semibold" style="color:{{ $bucket['color'] }}">{{ $bucket['pct'] }}%</span>
                        </div>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ min($bucket['pct'], 100) }}%;background:{{ $bucket['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- By Product --}}
        <div class="card">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Overdue by Product</h3>
            @if($byProduct->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">No overdue loans.</p>
            @else
            <div class="space-y-3">
                @foreach($byProduct as $p)
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $p->name }}</p>
                        <p class="text-xs text-gray-400">{{ $p->cnt }} {{ $p->cnt === 1 ? 'loan' : 'loans' }}</p>
                    </div>
                    <p class="text-sm font-bold text-red-600">₦{{ number_format($p->total / 1000, 0) }}K</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Monthly PAR trend --}}
    <div class="card mb-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">6-Month PAR Trend</h3>
        <div class="flex items-end gap-3 h-28">
            @php $maxTrend = max(collect($trend)->pluck('amount')->max(), 1); @endphp
            @foreach($trend as $t)
            @php $h = max(8, ($t['amount'] / $maxTrend) * 100); @endphp
            <div class="flex-1 flex flex-col items-center gap-2">
                <span class="text-xs text-gray-400 font-mono">{{ $t['amount'] > 0 ? '₦' . number_format($t['amount']/1000,0).'K' : '—' }}</span>
                <div class="w-full rounded-t-md {{ $t['amount'] > 0 ? 'bg-red-400' : 'bg-gray-200' }}" style="height:{{ $h }}px"></div>
                <span class="text-xs text-gray-500 font-semibold">{{ $t['month'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top overdue loans --}}
    @if($topOverdue->isNotEmpty())
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-bankos-border">
            <h3 class="text-sm font-semibold text-gray-700">Top Overdue Loans (Largest Exposure)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-bankos-border">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Outstanding</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Principal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Next Due</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($topOverdue as $loan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $loan->loan_reference }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            {{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-right">₦{{ number_format($loan->outstanding_balance, 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-right">₦{{ number_format($loan->principal_amount, 0) }}</td>
                        <td class="px-4 py-3 text-xs text-red-500 font-semibold">{{ $loan->next_due_date ? \Carbon\Carbon::parse($loan->next_due_date)->format('d M Y') : '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('loans.show', $loan->id) }}" class="text-xs font-semibold text-bankos-primary hover:underline">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
