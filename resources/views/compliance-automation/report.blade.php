<x-app-layout>
    <x-slot name="header">Compliance Report</x-slot>

    <div class="space-y-6" id="complianceReport">

        {{-- Report Header --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">Compliance Status Report</h1>
                    <p class="text-sm text-bankos-muted mt-1">{{ $tenant->name ?? 'Institution' }} | Generated {{ now()->format('F d, Y H:i') }}</p>
                </div>
                <button onclick="window.print()" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90 print:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline mr-1"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print Report
                </button>
            </div>
        </div>

        {{-- Executive Summary --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-3">Executive Summary</h2>
            <div class="flex items-center gap-6 mb-4">
                <div class="text-center">
                    <p class="text-4xl font-bold {{ $overallScore >= 80 ? 'text-green-600' : ($overallScore >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $overallScore }}%</p>
                    <p class="text-xs text-bankos-muted">Overall Score</p>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-bankos-text dark:text-bankos-dark-text leading-relaxed">{{ $narrative }}</p>
                </div>
            </div>
        </div>

        {{-- Frameworks --}}
        @foreach($frameworks as $fw)
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-bankos-text dark:text-bankos-dark-text">{{ $fw->name }}</h3>
                <span class="text-2xl font-bold {{ $fw->compliance_score >= 80 ? 'text-green-600' : ($fw->compliance_score >= 60 ? 'text-amber-500' : 'text-red-600') }}">{{ $fw->compliance_score }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-4">
                <div class="h-2.5 rounded-full {{ $fw->compliance_score >= 80 ? 'bg-green-500' : ($fw->compliance_score >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $fw->compliance_score }}%"></div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Ref</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Title</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Priority</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border/50">
                        @foreach($fw->controls as $ctrl)
                        <tr>
                            <td class="py-2 font-mono text-xs">{{ $ctrl->control_ref }}</td>
                            <td class="py-2 text-bankos-text dark:text-bankos-dark-text">{{ $ctrl->title }}</td>
                            <td class="py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $ctrl->status === 'compliant' ? 'bg-green-100 text-green-800' :
                                       ($ctrl->status === 'partial' ? 'bg-amber-100 text-amber-800' :
                                       ($ctrl->status === 'non_compliant' ? 'bg-red-100 text-red-800' :
                                       'bg-gray-100 text-gray-600')) }}">
                                    {{ str_replace('_', ' ', ucfirst($ctrl->status)) }}
                                </span>
                            </td>
                            <td class="py-2 text-xs">{{ $ctrl->priorityLabel() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        {{-- Monitors --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Compliance Monitors</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border">
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Monitor</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Current</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Threshold</th>
                            <th class="text-left py-2 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border/50">
                        @foreach($monitors as $mon)
                        <tr>
                            <td class="py-2 text-bankos-text dark:text-bankos-dark-text">{{ $mon->name }}</td>
                            <td class="py-2 font-medium">{{ $mon->current_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}</td>
                            <td class="py-2 text-bankos-muted">{{ $mon->threshold_value }}{{ $mon->check_type === 'str_response' || $mon->check_type === 'data_breach' ? 'hrs' : '%' }}</td>
                            <td class="py-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium
                                    {{ $mon->status === 'passing' ? 'bg-green-100 text-green-800' :
                                       ($mon->status === 'warning' ? 'bg-amber-100 text-amber-800' :
                                       'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($mon->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
