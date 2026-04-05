<x-app-layout>
    <x-slot name="header">Regulatory Change - {{ Str::limit($change->title, 50) }}</x-slot>

    <div class="space-y-6">

        <a href="{{ route('compliance-auto.regulatory-changes') }}" class="text-bankos-primary hover:underline text-sm">&larr; Back to Regulatory Changes</a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Details --}}
            <div class="lg:col-span-2 bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $change->regulator }}</span>
                    @php $ic = match($change->impact_level) { 'critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ic }}">{{ strtoupper($change->impact_level) }} IMPACT</span>
                    @php $stc = match($change->status) { 'implemented' => 'bg-green-100 text-green-700', 'impact_assessed' => 'bg-blue-100 text-blue-700', 'under_review' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ ucfirst(str_replace('_', ' ', $change->status)) }}</span>
                </div>
                <h2 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text mb-2">{{ $change->title }}</h2>
                @if($change->reference_number)
                <p class="text-sm text-bankos-muted mb-4">Reference: {{ $change->reference_number }}</p>
                @endif
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <h4>Summary</h4>
                    <p>{{ $change->summary }}</p>
                    @if($change->full_text)
                    <h4>Full Text</h4>
                    <p class="whitespace-pre-wrap">{{ $change->full_text }}</p>
                    @endif
                </div>
            </div>

            {{-- Metadata --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Key Dates</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-bankos-muted">Published</dt><dd>{{ $change->published_date?->format('M d, Y') ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-bankos-muted">Effective</dt><dd>{{ $change->effective_date?->format('M d, Y') ?? '-' }}</dd></div>
                    </dl>
                </div>

                @if(!empty($change->affected_areas))
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Affected Areas</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($change->affected_areas as $area)
                        <span class="px-2 py-1 bg-gray-100 dark:bg-bankos-dark-bg rounded text-xs">{{ strtoupper($area) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($change->implementation_plan)
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Implementation Plan</h3>
                    <p class="text-sm text-bankos-text dark:text-bankos-dark-text whitespace-pre-wrap">{{ $change->implementation_plan }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
