<x-app-layout>
    <x-slot name="header">Compliance Frameworks</x-slot>

    <div class="space-y-6">

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <p class="text-sm text-bankos-muted">Manage regulatory compliance frameworks and track adherence across all controls.</p>
            <a href="{{ route('compliance-auto.dashboard') }}" class="text-sm text-bankos-primary hover:underline">Back to Command Center</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($frameworks as $fw)
            <a href="{{ route('compliance-auto.frameworks.show', $fw->id) }}" class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 hover:shadow-lg transition-shadow group">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-bankos-text dark:text-bankos-dark-text group-hover:text-bankos-primary transition-colors">{{ $fw->name }}</h3>
                        <span class="text-xs text-bankos-muted uppercase tracking-wide">{{ $fw->code }}</span>
                    </div>
                    <span class="text-3xl font-bold {{ $fw->compliance_score >= 80 ? 'text-green-600' : ($fw->compliance_score >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $fw->compliance_score }}%</span>
                </div>

                @if($fw->description)
                <p class="text-sm text-bankos-muted mb-4 line-clamp-2">{{ $fw->description }}</p>
                @endif

                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-4">
                    <div class="h-3 rounded-full transition-all {{ $fw->compliance_score >= 80 ? 'bg-green-500' : ($fw->compliance_score >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $fw->compliance_score }}%"></div>
                </div>

                {{-- Status breakdown --}}
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <div class="text-center">
                        <p class="text-lg font-bold text-green-600">{{ $fw->compliant_count ?? 0 }}</p>
                        <p class="text-xs text-bankos-muted">Compliant</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-amber-500">{{ $fw->partial_count ?? 0 }}</p>
                        <p class="text-xs text-bankos-muted">Partial</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-red-600">{{ $fw->non_compliant_count ?? 0 }}</p>
                        <p class="text-xs text-bankos-muted">Non-Comp.</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-400">{{ $fw->not_assessed_count ?? 0 }}</p>
                        <p class="text-xs text-bankos-muted">Pending</p>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-bankos-muted border-t border-bankos-border dark:border-bankos-dark-border pt-3">
                    <span>{{ $fw->controls_count ?? 0 }} total controls</span>
                    <span>Last assessed: {{ $fw->last_assessed_at ? $fw->last_assessed_at->format('M d, Y') : 'Never' }}</span>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-12 text-bankos-muted">No compliance frameworks configured yet.</div>
            @endforelse
        </div>

    </div>
</x-app-layout>
