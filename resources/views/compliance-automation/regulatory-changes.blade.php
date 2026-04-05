<x-app-layout>
    <x-slot name="header">Regulatory Change Tracker</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Add New Change --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Add Regulatory Change</h3>
            <form method="POST" action="{{ route('compliance-auto.regulatory-changes.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Regulator</label>
                    <select name="regulator" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="CBN">CBN</option>
                        <option value="NDIC">NDIC</option>
                        <option value="NFIU">NFIU</option>
                        <option value="SEC">SEC</option>
                        <option value="FCCPC">FCCPC</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Title</label>
                    <input type="text" name="title" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Impact Level</label>
                    <select name="impact_level" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-bankos-muted mb-1">Summary</label>
                    <textarea name="summary" required rows="2" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2"></textarea>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Add Change</button>
                </div>
            </form>
        </div>

        {{-- Changes Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Regulator</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Title</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Impact</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Effective Date</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($changes as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ $c->regulator }}</span></td>
                            <td class="px-4 py-3 max-w-xs">
                                <span class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ Str::limit($c->title, 60) }}</span>
                                @if($c->reference_number)
                                <span class="text-xs text-bankos-muted ml-1">({{ $c->reference_number }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $ic = match($c->impact_level) { 'critical' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ic }}">{{ strtoupper($c->impact_level) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @php $stc = match($c->status) { 'implemented' => 'bg-green-100 text-green-700', 'impact_assessed' => 'bg-blue-100 text-blue-700', 'under_review' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ ucfirst(str_replace('_', ' ', $c->status)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $c->effective_date?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('compliance-auto.regulatory-changes.show', $c->id) }}" class="text-bankos-primary hover:underline text-xs font-medium">Details</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No regulatory changes tracked.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
