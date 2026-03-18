<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Teller Workstation
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </x-slot>

    {{-- ─── Session Status Banner ─────────────────────────────────────── --}}
    @if ($mySession)
        @php
            $statusColors = [
                'open'       => 'border-green-400 bg-green-50 dark:bg-green-900/20',
                'balanced'   => 'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
                'unbalanced' => 'border-red-400 bg-red-50 dark:bg-red-900/20',
                'closed'     => 'border-gray-300 bg-gray-50 dark:bg-gray-800/20',
            ];
            $sc = $statusColors[$mySession->status] ?? $statusColors['open'];
        @endphp
        <div class="mb-6 rounded-xl border-l-4 p-5 {{ $sc }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-bankos-muted">Active Session</p>
                    <p class="text-lg font-bold mt-0.5">
                        {{ $mySession->branch?->name ?? 'Branch' }} &mdash;
                        <span class="capitalize">{{ $mySession->status }}</span>
                    </p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Opening</p>
                        <p class="font-bold text-lg">₦{{ number_format($mySession->opening_cash, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Cash In</p>
                        <p class="font-bold text-lg text-bankos-success">₦{{ number_format($mySession->cash_in, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Cash Out</p>
                        <p class="font-bold text-lg text-accent-crimson">₦{{ number_format($mySession->cash_out, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-bankos-muted uppercase tracking-wider">Expected Closing</p>
                        <p class="font-bold text-lg">₦{{ number_format($mySession->expected_closing, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-xl border border-dashed border-bankos-border dark:border-bankos-dark-border p-5 text-center text-bankos-muted">
            <svg class="mx-auto mb-2 w-8 h-8 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm font-medium">No teller session open today. Open a session to start processing transactions.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Left Column: Open / Close Session ─────────────────── --}}
        <div class="space-y-6">

            {{-- Open Session Form --}}
            @if (!$mySession)
            <div class="card p-6">
                <h3 class="font-bold text-base mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-bankos-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Open Teller Session
                </h3>
                <form action="{{ route('teller.open') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">Branch <span class="text-red-500">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            <option value="">— Select Branch —</option>
                            @foreach (\App\Models\Branch::where('tenant_id', auth()->user()->tenant_id)->get() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Opening Cash (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="opening_cash" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Open Session</button>
                </form>
            </div>
            @endif

            {{-- Close Session Form --}}
            @if ($mySession && $mySession->status === 'open')
            <div class="card p-6 border-t-4 border-t-orange-400">
                <h3 class="font-bold text-base mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Close Session
                </h3>
                <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-bankos-dark-bg text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-bankos-muted">Expected:</span><span class="font-bold">₦{{ number_format($mySession->expected_closing, 2) }}</span></div>
                </div>
                <form action="{{ route('teller.close', $mySession) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="form-label">Actual Closing Cash (₦) <span class="text-red-500">*</span></label>
                        <input type="number" name="closing_cash" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Any remarks..."></textarea>
                    </div>
                    <button type="submit" class="btn w-full bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400">Close & Balance</button>
                </form>
            </div>
            @endif

            {{-- Closed/Balanced session summary --}}
            @if ($mySession && in_array($mySession->status, ['balanced', 'unbalanced', 'closed']))
            <div class="card p-6 {{ $mySession->status === 'balanced' ? 'border-t-4 border-t-bankos-success' : 'border-t-4 border-t-accent-crimson' }}">
                <h3 class="font-bold text-base mb-3">Session Summary</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-bankos-muted">Opening Cash</dt><dd class="font-medium">₦{{ number_format($mySession->opening_cash, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">+ Cash In</dt><dd class="font-medium text-bankos-success">₦{{ number_format($mySession->cash_in, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">− Cash Out</dt><dd class="font-medium text-accent-crimson">₦{{ number_format($mySession->cash_out, 2) }}</dd></div>
                    <div class="flex justify-between border-t border-bankos-border dark:border-bankos-dark-border pt-2"><dt class="font-semibold">Expected</dt><dd class="font-bold">₦{{ number_format($mySession->expected_closing, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="font-semibold">Actual</dt><dd class="font-bold">₦{{ number_format($mySession->closing_cash, 2) }}</dd></div>
                    <div class="flex justify-between">
                        <dt class="font-semibold">Variance</dt>
                        <dd class="font-bold {{ $mySession->variance == 0 ? 'text-bankos-success' : 'text-accent-crimson' }}">
                            ₦{{ number_format($mySession->variance, 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between pt-1">
                        <dt class="text-bankos-muted">Status</dt>
                        <dd>
                            @if ($mySession->status === 'balanced')
                                <span class="badge badge-active">Balanced</span>
                            @elseif ($mySession->status === 'unbalanced')
                                <span class="badge badge-danger">Unbalanced</span>
                            @else
                                <span class="badge badge-dormant">Closed</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            @endif

        </div>

        {{-- ─── Middle Column: Deposit & Withdrawal Forms ─────────── --}}
        <div class="lg:col-span-2 space-y-6">

            @if ($mySession && $mySession->status === 'open')

            {{-- Cash Deposit --}}
            <div class="card p-6 border-t-4 border-t-bankos-success" x-data="accountLookup('deposit')">
                <h3 class="font-bold text-base mb-4 flex items-center gap-2 text-bankos-success">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Cash Deposit
                </h3>
                <form action="{{ route('teller.deposit') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="account_id" :value="accountId">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Account Number <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" x-model="accountNumber" @keyup.enter.prevent="lookup" class="form-input" placeholder="e.g. 1000001234" maxlength="20">
                                <button type="button" @click="lookup" class="btn btn-secondary shrink-0">Find</button>
                            </div>
                            <p x-show="customerName" x-text="customerName" class="text-xs text-bankos-success mt-1 font-medium"></p>
                            <p x-show="lookupError" x-text="lookupError" class="text-xs text-red-500 mt-1"></p>
                        </div>
                        <div>
                            <label class="form-label">Amount (₦) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="form-input" step="0.01" min="1" placeholder="0.00" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Narration</label>
                        <input type="text" name="narration" class="form-input" placeholder="Cash deposit" maxlength="255">
                    </div>
                    <button type="submit" :disabled="!accountId" class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        Post Deposit
                    </button>
                </form>
            </div>

            {{-- Cash Withdrawal --}}
            <div class="card p-6 border-t-4 border-t-accent-crimson" x-data="accountLookup('withdrawal')">
                <h3 class="font-bold text-base mb-4 flex items-center gap-2 text-accent-crimson">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Cash Withdrawal
                </h3>
                <form action="{{ route('teller.withdrawal') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="account_id" :value="accountId">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Account Number <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" x-model="accountNumber" @keyup.enter.prevent="lookup" class="form-input" placeholder="e.g. 1000001234" maxlength="20">
                                <button type="button" @click="lookup" class="btn btn-secondary shrink-0">Find</button>
                            </div>
                            <div x-show="customerName" class="text-xs mt-1 space-y-0.5">
                                <p x-text="customerName" class="font-medium text-bankos-text dark:text-white"></p>
                                <p x-show="balance" class="text-bankos-muted">Available: <span x-text="'₦' + balance" class="font-semibold text-bankos-text dark:text-white"></span></p>
                                <p x-show="pnd" class="text-red-500 font-semibold">⚠ PND Active — Withdrawals Blocked</p>
                            </div>
                            <p x-show="lookupError" x-text="lookupError" class="text-xs text-red-500 mt-1"></p>
                        </div>
                        <div>
                            <label class="form-label">Amount (₦) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="form-input" step="0.01" min="1" placeholder="0.00" required>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Narration</label>
                        <input type="text" name="narration" class="form-input" placeholder="Cash withdrawal" maxlength="255">
                    </div>
                    <button type="submit" :disabled="!accountId || pnd" class="btn bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        Process Withdrawal
                    </button>
                </form>
            </div>

            @else
                <div class="card p-10 text-center text-bankos-muted">
                    <svg class="mx-auto mb-3 w-10 h-10 opacity-30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <p class="font-medium">
                        @if($mySession)
                            Session is {{ $mySession->status }}. No further transactions can be posted.
                        @else
                            Open a teller session to post deposits and withdrawals.
                        @endif
                    </p>
                </div>
            @endif

        </div>
    </div>

    {{-- ─── Branch Sessions Table ───────────────────────────────────── --}}
    <div class="mt-8 card p-0 overflow-hidden">
        <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-bold text-base">Today's Teller Sessions — All Branch</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-3 font-semibold">Teller</th>
                        <th class="px-5 py-3 font-semibold">Branch</th>
                        <th class="px-5 py-3 font-semibold text-right">Opening</th>
                        <th class="px-5 py-3 font-semibold text-right">Cash In</th>
                        <th class="px-5 py-3 font-semibold text-right">Cash Out</th>
                        <th class="px-5 py-3 font-semibold text-right">Expected</th>
                        <th class="px-5 py-3 font-semibold text-right">Variance</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($branchSessions as $s)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $s->teller_id === auth()->id() ? 'bg-blue-50/40 dark:bg-blue-900/10' : '' }}">
                        <td class="px-5 py-3 font-medium">
                            {{ $s->teller->name ?? 'N/A' }}
                            @if ($s->teller_id === auth()->id())
                                <span class="ml-1 badge badge-pending text-[10px]">You</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-bankos-muted">{{ $s->branch?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-mono">₦{{ number_format($s->opening_cash, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono text-bankos-success">₦{{ number_format($s->cash_in, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono text-accent-crimson">₦{{ number_format($s->cash_out, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono">₦{{ number_format((float)$s->opening_cash + (float)$s->cash_in - (float)$s->cash_out, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono">
                            @if (!is_null($s->variance))
                                <span class="{{ $s->variance == 0 ? 'text-bankos-success' : 'text-accent-crimson font-bold' }}">
                                    ₦{{ number_format($s->variance, 2) }}
                                </span>
                            @else
                                <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if ($s->status === 'open')
                                <span class="badge badge-active">Open</span>
                            @elseif ($s->status === 'balanced')
                                <span class="badge bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Balanced</span>
                            @elseif ($s->status === 'unbalanced')
                                <span class="badge badge-danger">Unbalanced</span>
                            @else
                                <span class="badge badge-dormant">Closed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-bankos-muted">No teller sessions today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function accountLookup(type) {
        return {
            accountNumber: '',
            accountId: null,
            customerName: '',
            balance: '',
            pnd: false,
            lookupError: '',
            async lookup() {
                this.lookupError = '';
                this.customerName = '';
                this.accountId = null;
                this.balance = '';
                this.pnd = false;
                if (!this.accountNumber.trim()) return;
                try {
                    const res = await fetch(`{{ route('teller.lookup') }}?account_number=${encodeURIComponent(this.accountNumber)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.lookupError = data.error || 'Account not found.';
                        return;
                    }
                    this.accountId    = data.id;
                    this.customerName = `${data.account_name} (${data.customer_name})`;
                    this.balance      = data.balance;
                    this.pnd          = data.pnd_active;
                } catch(e) {
                    this.lookupError = 'Lookup failed. Try again.';
                }
            }
        }
    }
    </script>
</x-app-layout>
