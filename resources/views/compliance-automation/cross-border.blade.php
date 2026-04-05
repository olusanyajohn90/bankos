<x-app-layout>
    <x-slot name="header">Cross-border Compliance Rules</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Add Rule --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Add Country Rule</h3>
            <form method="POST" action="{{ route('compliance-auto.cross-border.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Country Code</label>
                    <input type="text" name="country_code" maxlength="3" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. GH">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Country Name</label>
                    <input type="text" name="country_name" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. Ghana">
                </div>
                <div>
                    <label class="block text-xs text-bankos-muted mb-1">Risk Category</label>
                    <select name="risk_category" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="prohibited">Prohibited</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Add Rule</button>
                </div>
            </form>
        </div>

        {{-- Rules Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Country</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Code</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Risk Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Requirements</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Restrictions</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($rules as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg {{ $r->risk_category === 'prohibited' ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            <td class="px-4 py-3 font-medium">{{ $r->country_name }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->country_code }}</td>
                            <td class="px-4 py-3">
                                @php $rc = match($r->risk_category) { 'prohibited' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', default => 'bg-green-100 text-green-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rc }}">{{ strtoupper($r->risk_category) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($r->requirements)
                                @foreach($r->requirements as $key => $val)
                                <span class="inline-block px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded text-xs mr-1 mb-1">{{ str_replace('_', ' ', $key) }}: {{ is_array($val) ? implode(', ', $val) : $val }}</span>
                                @endforeach
                                @else - @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($r->restrictions)
                                @foreach($r->restrictions as $key => $val)
                                <span class="inline-block px-1.5 py-0.5 bg-red-50 text-red-700 rounded text-xs mr-1 mb-1">{{ str_replace('_', ' ', $key) }}: {{ is_array($val) ? implode(', ', $val) : $val }}</span>
                                @endforeach
                                @else - @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($r->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bankos-muted">No cross-border rules defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
