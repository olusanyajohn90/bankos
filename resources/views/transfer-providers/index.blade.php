<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Transfer Providers</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage interbank transfer service providers</p>
            </div>
            <a href="{{ route('transfer-providers.create') }}" class="btn btn-primary shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="mr-1.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Provider
            </a>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border
                                text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Provider</th>
                        <th class="px-6 py-4 font-semibold">Code</th>
                        <th class="px-6 py-4 font-semibold">Driver Class</th>
                        <th class="px-6 py-4 font-semibold text-center">Priority</th>
                        <th class="px-6 py-4 font-semibold text-right">Fee</th>
                        <th class="px-6 py-4 font-semibold text-right">Amount Limits</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($providers as $provider)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        {{-- Provider Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-bankos-text">{{ $provider->name }}</p>
                                @if($provider->is_default)
                                    <span class="badge badge-active text-[10px] uppercase tracking-wider">Default</span>
                                @endif
                            </div>
                        </td>

                        {{-- Code --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs text-bankos-primary bg-blue-50 dark:bg-blue-900/20 px-2 py-1 rounded">{{ $provider->code }}</span>
                        </td>

                        {{-- Driver Class --}}
                        <td class="px-6 py-4 text-xs text-bankos-text-sec font-mono">
                            {{ class_basename($provider->provider_class) }}
                        </td>

                        {{-- Priority --}}
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-sm font-bold text-bankos-text">
                                {{ $provider->priority }}
                            </span>
                        </td>

                        {{-- Fee --}}
                        <td class="px-6 py-4 text-right text-xs text-bankos-text-sec">
                            @if((float)$provider->flat_fee > 0 || (float)$provider->percentage_fee > 0)
                                @if((float)$provider->flat_fee > 0)
                                    <span class="block">Flat: {{ number_format($provider->flat_fee, 2) }}</span>
                                @endif
                                @if((float)$provider->percentage_fee > 0)
                                    <span class="block">Rate: {{ number_format($provider->percentage_fee * 100, 2) }}%</span>
                                @endif
                                @if($provider->fee_cap !== null)
                                    <span class="block text-bankos-muted">Cap: {{ number_format($provider->fee_cap, 2) }}</span>
                                @endif
                            @else
                                <span class="text-bankos-muted">No fee</span>
                            @endif
                        </td>

                        {{-- Amount Limits --}}
                        <td class="px-6 py-4 text-right text-xs text-bankos-text-sec">
                            <span class="block">Min: {{ number_format($provider->min_amount, 2) }}</span>
                            @if($provider->max_amount !== null)
                                <span class="block">Max: {{ number_format($provider->max_amount, 2) }}</span>
                            @else
                                <span class="block text-bankos-muted">Max: Unlimited</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($provider->is_active)
                                <span class="badge badge-active text-[10px] uppercase tracking-wider">Active</span>
                            @else
                                <span class="badge badge-danger text-[10px] uppercase tracking-wider">Inactive</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('transfer-providers.edit', $provider) }}"
                                   class="text-bankos-primary hover:text-blue-700 font-medium text-sm">
                                    Edit
                                </a>

                                {{-- Toggle Active --}}
                                <form method="POST" action="{{ route('transfer-providers.toggle', $provider) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-sm font-medium {{ $provider->is_active ? 'text-orange-600 hover:text-orange-700' : 'text-green-600 hover:text-green-700' }}">
                                        {{ $provider->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                {{-- Set Default --}}
                                @if(!$provider->is_default)
                                <form method="POST" action="{{ route('transfer-providers.default', $provider) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="text-sm font-medium text-bankos-text-sec hover:text-bankos-primary">
                                        Set Default
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="inline-flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-bankos-border" xmlns="http://www.w3.org/2000/svg"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <p class="font-medium text-bankos-text">No transfer providers configured.</p>
                                <p class="text-bankos-text-sec text-sm">
                                    <a href="{{ route('transfer-providers.create') }}" class="text-bankos-primary hover:underline">Add a provider</a> to get started.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
