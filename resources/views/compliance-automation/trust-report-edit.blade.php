<x-app-layout>
    <x-slot name="header">Trust Report Editor</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        @if($report)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Editor --}}
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('compliance-auto.trust-report.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 space-y-6">

                        {{-- Published toggle --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Publish Status</h3>
                                <p class="text-xs text-bankos-muted mt-0.5">Make your trust report publicly accessible.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_published" value="0">
                                <input type="checkbox" name="is_published" value="1" {{ $report->is_published ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                                <span class="ml-3 text-sm font-medium {{ $report->is_published ? 'text-green-600' : 'text-bankos-muted' }}">{{ $report->is_published ? 'Published' : 'Unpublished' }}</span>
                            </label>
                        </div>

                        <hr class="border-bankos-border dark:border-bankos-dark-border">

                        {{-- Logo upload --}}
                        <div>
                            <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Bank Logo</label>
                            @if($report->logo_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $report->logo_path) }}" alt="Logo" class="h-12">
                            </div>
                            @endif
                            <input type="file" name="logo" accept="image/*" class="text-sm">
                        </div>

                        {{-- Intro text --}}
                        <div>
                            <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Introduction Text</label>
                            <textarea name="intro_text" rows="4" class="w-full rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Enter a welcome message for your trust report...">{{ $report->intro_text }}</textarea>
                        </div>

                        {{-- Framework selection --}}
                        <div>
                            <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Visible Frameworks</label>
                            <p class="text-xs text-bankos-muted mb-3">Select which frameworks to display on the public trust report.</p>
                            <div class="space-y-2">
                                @foreach($frameworks as $fw)
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-bankos-border dark:border-bankos-dark-border hover:bg-gray-50 dark:hover:bg-bankos-dark-bg cursor-pointer">
                                    <input type="checkbox" name="visible_frameworks[]" value="{{ $fw->id }}"
                                        {{ in_array($fw->id, $report->visible_frameworks ?? []) ? 'checked' : '' }}
                                        class="rounded border-gray-300">
                                    <div class="flex-1">
                                        <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $fw->name }}</span>
                                        <span class="text-xs text-bankos-muted ml-2">{{ $fw->compliance_score }}%</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Share Panel --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Share Link</h3>
                    <p class="text-xs text-bankos-muted mb-3">Share this link with auditors, regulators, or partners.</p>

                    <div class="flex items-center gap-2" x-data="{ copied: false }">
                        <input type="text" value="{{ $publicUrl }}" readonly class="flex-1 rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg text-xs px-3 py-2">
                        <button @click="navigator.clipboard.writeText('{{ $publicUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="px-3 py-2 bg-bankos-primary text-white rounded-lg text-xs hover:bg-bankos-primary/90 transition-colors">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied!</span>
                        </button>
                    </div>

                    @if($report->is_published)
                    <a href="{{ $publicUrl }}" target="_blank" class="mt-3 inline-block text-sm text-bankos-primary hover:underline">Open Trust Report</a>
                    @else
                    <p class="mt-3 text-xs text-amber-600">Report is not published yet. Publish it to make the link work.</p>
                    @endif
                </div>

                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-3">Status</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-bankos-muted">Published</span>
                            <span class="{{ $report->is_published ? 'text-green-600' : 'text-red-600' }}">{{ $report->is_published ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-bankos-muted">Frameworks</span>
                            <span>{{ count($report->visible_frameworks ?? []) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-bankos-muted">Last Updated</span>
                            <span>{{ $report->updated_at?->format('M d, Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-12 text-bankos-muted">Unable to load trust report.</div>
        @endif

    </div>
</x-app-layout>
