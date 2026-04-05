<x-app-layout>
    <x-slot name="header">Customer Risk Profile - {{ $customer->first_name }} {{ $customer->last_name }}</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('compliance-auto.risk-scoring') }}" class="text-bankos-primary hover:underline text-sm">&larr; Back to Risk Scoring</a>
            </div>
            <form method="POST" action="{{ route('compliance-auto.recalculate-risk', $customer->id) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Recalculate Risk</button>
            </form>
        </div>

        {{-- Customer Info & Score --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Customer Info</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-bankos-muted">Name</dt><dd class="text-bankos-text dark:text-bankos-dark-text font-medium">{{ $customer->first_name }} {{ $customer->last_name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Customer No</dt><dd class="font-mono">{{ $customer->customer_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">KYC Status</dt><dd><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $customer->kyc_status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($customer->kyc_status) }}</span></dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">BVN Verified</dt><dd>{{ $customer->bvn_verified ? 'Yes' : 'No' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Type</dt><dd>{{ ucfirst($customer->type ?? 'individual') }}</dd></div>
                </dl>
            </div>

            @if($riskScore)
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Risk Score</h3>
                <div class="text-center mb-4">
                    @php
                        $color = match($riskScore->risk_level) {
                            'critical' => 'text-red-600',
                            'high' => 'text-orange-600',
                            'medium' => 'text-yellow-600',
                            default => 'text-green-600',
                        };
                    @endphp
                    <span class="text-5xl font-bold {{ $color }}">{{ $riskScore->overall_score }}</span>
                    <span class="text-lg text-bankos-muted">/100</span>
                    <p class="text-sm font-semibold mt-1 {{ $color }}">{{ strtoupper($riskScore->risk_level) }}</p>
                    <p class="text-xs text-bankos-muted mt-1">Assessed {{ $riskScore->last_assessed_at?->diffForHumans() ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Score Breakdown</h3>
                <canvas id="breakdownRadar" height="200"></canvas>
            </div>
            @else
            <div class="lg:col-span-2 bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 flex items-center justify-center">
                <p class="text-bankos-muted">No risk score available. Click "Recalculate Risk" to generate.</p>
            </div>
            @endif
        </div>

        {{-- Risk Factors --}}
        @if($riskScore && !empty($riskScore->risk_factors))
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-4">Risk Factors</h3>
            <div class="space-y-3">
                @foreach($riskScore->risk_factors as $factor)
                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-bankos-dark-bg rounded-lg">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $factor['description'] ?? $factor['factor'] }}</p>
                        <p class="text-xs text-bankos-muted">Factor: {{ $factor['factor'] ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold {{ ($factor['score'] ?? 0) >= 10 ? 'text-red-600' : 'text-yellow-600' }}">+{{ $factor['score'] ?? 0 }}</span>
                        <p class="text-xs text-bankos-muted">/ {{ $factor['weight'] ?? 0 }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recent Screenings --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Transaction Screenings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Result</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Confidence</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Disposition</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($screenings as $s)
                        <tr>
                            <td class="px-4 py-3 text-xs">{{ $s->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $s->screening_type)) }}</td>
                            <td class="px-4 py-3">
                                @php $rc = match($s->result) { 'clear' => 'bg-green-100 text-green-700', 'match' => 'bg-red-100 text-red-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rc }}">{{ strtoupper(str_replace('_', ' ', $s->result)) }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->confidence }}%</td>
                            <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $s->disposition)) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-bankos-muted">No screenings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($riskScore && !empty($riskScore->score_breakdown))
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('breakdownRadar'), {
            type: 'radar',
            data: {
                labels: {!! json_encode(array_map(fn($k) => ucfirst($k), array_keys($riskScore->score_breakdown))) !!},
                datasets: [{
                    label: 'Score',
                    data: {!! json_encode(array_values($riskScore->score_breakdown)) !!},
                    backgroundColor: 'rgba(239,68,68,0.2)',
                    borderColor: '#ef4444',
                    pointBackgroundColor: '#ef4444',
                }]
            },
            options: { responsive: true, scales: { r: { beginAtZero: true, max: 25 } }, plugins: { legend: { display: false } } }
        });
    </script>
    @endpush
    @endif
</x-app-layout>
