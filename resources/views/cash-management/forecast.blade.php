<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Cash Flow Forecast</h2>
                <p class="text-sm text-bankos-text-sec mt-1">7-day projection based on 30-day historical averages</p>
            </div>
            <a href="{{ route('cash-management.dashboard') }}" class="btn btn-outline text-sm">Back to Dashboard</a>
        </div>
    </x-slot>

    <div class="grid grid-cols-3 gap-5 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Starting Balance</p>
            <h3 class="text-2xl font-bold mt-2 text-bankos-text dark:text-white">₦{{ number_format($startingBalance, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Avg Daily Inflows</p>
            <h3 class="text-2xl font-bold mt-2 text-green-600">₦{{ number_format($avgInflows, 0) }}</h3>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Avg Daily Outflows</p>
            <h3 class="text-2xl font-bold mt-2 text-red-600">₦{{ number_format($avgOutflows, 0) }}</h3>
        </div>
    </div>

    <div class="card overflow-hidden mb-8">
        <table class="bankos-table w-full">
            <thead>
                <tr><th>Date</th><th>Day</th><th>Est. Inflows</th><th>Est. Outflows</th><th>Projected Balance</th></tr>
            </thead>
            <tbody>
            @foreach($forecast as $f)
                <tr>
                    <td class="font-medium">{{ $f['date'] }}</td>
                    <td>{{ $f['day'] }}</td>
                    <td class="text-green-600">₦{{ number_format($f['inflows'], 0) }}</td>
                    <td class="text-red-600">₦{{ number_format($f['outflows'], 0) }}</td>
                    <td class="font-bold {{ $f['balance'] < 0 ? 'text-red-600' : '' }}">₦{{ number_format($f['balance'], 0) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card p-6">
        <h3 class="text-lg font-semibold text-bankos-text dark:text-white mb-4">Forecast Trend</h3>
        <canvas id="forecastChart" height="200"></canvas>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const data = @json($forecast);
        new Chart(document.getElementById('forecastChart'), {
            type: 'line',
            data: {
                labels: data.map(d => d.day),
                datasets: [{
                    label: 'Projected Balance',
                    data: data.map(d => d.balance),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: { responsive: true }
        });
    });
    </script>
</x-app-layout>
