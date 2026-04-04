<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Regulatory Reporting Dashboard</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Report calendar, submissions and compliance status</p>
            </div>
            <a href="{{ route('regulatory.create') }}" class="btn btn-primary text-sm">New Report</a>
        </div>
    </x-slot>

    @if(isset($error))<div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800"><p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p></div>@endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Total Reports</p><h3 class="text-2xl font-bold mt-2">{{ $totalReports }}</h3></div>
        <div class="card p-5 border-l-4 border-l-amber-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Pending</p><h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $pendingReports }}</h3></div>
        <div class="card p-5 border-l-4 border-l-indigo-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Draft</p><h3 class="text-2xl font-bold mt-2">{{ $draftReports }}</h3></div>
        <div class="card p-5 border-l-4 border-l-green-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Submitted</p><h3 class="text-2xl font-bold mt-2 text-green-600">{{ $submittedReports }}</h3></div>
        <div class="card p-5 border-l-4 border-l-red-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Overdue</p><h3 class="text-2xl font-bold mt-2 {{ $overdueReports > 0 ? 'text-red-600' : '' }}">{{ $overdueReports }}</h3></div>
        <div class="card p-5 border-l-4 border-l-purple-500"><p class="text-xs font-medium text-bankos-text-sec uppercase">Accepted</p><h3 class="text-2xl font-bold mt-2 text-purple-600">{{ $acceptedReports }}</h3></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">By Report Type</h3>
            <canvas id="typeChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Status Distribution</h3>
            <canvas id="statusChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Submission Trend</h3>
            <canvas id="trendChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Upcoming Deadlines</h3>
            @if($upcomingDeadlines->count())
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($upcomingDeadlines as $d)
                <div class="flex justify-between items-center p-2 rounded-lg {{ $d->due_date->isPast() ? 'bg-red-50 dark:bg-red-900/20' : ($d->due_date->diffInDays(now()) <= 7 ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-gray-50 dark:bg-bankos-dark-bg') }}">
                    <div>
                        <p class="text-sm font-medium">{{ $d->report_name }}</p>
                        <p class="text-xs text-bankos-muted">{{ strtoupper(str_replace('_',' ',$d->report_type)) }} - {{ $d->period }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $d->due_date->isPast() ? 'text-red-600' : '' }}">{{ $d->due_date->format('d M') }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-bankos-muted text-sm">No upcoming deadlines.</p> @endif
        </div>
    </div>

    {{-- Due this month --}}
    @if($dueThisMonth->count())
    <div class="card overflow-hidden">
        <h3 class="p-4 text-lg font-semibold border-b border-bankos-border dark:border-bankos-dark-border">Due This Month</h3>
        <table class="bankos-table w-full text-sm">
            <thead><tr><th>Report</th><th>Type</th><th>Period</th><th>Due Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($dueThisMonth as $r)
                <tr>
                    <td class="font-medium">{{ $r->report_name }}</td>
                    <td>{{ strtoupper(str_replace('_',' ',$r->report_type)) }}</td>
                    <td>{{ $r->period }}</td>
                    <td class="{{ $r->due_date->isPast() ? 'text-red-600 font-bold' : '' }}">{{ $r->due_date->format('d M Y') }}</td>
                    <td><span class="badge {{ $r->status=='pending' ? 'badge-amber' : 'badge-blue' }}">{{ ucfirst($r->status) }}</span></td>
                    <td><a href="{{ route('regulatory.show', $r->id) }}" class="text-bankos-primary hover:underline">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('typeChart'), { type: 'bar', data: { labels: @json($byType->pluck('report_type')->map(fn($t) => strtoupper(str_replace('_',' ',$t)))), datasets: [{ label: 'Count', data: @json($byType->pluck('count')), backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'], borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
        new Chart(document.getElementById('statusChart'), { type: 'doughnut', data: { labels: @json($byStatus->pluck('status')->map(fn($s) => ucfirst($s))), datasets: [{ data: @json($byStatus->pluck('count')), backgroundColor: ['#f59e0b','#3b82f6','#10b981','#22c55e','#ef4444'] }] }, options: { responsive: true } });
        new Chart(document.getElementById('trendChart'), { type: 'line', data: { labels: @json($monthlySubmissions->pluck('month')), datasets: [{ label: 'Submitted', data: @json($monthlySubmissions->pluck('count')), borderColor: '#10b981', tension: 0.3, fill: false }] }, options: { responsive: true, scales: { y: { beginAtZero: true } } } });
    });
    </script>
</x-app-layout>
