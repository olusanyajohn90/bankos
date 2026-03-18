<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">IP Whitelist</h2>
            <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-0.5">Restrict admin access to specific IP addresses only.</p>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- ── Status Banner ────────────────────────────────────────────────── --}}
        @if($activeCount > 0)
            <div class="flex items-start gap-4 px-5 py-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">IP Restrictions are: ON ({{ $activeCount }} active {{ Str::plural('entry', $activeCount) }})</p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                        Access is restricted to the {{ $activeCount }} listed IP {{ Str::plural('address', $activeCount) }}.
                        Any login attempt from an unlisted IP will be blocked.
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-start gap-4 px-5 py-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-800 flex items-center justify-center mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">IP Restrictions are: OFF (no active entries)</p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">
                        All IP addresses can currently access this system. Add at least one active IP address to enable restrictions.
                        <strong>Warning:</strong> Enabling IP restrictions will block access from any IP not on this list — including your own if not added.
                    </p>
                </div>
            </div>
        @endif

        {{-- ── Current IP + Add Form ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Add Form --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-2xl border border-bankos-border dark:border-bankos-dark-border shadow-sm p-6">
                <h3 class="text-base font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Add IP Address</h3>
                <p class="text-xs text-bankos-text-sec dark:text-bankos-dark-text-sec mb-4">Supports IPv4 and IPv6.</p>

                {{-- Current IP quick-add --}}
                <div class="mb-5 p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Your current IP address:</p>
                    <div class="flex items-center justify-between gap-2">
                        <code class="font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $currentIp }}</code>
                        <form method="POST" action="{{ route('ip-whitelist.store') }}">
                            @csrf
                            <input type="hidden" name="ip_address" value="{{ $currentIp }}">
                            <input type="hidden" name="label" value="My IP ({{ now()->format('M d, Y') }})">
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Mine
                            </button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="{{ route('ip-whitelist.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address *</label>
                        <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="e.g. 192.168.1.100 or 2001:db8::1"
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono" required>
                        @error('ip_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Label *</label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Head Office, Lagos Branch"
                            class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add to Whitelist
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-2xl border border-bankos-border dark:border-bankos-dark-border shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                    <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">Allowed IPs ({{ $entries->count() }})</p>
                </div>

                @if($entries->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No IPs added yet. IP restrictions are currently OFF.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">IP Address</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Label</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Added By</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                                @foreach($entries as $entry)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors {{ $entry->ip_address === $currentIp ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <code class="font-mono text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $entry->ip_address }}</code>
                                            @if($entry->ip_address === $currentIp)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">You</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-bankos-text-sec">{{ $entry->label }}</td>
                                    <td class="px-4 py-3 text-xs text-bankos-text-sec">{{ $entry->created_by_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-bankos-text-sec whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->created_at)->format('M d, Y') }}</td>
                                    <td class="px-4 py-3">
                                        @if($entry->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('ip-whitelist.toggle', $entry->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                    {{ $entry->is_active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <span class="text-gray-300 dark:text-gray-600">|</span>
                                            <form method="POST" action="{{ route('ip-whitelist.destroy', $entry->id) }}" onsubmit="return confirm('Remove IP {{ $entry->ip_address }} from whitelist?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline">Remove</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
