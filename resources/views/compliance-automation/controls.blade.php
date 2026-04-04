<x-app-layout>
    <x-slot name="header">Compliance Controls</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search controls..." class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2 w-48">
                <select name="framework_id" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Frameworks</option>
                    @foreach($frameworks as $fId => $fName)
                    <option value="{{ $fId }}" {{ request('framework_id') === $fId ? 'selected' : '' }}>{{ $fName }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Statuses</option>
                    <option value="compliant" {{ request('status') === 'compliant' ? 'selected' : '' }}>Compliant</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="non_compliant" {{ request('status') === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                    <option value="not_assessed" {{ request('status') === 'not_assessed' ? 'selected' : '' }}>Not Assessed</option>
                </select>
                <select name="priority" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Priorities</option>
                    <option value="1" {{ request('priority') === '1' ? 'selected' : '' }}>Critical</option>
                    <option value="2" {{ request('priority') === '2' ? 'selected' : '' }}>High</option>
                    <option value="3" {{ request('priority') === '3' ? 'selected' : '' }}>Medium</option>
                    <option value="4" {{ request('priority') === '4' ? 'selected' : '' }}>Low</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Filter</button>
                <a href="{{ route('compliance-auto.controls') }}" class="px-4 py-2 bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text-sec rounded-lg text-sm hover:bg-gray-200">Reset</a>
            </form>
        </div>

        {{-- Controls Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Ref</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Title</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Framework</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Priority</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($controls as $ctrl)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg cursor-pointer" onclick="window.location='{{ route('compliance-auto.controls.show', $ctrl->id) }}'">
                            <td class="px-4 py-3 font-mono text-xs">{{ $ctrl->control_ref }}</td>
                            <td class="px-4 py-3 text-bankos-text dark:text-bankos-dark-text max-w-xs truncate">{{ $ctrl->title }}</td>
                            <td class="px-4 py-3 text-bankos-muted text-xs">{{ $ctrl->framework->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-bankos-muted">{{ $ctrl->category }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $ctrl->status === 'compliant' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' :
                                       ($ctrl->status === 'partial' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' :
                                       ($ctrl->status === 'non_compliant' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' :
                                       'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400')) }}">
                                    {{ str_replace('_', ' ', ucfirst($ctrl->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $ctrl->priority == 1 ? 'bg-red-100 text-red-800' :
                                       ($ctrl->priority == 2 ? 'bg-orange-100 text-orange-800' :
                                       ($ctrl->priority == 3 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ $ctrl->priorityLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $ctrl->assigned_to ? 'Assigned' : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No controls found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($controls->hasPages())
            <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $controls->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
