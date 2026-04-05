<x-app-layout>
    <x-slot name="header">Beneficial Ownership</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Add Owner Form --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Add Beneficial Owner</h3>
            <form method="POST" action="{{ route('compliance-auto.beneficial-ownership.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Corporate Customer</label>
                    <select name="customer_id" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="">Select...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->customer_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Owner Name</label>
                    <input type="text" name="owner_name" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Ownership %</label>
                    <input type="number" name="ownership_percentage" step="0.01" min="0" max="100" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                </div>
                <div class="flex items-end gap-2">
                    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_pep" value="1"> PEP</label>
                    <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="is_sanctioned" value="1"> Sanctioned</label>
                    <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Add</button>
                </div>
            </form>
        </div>

        {{-- Owners Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Owner Name</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Corporate Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Ownership %</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Nationality</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Flags</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Verification</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($owners as $o)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3 font-medium">{{ $o->owner_name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $o->customer->first_name ?? '' }} {{ $o->customer->last_name ?? '' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-blue-500" style="width: {{ $o->ownership_percentage }}%"></div>
                                    </div>
                                    <span class="text-xs font-mono">{{ $o->ownership_percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $o->nationality ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($o->is_pep)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 mr-1">PEP</span>
                                @endif
                                @if($o->is_sanctioned)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">SANCTIONED</span>
                                @endif
                                @if(!$o->is_pep && !$o->is_sanctioned)
                                <span class="text-xs text-bankos-muted">Clear</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $vc = match($o->verification_status) { 'verified' => 'bg-green-100 text-green-700', 'failed' => 'bg-red-100 text-red-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $vc }}">{{ ucfirst($o->verification_status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No beneficial owners found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
