<x-app-layout>
    <x-slot name="header">AML / Compliance Centre</x-slot>

    <div class="space-y-6" x-data="{
        tab: '{{ request('tab', 'all') }}',
        screenOpen: false,
        screenName: '',
        screening: false,
        screenResults: null,
        async doScreen() {
            if (!this.screenName.trim()) return;
            this.screening = true;
            this.screenResults = null;
            try {
                const res = await fetch('{{ route('aml.screen') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: this.screenName })
                });
                this.screenResults = await res.json();
            } catch (e) {
                this.screenResults = { match: false, matches: [], error: true };
            }
            this.screening = false;
        }
    }">

        {{-- Flash --}}
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Open Alerts</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $openCount }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">High / Critical</p>
                <p class="text-3xl font-bold text-orange-500 mt-1">{{ $critHighCount }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Pending STRs</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $pendingStrs }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Sanctions Hits Today</p>
                <p class="text-3xl font-bold text-purple-600 mt-1">{{ $sanctionsToday }}</p>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">

            {{-- Card Header: Tabs + Screen Form --}}
            <div class="flex flex-col md:flex-row md:items-center gap-4 p-4 border-b border-bankos-border dark:border-bankos-dark-border">

                {{-- Filter Tabs --}}
                <div class="flex items-center gap-1 flex-wrap">
                    @foreach(['all' => 'All', 'open' => 'Open', 'under_review' => 'Under Review', 'escalated' => 'Escalated', 'critical' => 'Critical'] as $tabKey => $tabLabel)
                    <a href="{{ request()->fullUrlWithQuery(['tab' => $tabKey]) }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                           {{ request('tab', 'all') === $tabKey
                               ? 'bg-bankos-primary text-white'
                               : 'bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-200' }}">
                        {{ $tabLabel }}
                    </a>
                    @endforeach
                </div>

                {{-- Sanctions Screen Form --}}
                <div class="md:ml-auto flex items-center gap-2">
                    <button @click="screenOpen = !screenOpen"
                            class="flex items-center gap-2 px-3 py-1.5 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Screen Name
                    </button>
                </div>
            </div>

            {{-- Inline Screen Panel --}}
            <div x-show="screenOpen" x-transition class="p-4 bg-purple-50 dark:bg-purple-900/10 border-b border-bankos-border dark:border-bankos-dark-border">
                <p class="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-3">Sanctions / Watchlist Screening</p>
                <div class="flex gap-2">
                    <input x-model="screenName" type="text" placeholder="Enter full name to screen..."
                           class="flex-1 border border-bankos-border dark:border-bankos-dark-border rounded-lg px-3 py-2 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           @keydown.enter="doScreen()">
                    <button @click="doScreen()" :disabled="screening"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg disabled:opacity-50 transition-colors">
                        <span x-show="!screening">Screen</span>
                        <span x-show="screening">Checking...</span>
                    </button>
                </div>

                <div x-show="screenResults !== null" class="mt-3">
                    <template x-if="screenResults && screenResults.match">
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-red-700">MATCH FOUND — <span x-text="screenResults.matches.length"></span> result(s)</p>
                            <template x-for="m in screenResults.matches" :key="m.id">
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-lg p-3 text-sm">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-red-800" x-text="m.full_name"></p>
                                            <p class="text-red-600 text-xs mt-0.5" x-text="m.reason"></p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"
                                                  x-text="m.confidence + '% confidence'"></span>
                                            <p class="text-xs text-red-500 mt-1" x-text="m.list_source + ' · ' + m.entity_type"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="screenResults && !screenResults.match && !screenResults.error">
                        <p class="text-sm text-green-700 font-medium">No matches found on any sanctions list.</p>
                    </template>
                    <template x-if="screenResults && screenResults.error">
                        <p class="text-sm text-red-600">Screening failed. Please try again.</p>
                    </template>
                </div>
            </div>

            {{-- Filters Bar --}}
            <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="tab" value="{{ request('tab', 'all') }}">
                    <div>
                        <label class="block text-xs font-medium text-bankos-muted mb-1">Severity</label>
                        <select name="severity" class="border border-bankos-border dark:border-bankos-dark-border rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                            <option value="">All Severities</option>
                            @foreach(['low', 'medium', 'high', 'critical'] as $s)
                            <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-bankos-muted mb-1">From</label>
                        <input type="date" name="from" value="{{ request('from') }}"
                               class="border border-bankos-border dark:border-bankos-dark-border rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-bankos-muted mb-1">To</label>
                        <input type="date" name="to" value="{{ request('to') }}"
                               class="border border-bankos-border dark:border-bankos-dark-border rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                    </div>
                    <button type="submit" class="px-4 py-1.5 bg-bankos-primary hover:bg-bankos-primary-dark text-white text-sm rounded-lg transition-colors">Filter</button>
                    @if(request()->anyFilled(['severity', 'from', 'to']))
                    <a href="{{ route('aml.index') }}" class="px-4 py-1.5 text-sm text-bankos-muted hover:text-bankos-text border border-bankos-border rounded-lg transition-colors">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Alert Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg">
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Severity</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider w-32">Score</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-bankos-muted text-xs uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($alerts as $alert)
                        @php
                            $customer = $customers[$alert->customer_id] ?? null;
                            $amount   = $alert->details['amount'] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                            <td class="px-4 py-3 text-bankos-muted whitespace-nowrap text-xs">
                                {{ $alert->created_at->format('d M Y') }}<br>
                                <span class="text-bankos-muted/70">{{ $alert->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeColors = [
                                        'velocity'        => 'bg-blue-100 text-blue-700',
                                        'large_amount'    => 'bg-orange-100 text-orange-700',
                                        'structuring'     => 'bg-red-100 text-red-700',
                                        'sanctions_match' => 'bg-purple-100 text-purple-700',
                                        'pep_match'       => 'bg-violet-100 text-violet-700',
                                        'unusual_pattern' => 'bg-amber-100 text-amber-700',
                                        'round_amount'    => 'bg-sky-100 text-sky-700',
                                        'rapid_movement'  => 'bg-teal-100 text-teal-700',
                                    ];
                                    $typeColor = $typeColors[$alert->alert_type] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800';
                                    $typeLabel = str_replace('_', ' ', ucwords($alert->alert_type, '_'));
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $sevColors = [
                                        'critical' => 'bg-red-100 text-red-700 ring-1 ring-red-300',
                                        'high'     => 'bg-orange-100 text-orange-700',
                                        'medium'   => 'bg-amber-100 text-amber-700',
                                        'low'      => 'bg-blue-100 text-blue-700',
                                    ];
                                    $sevColor = $sevColors[$alert->severity] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $sevColor }}">
                                    {{ strtoupper($alert->severity) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($customer)
                                <span class="font-medium text-bankos-text dark:text-bankos-dark-text">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                </span>
                                @else
                                <span class="text-bankos-muted text-xs">{{ Str::limit($alert->customer_id, 8) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-sm">
                                @if($amount)
                                ₦{{ number_format($amount, 2) }}
                                @else
                                <span class="text-bankos-muted">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 w-32">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        @php
                                            $scoreColor = $alert->score >= 80 ? 'bg-red-500' : ($alert->score >= 60 ? 'bg-orange-500' : ($alert->score >= 40 ? 'bg-amber-500' : 'bg-blue-400'));
                                        @endphp
                                        <div class="{{ $scoreColor }} h-2 rounded-full transition-all" style="width: {{ $alert->score }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold {{ $alert->score >= 80 ? 'text-red-600' : ($alert->score >= 60 ? 'text-orange-600' : 'text-bankos-muted') }}">{{ $alert->score }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statColors = [
                                        'open'         => 'bg-red-100 text-red-700',
                                        'under_review' => 'bg-amber-100 text-amber-700',
                                        'escalated'    => 'bg-orange-100 text-orange-700',
                                        'dismissed'    => 'bg-gray-100 text-gray-500',
                                        'reported'     => 'bg-green-100 text-green-700',
                                    ];
                                    $statColor = $statColors[$alert->status] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800';
                                    $statLabel = str_replace('_', ' ', ucwords($alert->status, '_'));
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statColor }}">
                                    {{ $statLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('aml.show', $alert->id) }}"
                                       class="text-xs text-bankos-primary hover:underline font-medium">Review</a>
                                    @if($alert->status === 'open')
                                    <form method="POST" action="{{ route('aml.review', $alert->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="status" value="dismissed">
                                        <button type="submit" class="text-xs text-bankos-muted hover:text-red-500 transition-colors"
                                                onclick="return confirm('Dismiss this alert?')">Dismiss</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-bankos-muted">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <p class="text-sm font-medium">No AML alerts found</p>
                                <p class="text-xs mt-1">Alerts are generated automatically during transaction processing.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($alerts->hasPages())
            <div class="px-4 py-3 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $alerts->links() }}
            </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('aml.limits') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border rounded-lg text-sm hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                Manage Limits
            </a>
            <a href="{{ route('aml.rules') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border rounded-lg text-sm hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                AML Rules
            </a>
            <a href="{{ route('aml.str.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-bankos-dark-surface border border-bankos-border dark:border-bankos-dark-border rounded-lg text-sm hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                STR Reports
                @if($pendingStrs > 0)
                <span class="bg-amber-500 text-white text-xs font-bold px-1.5 rounded-full">{{ $pendingStrs }}</span>
                @endif
            </a>
        </div>

    </div>
</x-app-layout>
