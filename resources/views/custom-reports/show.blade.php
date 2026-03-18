<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('custom-reports.index') }}" class="text-bankos-text-sec hover:text-bankos-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">{{ $report->name }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-0.5">{{ ucfirst(str_replace('_', ' ', $report->data_source)) }} · {{ $total }} records</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('custom-reports.export', $report->id) }}?format=csv" class="btn bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800 flex items-center gap-2 btn-sm">Export CSV</a>
                <a href="{{ route('custom-reports.export', $report->id) }}?format=pdf" class="btn btn-primary flex items-center gap-2 btn-sm">Export PDF</a>
            </div>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-bankos-dark-bg text-xs text-gray-500 uppercase">
                    <tr>
                        @foreach($columns as $col)
                        <th class="px-4 py-3 text-left">{{ ucfirst(str_replace('_', ' ', $col)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-bankos-dark-border">
                    @forelse($rows as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                        @foreach($columns as $col)
                        <td class="px-4 py-2.5 text-gray-700 dark:text-bankos-dark-text">{{ $row->$col ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr><td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($totalPages > 1)
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>Page {{ $page }} of {{ $totalPages }}</span>
            <div class="flex gap-2">
                @if($page > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn btn-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800">Previous</a>
                @endif
                @if($page < $totalPages)
                <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn btn-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800">Next</a>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
