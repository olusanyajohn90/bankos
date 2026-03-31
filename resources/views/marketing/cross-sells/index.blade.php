<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Cross-sell Opportunities</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Identify and track product cross-sell opportunities</p>
            </div>
            <form action="{{ route('marketing.cross-sells.generate') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    Generate Opportunities
                </button>
            </form>
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">Opportunity Type</label>
                <select name="type" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Types</option>
                    <option value="savings_to_loan" {{ request('type') === 'savings_to_loan' ? 'selected' : '' }}>Savings to Loan</option>
                    <option value="loan_to_insurance" {{ request('type') === 'loan_to_insurance' ? 'selected' : '' }}>Loan to Insurance</option>
                    <option value="loan_to_savings" {{ request('type') === 'loan_to_savings' ? 'selected' : '' }}>Loan to Savings</option>
                    <option value="dormant_reactivation" {{ request('type') === 'dormant_reactivation' ? 'selected' : '' }}>Dormant Reactivation</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-bankos-muted mb-1">Status</label>
                <select name="status" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All</option>
                    @foreach(['identified','contacted','interested','converted','declined'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-bankos-primary text-white text-sm rounded-lg hover:bg-bankos-primary/90">Filter</button>
            <a href="{{ route('marketing.cross-sells') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Opportunity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Est. Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Assigned To</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($crossSells as $cs)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50" x-data="{ editing: false }">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $cs->customer?->full_name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">
                                {{ str_replace('_', ' ', ucfirst($cs->opportunity_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec">{{ $cs->recommended_product }}</td>
                        <td class="px-6 py-3 text-right font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $cs->estimated_value ? number_format($cs->estimated_value, 2) : '-' }}
                        </td>
                        <td class="px-6 py-3">
                            @php
                            $csStatusClasses = match($cs->status) {
                                'identified' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'contacted'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'interested' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                'converted'  => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'declined'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                default      => 'bg-gray-100 text-gray-700',
                            };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $csStatusClasses }}">{{ ucfirst($cs->status) }}</span>
                        </td>
                        <td class="px-6 py-3 text-bankos-muted">{{ $cs->assignedTo?->name ?? 'Unassigned' }}</td>
                        <td class="px-6 py-3 text-right">
                            <div x-show="!editing">
                                <button @click="editing = true" class="text-bankos-primary hover:text-bankos-primary/80 text-xs font-medium">Update</button>
                            </div>
                            <div x-show="editing" x-transition>
                                <form action="{{ route('marketing.cross-sells.update', $cs->id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-xs px-2 py-1">
                                        @foreach(['identified','contacted','interested','converted','declined'] as $s)
                                        <option value="{{ $s }}" {{ $cs->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="assigned_to" class="rounded border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-xs px-2 py-1">
                                        <option value="">Unassigned</option>
                                        @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ $cs->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Save</button>
                                    <button type="button" @click="editing = false" class="text-bankos-muted text-xs">Cancel</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-2">No cross-sell opportunities found.</p>
                            <p class="text-sm">Click "Generate Opportunities" to auto-detect potential cross-sells.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($crossSells->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $crossSells->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
