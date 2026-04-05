<x-app-layout>
    <x-slot name="header">Regulatory Simulations</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Create Simulation --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Create What-If Simulation</h3>
            <form method="POST" action="{{ route('compliance-auto.simulations.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Simulation Name</label>
                    <input type="text" name="name" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. CAR Increase to 15%">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Regulation Change</label>
                    <select name="scenario_params[regulation_change]" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="increase_car">Increase Capital Adequacy Ratio</option>
                        <option value="stricter_kyc_cdd">Stricter KYC/CDD Requirements</option>
                        <option value="new_reporting_threshold">New Reporting Thresholds</option>
                        <option value="liquidity_buffer_increase">Liquidity Buffer Increase</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Create</button>
                </div>
            </form>
        </div>

        {{-- Simulations --}}
        <div class="space-y-4">
            @forelse($simulations as $sim)
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $sim->name }}</h3>
                        <p class="text-sm text-bankos-muted">{{ $sim->description }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @php $stc = match($sim->status) { 'completed' => 'bg-green-100 text-green-700', 'running' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ strtoupper($sim->status) }}</span>
                        @if($sim->status === 'draft')
                        <form method="POST" action="{{ route('compliance-auto.simulations.run', $sim->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-bankos-primary text-white rounded-lg text-xs hover:bg-bankos-primary/90">Run Simulation</button>
                        </form>
                        @endif
                    </div>
                </div>

                @if($sim->status === 'completed' && $sim->baseline_metrics && $sim->simulated_metrics)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-bankos-muted uppercase mb-2">Baseline Metrics</h4>
                        <dl class="space-y-1 text-sm">
                            @foreach($sim->baseline_metrics as $key => $val)
                            <div class="flex justify-between">
                                <dt class="text-bankos-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="font-mono">{{ is_numeric($val) ? number_format($val, 2) : $val }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <h4 class="text-xs font-semibold text-blue-800 dark:text-blue-300 uppercase mb-2">Simulated Metrics</h4>
                        <dl class="space-y-1 text-sm">
                            @foreach($sim->simulated_metrics as $key => $val)
                            <div class="flex justify-between">
                                <dt class="text-bankos-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="font-mono">{{ is_numeric($val) ? number_format($val, 2) : $val }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                </div>

                @if($sim->impact_analysis)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 mb-4">
                    <h4 class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 uppercase mb-2">Impact Analysis</h4>
                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        @foreach($sim->impact_analysis as $key => $val)
                        <div>
                            <dt class="text-xs text-bankos-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="font-mono font-bold">{{ is_numeric($val) ? number_format($val, 2) : (is_array($val) ? implode(', ', $val) : $val) }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
                @endif

                @if($sim->ai_recommendation)
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-indigo-800 dark:text-indigo-300 uppercase mb-1">AI Recommendation</h4>
                    <p class="text-sm text-indigo-700 dark:text-indigo-400">{{ $sim->ai_recommendation }}</p>
                </div>
                @endif
                @endif
            </div>
            @empty
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-8 text-center">
                <p class="text-bankos-muted">No simulations created yet. Use the form above to create a "what-if" regulatory simulation.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
