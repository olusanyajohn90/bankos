<x-app-layout>
    <x-slot name="header">Predictive Compliance Alerts</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Alerts --}}
        <div class="space-y-4">
            @forelse($alerts as $alert)
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border {{ $alert->severity === 'critical' ? 'border-red-300' : ($alert->severity === 'warning' ? 'border-yellow-300' : 'border-bankos-border') }} dark:border-bankos-dark-border p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            @php $svc = match($alert->severity) { 'critical' => 'bg-red-100 text-red-700', 'warning' => 'bg-yellow-100 text-yellow-700', default => 'bg-blue-100 text-blue-700' }; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $svc }}">{{ strtoupper($alert->severity) }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</span>
                            @php $stc = match($alert->status) { 'active' => 'bg-red-100 text-red-700', 'acknowledged' => 'bg-blue-100 text-blue-700', 'resolved' => 'bg-green-100 text-green-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ ucfirst($alert->status) }}</span>
                        </div>
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $alert->title }}</h3>
                        <p class="text-sm text-bankos-muted mt-1">{{ $alert->description }}</p>

                        @if($alert->prediction_data)
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                            @if(isset($alert->prediction_data['current_value']))
                            <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-lg p-2">
                                <p class="text-xs text-bankos-muted">Current Value</p>
                                <p class="font-mono font-bold text-sm">{{ is_numeric($alert->prediction_data['current_value']) ? number_format($alert->prediction_data['current_value'], 2) : $alert->prediction_data['current_value'] }}</p>
                            </div>
                            @endif
                            @if(isset($alert->prediction_data['predicted_date']))
                            <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-lg p-2">
                                <p class="text-xs text-bankos-muted">Predicted Date</p>
                                <p class="font-mono font-bold text-sm">{{ $alert->prediction_data['predicted_date'] }}</p>
                            </div>
                            @endif
                            @if(isset($alert->prediction_data['confidence']))
                            <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-lg p-2">
                                <p class="text-xs text-bankos-muted">Confidence</p>
                                <p class="font-mono font-bold text-sm">{{ $alert->prediction_data['confidence'] }}%</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($alert->recommended_action)
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs font-semibold text-blue-800 dark:text-blue-300">Recommended Action</p>
                            <p class="text-sm text-blue-700 dark:text-blue-400">{{ $alert->recommended_action }}</p>
                        </div>
                        @endif
                    </div>

                    @if($alert->status === 'active')
                    <form method="POST" action="{{ route('compliance-auto.predictive-alerts.acknowledge', $alert->id) }}" class="ml-4">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs hover:bg-blue-200">Acknowledge</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-8 text-center">
                <p class="text-bankos-muted">No predictive alerts at this time. The system is monitoring compliance metrics.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
