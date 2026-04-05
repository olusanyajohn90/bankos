<x-app-layout>
    <x-slot name="header">Adverse Media Screening</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Results Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Adverse Media Results</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Source</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Headline</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Severity</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Disposition</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($results as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3 font-medium">{{ $r->customer->first_name ?? '' }} {{ $r->customer->last_name ?? '' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $r->source }}</td>
                            <td class="px-4 py-3 text-xs max-w-xs truncate">
                                @if($r->url)
                                <a href="{{ $r->url }}" target="_blank" class="text-bankos-primary hover:underline">{{ Str::limit($r->headline, 60) }}</a>
                                @else
                                {{ Str::limit($r->headline, 60) }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $r->category)) }}</td>
                            <td class="px-4 py-3">
                                @php $svc = match($r->severity) { 'critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $svc }}">{{ strtoupper($r->severity) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php $dpc = match($r->disposition) { 'relevant' => 'bg-red-100 text-red-700', 'irrelevant' => 'bg-green-100 text-green-700', 'escalated' => 'bg-purple-100 text-purple-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $dpc }}">{{ ucfirst($r->disposition) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $r->published_date?->format('M d, Y') ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No adverse media results found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
