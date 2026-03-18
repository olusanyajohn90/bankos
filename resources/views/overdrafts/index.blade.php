<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Overdraft Facilities
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage account overdraft limits, utilisation, and interest accrual</p>
            </div>
            @can('accounts.edit')
            <button
                onclick="document.getElementById('create-overdraft-modal').classList.remove('hidden')"
                class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Overdraft Facility
            </button>
            @endcan
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm font-medium dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- Facilities Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Account</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold text-right">Limit</th>
                        <th class="px-6 py-4 font-semibold text-right">Used</th>
                        <th class="px-6 py-4 font-semibold text-right">Available</th>
                        <th class="px-6 py-4 font-semibold text-right">Rate (p.a.)</th>
                        <th class="px-6 py-4 font-semibold">Expiry</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($facilities as $facility)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" id="row-{{ $facility->id }}">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary font-mono">{{ $facility->account?->account_number }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5 uppercase">{{ $facility->account?->account_name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($facility->account?->customer)
                            <a href="{{ route('customers.show', $facility->account->customer) }}"
                               class="font-medium text-bankos-text dark:text-white hover:text-bankos-primary">
                                {{ $facility->account->customer->first_name }} {{ $facility->account->customer->last_name }}
                            </a>
                            @else
                            <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-medium">
                            {{ number_format($facility->limit_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium {{ $facility->used_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-bankos-muted' }}">
                            {{ number_format($facility->used_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-bankos-success">
                            {{ number_format($facility->available, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{ number_format($facility->interest_rate, 3) }}%
                        </td>
                        <td class="px-6 py-4">
                            @if($facility->expiry_date)
                                <span class="{{ $facility->is_expired ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-bankos-text dark:text-white' }}">
                                    {{ $facility->expiry_date->format('d M Y') }}
                                </span>
                                @if($facility->is_expired)
                                    <p class="text-xs text-red-500 mt-0.5">Expired</p>
                                @endif
                            @else
                                <span class="text-bankos-muted">No expiry</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($facility->status === 'active')
                                <span class="badge badge-active flex items-center w-max gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full bg-bankos-success"></div> Active
                                </span>
                            @elseif($facility->status === 'suspended')
                                <span class="badge badge-pending">Suspended</span>
                            @elseif($facility->status === 'expired')
                                <span class="badge badge-danger">Expired</span>
                            @else
                                <span class="badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Closed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('accounts.edit')
                            <button
                                onclick="openEditModal('{{ $facility->id }}', '{{ $facility->limit_amount }}', '{{ $facility->interest_rate }}', '{{ $facility->expiry_date?->format('Y-m-d') }}', '{{ $facility->status }}')"
                                class="text-bankos-primary hover:text-blue-700 font-medium text-sm">
                                Edit
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-bankos-muted">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-gray-300 dark:text-gray-600"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                <p class="text-lg font-medium text-bankos-text dark:text-white">No overdraft facilities found</p>
                                <p class="text-sm mt-1">Create a new facility to get started.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($facilities->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $facilities->links() }}
        </div>
        @endif
    </div>

    {{-- CREATE MODAL --}}
    <div id="create-overdraft-modal"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-bankos-dark-bg rounded-xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-bold text-lg text-bankos-text dark:text-bankos-dark-text">New Overdraft Facility</h3>
                <button onclick="document.getElementById('create-overdraft-modal').classList.add('hidden')"
                        class="text-bankos-muted hover:text-bankos-text dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form action="{{ route('overdrafts.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Account ID <span class="text-red-500">*</span></label>
                    <input type="text" name="account_id" placeholder="Paste account UUID"
                           class="form-input w-full font-mono text-sm" required>
                    <p class="text-xs text-bankos-muted mt-1">Find the account UUID from the Accounts Directory.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Limit Amount <span class="text-red-500">*</span></label>
                        <input type="number" name="limit_amount" min="1" step="0.01" class="form-input w-full" required placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Interest Rate (% p.a.) <span class="text-red-500">*</span></label>
                        <input type="number" name="interest_rate" min="0" max="100" step="0.001" class="form-input w-full" required placeholder="e.g. 24.000">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Approved Date <span class="text-red-500">*</span></label>
                        <input type="date" name="approved_date" class="form-input w-full" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-input w-full">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Notes</label>
                    <textarea name="notes" rows="2" maxlength="500"
                              class="form-input w-full resize-none" placeholder="Optional approval notes..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                            onclick="document.getElementById('create-overdraft-modal').classList.add('hidden')"
                            class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Facility</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="edit-overdraft-modal"
         class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-bankos-dark-bg rounded-xl shadow-2xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-bold text-lg text-bankos-text dark:text-bankos-dark-text">Edit Overdraft Facility</h3>
                <button onclick="document.getElementById('edit-overdraft-modal').classList.add('hidden')"
                        class="text-bankos-muted hover:text-bankos-text dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="edit-overdraft-form" action="" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Limit Amount <span class="text-red-500">*</span></label>
                        <input type="number" id="edit-limit" name="limit_amount" min="0" step="0.01" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Interest Rate (% p.a.) <span class="text-red-500">*</span></label>
                        <input type="number" id="edit-rate" name="interest_rate" min="0" max="100" step="0.001" class="form-input w-full" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Expiry Date</label>
                        <input type="date" id="edit-expiry" name="expiry_date" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Status <span class="text-red-500">*</span></label>
                        <select id="edit-status" name="status" class="form-input w-full">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                            onclick="document.getElementById('edit-overdraft-modal').classList.add('hidden')"
                            class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, limit, rate, expiry, status) {
            document.getElementById('edit-overdraft-form').action = '/overdrafts/' + id;
            document.getElementById('edit-limit').value = limit;
            document.getElementById('edit-rate').value = rate;
            document.getElementById('edit-expiry').value = expiry || '';
            document.getElementById('edit-status').value = status;
            document.getElementById('edit-overdraft-modal').classList.remove('hidden');
        }
        // Close modals on backdrop click
        ['create-overdraft-modal','edit-overdraft-modal'].forEach(function(id) {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
            });
        });
    </script>
</x-app-layout>
