<x-app-layout>
    <x-slot name="header">Compliance Scenario Testing</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Create Scenario --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Create Test Scenario</h3>
            <form method="POST" action="{{ route('compliance-auto.scenarios.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Name</label>
                    <input type="text" name="name" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Category</label>
                    <select name="category" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="aml">AML</option>
                        <option value="fraud">Fraud</option>
                        <option value="sanctions">Sanctions</option>
                        <option value="kyc">KYC</option>
                        <option value="regulatory">Regulatory</option>
                        <option value="stress_test">Stress Test</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Test Type</label>
                    <select name="test_config[type]" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="transaction_simulation">Transaction Simulation</option>
                        <option value="sanctions_check">Sanctions Check</option>
                        <option value="kyc_validation">KYC Validation</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Create</button>
                </div>
            </form>
        </div>

        {{-- Scenarios Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Name</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Result</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Last Run</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Outcome</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($scenarios as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3 font-medium">{{ $s->name }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ strtoupper($s->category) }}</span></td>
                            <td class="px-4 py-3">
                                @php $rc = match($s->result) { 'passed' => 'bg-green-100 text-green-700', 'failed' => 'bg-red-100 text-red-700', 'partial' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rc }}">{{ strtoupper($s->result) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $s->last_run_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($s->actual_outcome)
                                <span class="font-mono">{{ Str::limit(json_encode($s->actual_outcome), 50) }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('compliance-auto.scenarios.run', $s->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-bankos-primary text-white rounded text-xs hover:bg-bankos-primary/90">Run Test</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No scenarios created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
