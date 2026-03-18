<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Custom Reports</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Build, schedule and export custom data reports</p>
            </div>
            <a href="{{ route('custom-reports.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Report
            </a>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    {{-- Board Pack CTA --}}
    <div class="card mb-5 bg-gradient-to-r from-bankos-primary to-blue-700 text-white flex items-center justify-between">
        <div>
            <p class="font-bold text-lg">Board Pack Generator</p>
            <p class="text-sm opacity-80 mt-0.5">Generate comprehensive board reports with KPIs, financials, and loan analytics</p>
        </div>
        <a href="{{ route('board-pack.generate') }}" class="btn bg-white text-bankos-primary hover:bg-gray-50 flex items-center gap-2 flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Generate Board Pack
        </a>
    </div>

    @if($reports->isEmpty())
    <div class="card text-center py-16">
        <svg class="mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <p class="font-semibold text-gray-500 mb-2">No custom reports yet</p>
        <p class="text-sm text-gray-400 mb-4">Create your first report to start extracting insights from your data.</p>
        <a href="{{ route('custom-reports.create') }}" class="btn btn-primary">Create Report</a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($reports as $report)
        <div class="card flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <p class="font-semibold">{{ $report->name }}</p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ ucfirst(str_replace('_', ' ', $report->data_source)) }}</span>
                    @if(isset($schedules[$report->id]))
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Scheduled {{ ucfirst($schedules[$report->id]->frequency) }}</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400">{{ $report->description }} · Created {{ \Carbon\Carbon::parse($report->created_at)->diffForHumans() }} · Last run: {{ $report->last_run_at ? \Carbon\Carbon::parse($report->last_run_at)->diffForHumans() : 'Never' }}</p>
            </div>
            <div class="flex items-center gap-2 ml-4">
                <a href="{{ route('custom-reports.show', $report->id) }}" class="btn btn-sm btn-primary">Run</a>
                <form method="POST" action="{{ route('custom-reports.destroy', $report->id) }}" onsubmit="return confirm('Delete this report?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
