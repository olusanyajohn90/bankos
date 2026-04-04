<x-app-layout>
    <x-slot name="header">Control: {{ $control->control_ref }}</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-bankos-muted">
            <a href="{{ route('compliance-auto.frameworks') }}" class="hover:text-bankos-primary">Frameworks</a>
            <span>/</span>
            <a href="{{ route('compliance-auto.frameworks.show', $control->framework_id) }}" class="hover:text-bankos-primary">{{ $control->framework->name ?? 'Framework' }}</a>
            <span>/</span>
            <span class="text-bankos-text dark:text-bankos-dark-text">{{ $control->control_ref }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Status Card --}}
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">{{ $control->title }}</h2>
                            <p class="text-sm text-bankos-muted mt-1">{{ $control->category }} | {{ $control->framework->name ?? '' }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            {{ $control->status === 'compliant' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' :
                               ($control->status === 'partial' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' :
                               ($control->status === 'non_compliant' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400' :
                               'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400')) }}">
                            {{ str_replace('_', ' ', ucfirst($control->status)) }}
                        </span>
                    </div>
                    @if($control->description)
                    <p class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $control->description }}</p>
                    @endif
                </div>

                {{-- Update Form --}}
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Update Control</h3>
                    <form method="POST" action="{{ route('compliance-auto.controls.update', $control->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Status</label>
                                <select name="status" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="compliant" {{ $control->status === 'compliant' ? 'selected' : '' }}>Compliant</option>
                                    <option value="partial" {{ $control->status === 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="non_compliant" {{ $control->status === 'non_compliant' ? 'selected' : '' }}>Non-Compliant</option>
                                    <option value="not_assessed" {{ $control->status === 'not_assessed' ? 'selected' : '' }}>Not Assessed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Priority</label>
                                <select name="priority" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="1" {{ $control->priority == 1 ? 'selected' : '' }}>Critical</option>
                                    <option value="2" {{ $control->priority == 2 ? 'selected' : '' }}>High</option>
                                    <option value="3" {{ $control->priority == 3 ? 'selected' : '' }}>Medium</option>
                                    <option value="4" {{ $control->priority == 4 ? 'selected' : '' }}>Low</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Assigned To</label>
                                <select name="assigned_to" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $control->assigned_to === $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-bankos-muted mb-1">Evidence Notes</label>
                            <textarea name="evidence_notes" rows="3" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">{{ $control->evidence_notes }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Remediation Plan</label>
                                <textarea name="remediation_plan" rows="3" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">{{ $control->remediation_plan }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Remediation Due Date</label>
                                <input type="date" name="remediation_due" value="{{ $control->remediation_due?->format('Y-m-d') }}" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 transition-colors">Update Control</button>
                    </form>
                </div>

                {{-- Evidence Upload --}}
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Upload Evidence</h3>
                    <form method="POST" action="{{ route('compliance-auto.controls.evidence', $control->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Title</label>
                                <input type="text" name="title" required class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1">Type</label>
                                <select name="type" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                                    <option value="document">Document</option>
                                    <option value="screenshot">Screenshot</option>
                                    <option value="query_result">Query Result</option>
                                    <option value="api_response">API Response</option>
                                    <option value="manual_note">Manual Note</option>
                                    <option value="system_log">System Log</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-bankos-muted mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-bankos-muted mb-1">File</label>
                            <input type="file" name="file" required class="w-full text-sm">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition-colors">Upload Evidence</button>
                    </form>
                </div>

                {{-- Evidence List --}}
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
                    <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Evidence ({{ $control->evidence->count() }})</h3>
                    </div>
                    <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($control->evidence as $ev)
                        <div class="p-4 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                {{ $ev->is_auto_collected ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $ev->title }}</p>
                                <p class="text-xs text-bankos-muted mt-0.5">{{ $ev->type }} | {{ $ev->collected_at?->format('M d, Y') ?? 'N/A' }}
                                    {{ $ev->is_auto_collected ? '| Auto-collected' : '' }}
                                </p>
                                @if($ev->description)
                                <p class="text-xs text-bankos-muted mt-1">{{ $ev->description }}</p>
                                @endif
                            </div>
                            @if($ev->file_path)
                            <a href="{{ asset('storage/' . $ev->file_path) }}" target="_blank" class="text-xs text-bankos-primary hover:underline">View</a>
                            @endif
                        </div>
                        @empty
                        <div class="p-4 text-center text-sm text-bankos-muted">No evidence collected yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Auto-Check Results --}}
                @if($control->monitors->count() > 0)
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-3">Linked Monitors</h3>
                    @foreach($control->monitors as $mon)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-bankos-border dark:border-bankos-dark-border' : '' }}">
                        <span class="text-sm">{{ $mon->name }}</span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $mon->status === 'passing' ? 'bg-green-500' : ($mon->status === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                            <span class="text-sm font-medium">{{ $mon->current_value }}</span>
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Activity Timeline --}}
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                    <h3 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text mb-3">Activity Timeline</h3>
                    <div class="space-y-3">
                        @forelse($auditTrail as $entry)
                        <div class="flex items-start gap-2">
                            <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                                {{ $entry->event_type === 'breach' ? 'bg-red-500' :
                                   ($entry->event_type === 'warning' ? 'bg-amber-500' :
                                   ($entry->event_type === 'evidence_added' ? 'bg-blue-500' :
                                   ($entry->event_type === 'status_changed' ? 'bg-purple-500' : 'bg-gray-400'))) }}"></div>
                            <div>
                                <p class="text-xs text-bankos-text dark:text-bankos-dark-text">{{ $entry->description }}</p>
                                <p class="text-xs text-bankos-muted">{{ $entry->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-bankos-muted">No activity yet.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
