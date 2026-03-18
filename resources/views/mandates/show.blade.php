<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('mandates.index') }}" class="text-bankos-muted hover:text-bankos-primary transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </a>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        Mandate for {{ $mandate->account?->account_number }}
                    </h2>
                    @if($mandate->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-bankos-text-sec">
                    {{ $mandate->account?->account_name }}
                    @if($mandate->account?->customer)
                        &nbsp;&bull;&nbsp; {{ $mandate->account->customer->full_name }}
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ===================== LEFT COLUMN ===================== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Mandate Detail Card --}}
            <div class="card p-6">
                <div class="flex justify-between items-center mb-4 border-b border-bankos-border dark:border-bankos-dark-border pb-3">
                    <h3 class="font-bold text-base">Mandate Configuration</h3>
                </div>

                @php
                    $ruleLabels = [
                        'sole'        => ['label' => 'Sole Signatory', 'class' => 'badge-pending'],
                        'any_one'     => ['label' => 'Any One',        'class' => 'badge-success'],
                        'any_two'     => ['label' => 'Any Two',        'class' => 'badge-success'],
                        'a_and_b'     => ['label' => 'A and B',        'class' => 'badge-warning'],
                        'a_and_any_b' => ['label' => 'A and Any B',    'class' => 'badge-warning'],
                        'all'         => ['label' => 'All Signatories','class' => 'badge-danger'],
                    ];
                    $rule = $ruleLabels[$mandate->signing_rule] ?? ['label' => ucfirst(str_replace('_', ' ', $mandate->signing_rule)), 'class' => 'badge-pending'];
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider mb-1">Signing Rule</p>
                        <span class="badge {{ $rule['class'] }}">{{ $rule['label'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider mb-1">Sole Threshold</p>
                        <p class="font-semibold text-bankos-text dark:text-white text-sm">
                            {{ $mandate->max_amount_sole ? '₦' . number_format($mandate->max_amount_sole, 2) : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider mb-1">Effective Period</p>
                        <p class="text-sm text-bankos-text dark:text-white">
                            {{ $mandate->effective_from?->format('d M Y') ?? '—' }}
                            @if($mandate->effective_to)
                                &rarr; {{ $mandate->effective_to->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                </div>

                @if($mandate->description)
                <div class="mb-5 p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/40 text-sm text-bankos-text-sec border border-bankos-border dark:border-bankos-dark-border">
                    {{ $mandate->description }}
                </div>
                @endif

                {{-- Inline Edit Form --}}
                <details class="mt-2">
                    <summary class="cursor-pointer text-sm font-medium text-bankos-primary hover:underline select-none">
                        Edit Signing Rule / Details
                    </summary>
                    <form action="{{ route('mandates.update', $mandate) }}" method="POST"
                        class="mt-4 space-y-4 p-4 border border-bankos-border dark:border-bankos-dark-border rounded-lg bg-gray-50/50 dark:bg-bankos-dark-bg/30">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Signing Rule</label>
                                <select name="signing_rule" class="form-select w-full text-sm">
                                    <option value="sole"        @selected($mandate->signing_rule === 'sole')>Sole Signatory</option>
                                    <option value="any_one"     @selected($mandate->signing_rule === 'any_one')>Any One</option>
                                    <option value="any_two"     @selected($mandate->signing_rule === 'any_two')>Any Two</option>
                                    <option value="a_and_b"     @selected($mandate->signing_rule === 'a_and_b')>A and B</option>
                                    <option value="a_and_any_b" @selected($mandate->signing_rule === 'a_and_any_b')>A and Any B</option>
                                    <option value="all"         @selected($mandate->signing_rule === 'all')>All Signatories</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Sole Threshold (₦)</label>
                                <input type="number" name="max_amount_sole" value="{{ $mandate->max_amount_sole }}"
                                    step="0.01" min="0" class="form-input w-full text-sm" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Effective From</label>
                                <input type="date" name="effective_from" value="{{ $mandate->effective_from?->format('Y-m-d') }}" class="form-input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Effective To</label>
                                <input type="date" name="effective_to" value="{{ $mandate->effective_to?->format('Y-m-d') }}" class="form-input w-full text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-bankos-text-sec mb-1">Description</label>
                                <textarea name="description" rows="2" class="form-input w-full text-sm">{{ $mandate->description }}</textarea>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary text-sm">Save Changes</button>
                        </div>
                    </form>
                </details>
            </div>

            {{-- Signatories Table --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
                    <h3 class="font-bold text-base">Signatories</h3>
                    <span class="text-xs text-bankos-muted">
                        {{ $mandate->signatories->where('is_active', true)->count() }} active
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                                <th class="px-6 py-3 font-semibold">Name</th>
                                <th class="px-6 py-3 font-semibold">Class</th>
                                <th class="px-6 py-3 font-semibold">Phone</th>
                                <th class="px-6 py-3 font-semibold">Email</th>
                                <th class="px-6 py-3 font-semibold">Linked User</th>
                                <th class="px-6 py-3 font-semibold text-center">Active</th>
                                <th class="px-6 py-3 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                            @forelse($mandate->signatories as $sig)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $sig->is_active ? '' : 'opacity-50' }}">
                                <td class="px-6 py-3 font-medium">{{ $sig->signatory_name }}</td>
                                <td class="px-6 py-3">
                                    @php
                                        $classBadge = [
                                            'A' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                            'B' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                            'C' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $classBadge[$sig->signatory_class] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
                                        Class {{ $sig->signatory_class }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-bankos-text-sec text-xs">
                                    {{ $sig->phone ?: '—' }}
                                </td>
                                <td class="px-6 py-3 text-bankos-text-sec text-xs">
                                    {{ $sig->email ?: '—' }}
                                </td>
                                <td class="px-6 py-3 text-bankos-text-sec text-xs">
                                    {{ $sig->user?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if($sig->is_active)
                                        <span class="badge badge-success text-xs">Yes</span>
                                    @else
                                        <span class="badge badge-danger text-xs">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('mandates.signatories.destroy', $sig) }}" method="POST"
                                        onsubmit="return confirm('Remove {{ addslashes($sig->signatory_name) }} from this mandate?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-bankos-muted text-sm">No signatories on this mandate.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add Signatory Form --}}
                <div class="px-6 py-5 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/30">
                    <p class="text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-3">Add Signatory</p>
                    <form action="{{ route('mandates.signatories.store', $mandate) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            <div class="col-span-2 sm:col-span-1 lg:col-span-1">
                                <label class="block text-xs text-bankos-text-sec mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="signatory_name" placeholder="Full name" class="form-input w-full text-sm" required>
                                @error('signatory_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs text-bankos-text-sec mb-1">Class <span class="text-red-500">*</span></label>
                                <select name="signatory_class" class="form-select w-full text-sm">
                                    <option value="A">Class A</option>
                                    <option value="B">Class B</option>
                                    <option value="C">Class C</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-bankos-text-sec mb-1">Phone</label>
                                <input type="tel" name="phone" placeholder="Phone" class="form-input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-bankos-text-sec mb-1">Email</label>
                                <input type="email" name="email" placeholder="Email" class="form-input w-full text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-bankos-text-sec mb-1">Link Staff</label>
                                <select name="user_id" class="form-select w-full text-sm">
                                    <option value="">— None —</option>
                                    @foreach($staff as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-secondary text-sm">Add Signatory</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ===================== RIGHT COLUMN ===================== --}}
        <div class="space-y-4">
            <div class="card p-0 overflow-hidden">
                <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
                    <h3 class="font-bold text-base">Recent Approvals</h3>
                    <a href="{{ route('mandates.approvals') }}" class="text-xs text-bankos-primary hover:underline">View all pending</a>
                </div>

                @php
                    $statusMap = [
                        'pending'  => 'badge-pending',
                        'approved' => 'badge-success',
                        'rejected' => 'badge-danger',
                        'expired'  => 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                    ];
                @endphp

                @forelse($mandate->approvals->take(10) as $approval)
                <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border last:border-0">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-1.5">
                        <p class="text-xs text-bankos-muted">{{ $approval->created_at->format('d M Y, H:i') }}</p>
                        <span class="badge {{ $statusMap[$approval->status] ?? 'badge-pending' }} text-xs">
                            {{ ucfirst($approval->status) }}
                        </span>
                    </div>

                    {{-- Transaction --}}
                    <p class="text-sm font-medium text-bankos-text dark:text-white leading-snug">
                        {{ $approval->transaction_description }}
                    </p>
                    <p class="text-sm font-bold text-bankos-primary mt-0.5">₦{{ number_format($approval->amount, 2) }}</p>

                    {{-- Requested by --}}
                    <p class="text-xs text-bankos-muted mt-1">
                        Requested by {{ $approval->requested_by?->name ?? 'System' }}
                    </p>

                    {{-- Actions taken --}}
                    @if($approval->actions->count())
                    <div class="mt-2 space-y-1">
                        @foreach($approval->actions as $action)
                        <div class="flex items-center gap-1.5 text-xs">
                            @if($action->action === 'approve')
                                <svg class="w-3 h-3 text-bankos-success flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-bankos-success font-medium">Approved</span>
                            @else
                                <svg class="w-3 h-3 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-red-500 font-medium">Rejected</span>
                            @endif
                            <span class="text-bankos-muted">
                                by {{ $action->actioned_by?->name ?? 'Unknown' }}
                                @if($action->signatory)
                                    ({{ $action->signatory->signatory_name }})
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="px-5 py-8 text-center text-bankos-muted text-sm">
                    No approvals recorded for this mandate.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
