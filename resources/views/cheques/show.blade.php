<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('cheques.index') }}"
                   class="text-bankos-muted hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        Cheque Book — {{ $chequeBook->series_start }} to {{ $chequeBook->series_end }}
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-1">
                        {{ $chequeBook->account?->account_number }}
                        @if($chequeBook->account?->customer)
                            &bull; {{ $chequeBook->account->customer->first_name }} {{ $chequeBook->account->customer->last_name }}
                        @endif
                    </p>
                </div>
            </div>
            @if($chequeBook->status === 'active')
            <button @click="$dispatch('open-modal', 'issue-leaf')"
                    class="btn btn-primary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Issue Leaf
            </button>
            @endif
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Book Summary --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="card p-6">
                <h3 class="font-bold text-base mb-4">Book Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Status</dt>
                        <dd>
                            @if($chequeBook->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif($chequeBook->status === 'exhausted')
                                <span class="badge badge-dormant">Exhausted</span>
                            @else
                                <span class="badge badge-danger">Cancelled</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Series</dt>
                        <dd class="font-mono font-semibold">{{ $chequeBook->series_start }} — {{ $chequeBook->series_end }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Total Leaves</dt>
                        <dd class="font-semibold">{{ $chequeBook->leaves }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Leaves Used</dt>
                        <dd class="font-semibold text-amber-600 dark:text-amber-400">{{ $chequeBook->leaves_used }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Remaining</dt>
                        <dd class="font-bold text-bankos-success">{{ $chequeBook->leaves_remaining }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Issued Date</dt>
                        <dd>{{ $chequeBook->issued_date->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Issued By</dt>
                        <dd>{{ $chequeBook->issuedBy?->name ?? '—' }}</dd>
                    </div>
                </dl>

                {{-- Leaves progress bar --}}
                <div class="mt-4 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    @php $usedPct = $chequeBook->leaves > 0 ? round($chequeBook->leaves_used / $chequeBook->leaves * 100) : 0; @endphp
                    <div class="flex justify-between text-xs text-bankos-muted mb-1.5">
                        <span>Usage</span>
                        <span>{{ $usedPct }}%</span>
                    </div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $usedPct >= 90 ? 'bg-red-500' : ($usedPct >= 70 ? 'bg-amber-500' : 'bg-bankos-primary') }}"
                             style="width: {{ $usedPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cheque Leaves Table --}}
        <div class="lg:col-span-3 card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex justify-between items-center">
                <h3 class="font-semibold text-bankos-text dark:text-white">Cheque Leaves</h3>
                <span class="text-xs text-bankos-muted">{{ $chequeBook->cheques->count() }} recorded</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-5 py-3 font-semibold">Cheque No.</th>
                            <th class="px-5 py-3 font-semibold">Payee</th>
                            <th class="px-5 py-3 font-semibold text-right">Amount</th>
                            <th class="px-5 py-3 font-semibold">Issue Date</th>
                            <th class="px-5 py-3 font-semibold">Presented</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($chequeBook->cheques as $leaf)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            x-data="{ open: false }">
                            <td class="px-5 py-3">
                                <p class="font-mono font-bold text-bankos-primary">{{ $leaf->cheque_number }}</p>
                                @if($leaf->drawer_reference)
                                    <p class="text-xs text-bankos-muted mt-0.5">Ref: {{ $leaf->drawer_reference }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-bankos-text dark:text-gray-300">{{ $leaf->payee_name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($leaf->amount)
                                    <p class="font-bold">₦{{ number_format($leaf->amount, 2) }}</p>
                                @else
                                    <p class="text-bankos-muted">—</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-bankos-muted">
                                {{ $leaf->issue_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-xs text-bankos-muted">
                                {{ $leaf->presented_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @if($leaf->status === 'issued')
                                    <span class="badge badge-dormant">Issued</span>
                                @elseif($leaf->status === 'presented')
                                    <span class="badge badge-pending">Presented</span>
                                @elseif($leaf->status === 'cleared')
                                    <span class="badge badge-active">Cleared</span>
                                @elseif($leaf->status === 'bounced')
                                    <span class="badge badge-danger">Bounced</span>
                                @elseif($leaf->status === 'cancelled')
                                    <span class="badge bg-gray-100 text-gray-500 border border-gray-200">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if(in_array($leaf->status, ['issued', 'presented']))
                                <button @click="open = !open"
                                        class="text-bankos-primary text-xs font-medium border border-bankos-border dark:border-bankos-dark-border px-2.5 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                    Update
                                </button>
                                @else
                                    <span class="text-bankos-muted text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        {{-- Inline status update form --}}
                        @if(in_array($leaf->status, ['issued', 'presented']))
                        <tr x-show="open" x-cloak class="bg-blue-50/50 dark:bg-blue-900/10">
                            <td colspan="7" class="px-5 py-4">
                                <form action="{{ route('cheques.updateLeaf', $chequeBook) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="cheque_transaction_id" value="{{ $leaf->id }}">
                                    <div class="flex flex-wrap gap-3 items-end">
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">New Status</label>
                                            <select name="status" class="form-select text-sm py-2">
                                                @if($leaf->status === 'issued')
                                                    <option value="presented">Presented</option>
                                                @endif
                                                <option value="cleared">Cleared</option>
                                                <option value="bounced">Bounced</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Bank Reference</label>
                                            <input type="text" name="bank_reference" value="{{ $leaf->bank_reference }}"
                                                   class="form-input text-sm py-2 w-40" placeholder="Bank ref...">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Notes</label>
                                            <input type="text" name="notes" value="{{ $leaf->notes }}"
                                                   class="form-input text-sm py-2 w-48" placeholder="Optional notes...">
                                        </div>
                                        <button type="submit" class="btn btn-primary text-sm py-2">Save</button>
                                        <button type="button" @click="open = false" class="btn btn-secondary text-sm py-2">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-bankos-muted text-sm">
                                No cheque leaves have been issued yet.
                                @if($chequeBook->status === 'active')
                                    <button @click="$dispatch('open-modal', 'issue-leaf')"
                                            class="block mx-auto mt-3 btn btn-primary text-sm">Issue First Leaf</button>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Issue Leaf Modal --}}
    @if($chequeBook->status === 'active')
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'issue-leaf') show = true"
        @keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Issue Cheque Leaf</h3>
                <button @click="show = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('cheques.issueLeaf', $chequeBook) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Cheque Number <span class="text-red-500">*</span></label>
                    <input type="text" name="cheque_number" class="form-input font-mono"
                           placeholder="Must be in range {{ $chequeBook->series_start }}–{{ $chequeBook->series_end }}" maxlength="20" required>
                </div>
                <div>
                    <label class="form-label">Payee Name</label>
                    <input type="text" name="payee_name" class="form-input" placeholder="Payee on the cheque" maxlength="150">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Amount (₦)</label>
                        <input type="number" name="amount" class="form-input" step="0.01" min="0.01" placeholder="0.00">
                    </div>
                    <div>
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-input" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Drawer Reference</label>
                    <input type="text" name="drawer_reference" class="form-input" placeholder="Internal reference" maxlength="100">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Optional notes..."></textarea>
                </div>
                <p class="text-xs text-bankos-muted">
                    {{ $chequeBook->leaves_remaining }} leaves remaining in this book.
                </p>
                <button type="submit" class="btn btn-primary w-full">Issue Leaf</button>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>
