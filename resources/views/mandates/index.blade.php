<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Account Mandates
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage corporate signing mandates for accounts</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('mandates.approvals') }}" class="btn btn-secondary flex items-center gap-2">
                    Pending Approvals
                    @if(isset($pendingCount) && $pendingCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-white text-xs font-bold leading-none">
                            {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('mandates.create') }}" class="btn btn-primary">
                    New Mandate
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <form action="{{ route('mandates.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Signing Rule</label>
                    <select name="signing_rule" class="form-select text-sm" onchange="this.form.submit()">
                        <option value="">All Rules</option>
                        <option value="sole"        @selected(request('signing_rule') === 'sole')>Sole Signatory</option>
                        <option value="any_one"     @selected(request('signing_rule') === 'any_one')>Any One</option>
                        <option value="any_two"     @selected(request('signing_rule') === 'any_two')>Any Two</option>
                        <option value="a_and_b"     @selected(request('signing_rule') === 'a_and_b')>A and B</option>
                        <option value="a_and_any_b" @selected(request('signing_rule') === 'a_and_any_b')>A and Any B</option>
                        <option value="all"         @selected(request('signing_rule') === 'all')>All Signatories</option>
                    </select>
                </div>
                @if(request('signing_rule'))
                <a href="{{ route('mandates.index') }}" class="text-sm text-bankos-muted hover:text-bankos-primary self-end pb-1">Clear filter</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Account</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Signing Rule</th>
                        <th class="px-6 py-4 font-semibold text-center">Signatories</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Effective Dates</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($mandates as $mandate)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary font-mono">{{ $mandate->account?->account_number }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $mandate->account?->account_name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($mandate->account?->customer)
                                <span class="font-medium text-bankos-text dark:text-white">
                                    {{ $mandate->account->customer->full_name }}
                                </span>
                            @else
                                <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $ruleLabels = [
                                    'sole'        => ['label' => 'Sole Signatory', 'class' => 'badge-pending'],
                                    'any_one'     => ['label' => 'Any One',        'class' => 'badge-active'],
                                    'any_two'     => ['label' => 'Any Two',        'class' => 'badge-active'],
                                    'a_and_b'     => ['label' => 'A and B',        'class' => 'badge-warning'],
                                    'a_and_any_b' => ['label' => 'A and Any B',    'class' => 'badge-warning'],
                                    'all'         => ['label' => 'All',            'class' => 'badge-danger'],
                                ];
                                $rule = $ruleLabels[$mandate->signing_rule] ?? ['label' => ucfirst(str_replace('_', ' ', $mandate->signing_rule)), 'class' => 'badge-pending'];
                            @endphp
                            <span class="badge {{ $rule['class'] }}">{{ $rule['label'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-bankos-primary/10 text-bankos-primary font-bold text-xs">
                                {{ $mandate->signatories->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($mandate->is_active)
                                <span class="badge badge-success flex items-center w-max gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full bg-current"></div> Active
                                </span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-bankos-text-sec">
                            @if($mandate->effective_from || $mandate->effective_to)
                                <p>From: {{ $mandate->effective_from ? $mandate->effective_from->format('d M Y') : '—' }}</p>
                                <p>To: {{ $mandate->effective_to ? $mandate->effective_to->format('d M Y') : 'Open-ended' }}</p>
                            @else
                                <span class="text-bankos-muted">No dates set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('mandates.show', $mandate) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-bankos-muted">
                            <svg class="w-10 h-10 mx-auto mb-3 text-bankos-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mb-4">No mandates found.</p>
                            <a href="{{ route('mandates.create') }}" class="btn btn-secondary text-sm">Set Up First Mandate</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mandates->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $mandates->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
