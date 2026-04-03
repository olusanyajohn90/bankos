<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Staff Dashboard</h1>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-1">Workforce analytics, demographics & headcount breakdown</p>
            </div>
        </div>
    </x-slot>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Employees</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ number_format($totalEmployees) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Active headcount</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">New Hires (Month)</p>
            <p class="text-2xl font-extrabold text-green-600 mt-1">{{ number_format($newHiresMonth) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $newHiresQuarter }} this quarter</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Attrition Rate</p>
            <p class="text-2xl font-extrabold {{ $attritionRate > 15 ? 'text-red-600' : ($attritionRate > 10 ? 'text-amber-600' : 'text-green-600') }} mt-1">{{ $attritionRate }}%</p>
            <p class="text-xs text-bankos-text-sec mt-2">{{ $terminatedThisYear }} departed this year</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Departments</p>
            <p class="text-2xl font-extrabold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $byDepartment->count() }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Active departments</p>
        </div>
    </div>

    {{-- Employment Type Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach($byEmploymentType as $type => $count)
        <div class="card p-4 text-center">
            <p class="text-xl font-bold text-bankos-primary">{{ number_format($count) }}</p>
            <p class="text-xs text-bankos-text-sec mt-0.5 capitalize">{{ str_replace('_', ' ', $type ?: 'Unspecified') }}</p>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Staff by Department</h3>
            <canvas id="deptChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Gender Distribution</h3>
            <canvas id="genderChart" height="250"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Staff by Branch</h3>
            <canvas id="branchChart" height="250"></canvas>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Tenure Distribution</h3>
            <canvas id="tenureChart" height="250"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('deptChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byDepartment->pluck('dept')) !!},
                datasets: [{
                    label: 'Staff',
                    data: {!! json_encode($byDepartment->pluck('total')) !!},
                    backgroundColor: '#3b82f6'
                }]
            },
            options: { responsive: true, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($byGender->keys()->map(fn($g) => ucfirst($g ?: 'Not Set'))) !!},
                datasets: [{
                    data: {!! json_encode($byGender->values()) !!},
                    backgroundColor: ['#3b82f6', '#ec4899', '#94a3b8', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('branchChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($byBranch->pluck('branch')) !!},
                datasets: [{
                    label: 'Staff',
                    data: {!! json_encode($byBranch->pluck('total')) !!},
                    backgroundColor: '#22c55e'
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('tenureChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($tenureDistribution->keys()) !!},
                datasets: [{
                    data: {!! json_encode($tenureDistribution->values()) !!},
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endpush
</x-app-layout>
