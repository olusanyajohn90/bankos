<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">API Documentation</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Available Open Banking API endpoints</p>
            </div>
            <a href="{{ route('open-banking.dashboard') }}" class="btn btn-outline text-sm">Dashboard</a>
        </div>
    </x-slot>

    <div class="card p-6 mb-6">
        <h3 class="text-lg font-semibold mb-2">Base URL</h3>
        <code class="text-sm bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded block">{{ url('/api/v1') }}</code>
        <p class="text-sm text-bankos-muted mt-3">All requests require <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Authorization: Bearer {token}</code> header and <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">X-Tenant-ID</code> header.</p>
    </div>

    <div class="space-y-3">
        @foreach($endpoints as $ep)
        <div class="card p-4 flex items-center gap-4">
            <span class="inline-block px-2 py-1 rounded text-xs font-bold uppercase
                {{ $ep['method'] == 'GET' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' :
                   ($ep['method'] == 'POST' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-gray-100 text-gray-700') }}">
                {{ $ep['method'] }}
            </span>
            <code class="text-sm font-mono flex-1">{{ $ep['path'] }}</code>
            <span class="text-sm text-bankos-text-sec">{{ $ep['description'] }}</span>
            @if(isset($ep['scope']))
            <span class="badge badge-purple text-xs">{{ $ep['scope'] }}</span>
            @endif
        </div>
        @endforeach
    </div>
</x-app-layout>
