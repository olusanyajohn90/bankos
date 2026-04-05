<x-app-layout>
    <x-slot name="header">Transaction Screening Dashboard</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Clear</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['clear'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Potential Match</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['potential_match'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Match</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $stats['match'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Flagged</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ $stats['flagged'] ?? 0 }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">False Positive Rate</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $fpRate ?? 0 }}%</p>
            </div>
        </div>

        {{-- Screenings Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Screening Results</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Result</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Confidence</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Disposition</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($screenings as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3 text-xs">{{ $s->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $s->customer->first_name ?? '' }} {{ $s->customer->last_name ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $s->screening_type)) }}</td>
                            <td class="px-4 py-3">
                                @php $rc = match($s->result) { 'clear' => 'bg-green-100 text-green-700', 'match' => 'bg-red-100 text-red-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rc }}">{{ strtoupper(str_replace('_', ' ', $s->result)) }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->confidence }}%</td>
                            <td class="px-4 py-3">
                                @php $dc = match($s->disposition) { 'true_positive' => 'bg-red-100 text-red-700', 'false_positive' => 'bg-green-100 text-green-700', 'escalated' => 'bg-purple-100 text-purple-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $dc }}">{{ ucfirst(str_replace('_', ' ', $s->disposition)) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($s->disposition === 'pending')
                                <form method="POST" action="{{ route('compliance-auto.screening.review', $s->id) }}" class="flex gap-1">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="disposition" value="false_positive">
                                    <button type="submit" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">FP</button>
                                </form>
                                @else
                                <span class="text-xs text-bankos-muted">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No screening results found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
