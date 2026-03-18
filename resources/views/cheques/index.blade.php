<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Cheque Management</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Issue cheque books and process inward/outward cheques</p>
            </div>
            <button @click="$dispatch('open-modal', 'issue-book')" class="btn btn-primary">
                Issue Cheque Book
            </button>
        </div>
    </x-slot>

    {{-- ─── Pending Cheques Requiring Action ──────────────────────────── --}}
    @if ($pendingCheques->isNotEmpty())
    <div class="mb-6 rounded-xl border border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-4">
        <h3 class="font-bold text-sm text-yellow-800 dark:text-yellow-300 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $pendingCheques->count() }} Cheque(s) Awaiting Processing
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-yellow-700 dark:text-yellow-400 border-b border-yellow-200 dark:border-yellow-700">
                        <th class="pb-2 pr-4 text-left font-semibold">Cheque No.</th>
                        <th class="pb-2 px-4 text-left font-semibold">Account</th>
                        <th class="pb-2 px-4 text-left font-semibold">Payee</th>
                        <th class="pb-2 px-4 font-semibold text-right">Amount</th>
                        <th class="pb-2 px-4 font-semibold">Date</th>
                        <th class="pb-2 px-4 font-semibold">Status</th>
                        <th class="pb-2 pl-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-yellow-100 dark:divide-yellow-800/50">
                    @foreach ($pendingCheques as $cheque)
                    <tr x-data="{ open: false }">
                        <td class="py-2 pr-4 font-mono font-bold text-bankos-primary">{{ $cheque->cheque_number }}</td>
                        <td class="py-2 px-4">
                            <p class="font-medium">{{ $cheque->account?->account_number }}</p>
                            <p class="text-xs text-bankos-muted">{{ $cheque->account?->customer?->full_name }}</p>
                        </td>
                        <td class="py-2 px-4 text-bankos-muted">{{ $cheque->payee_name ?? '—' }}</td>
                        <td class="py-2 px-4 text-right font-bold">₦{{ number_format($cheque->amount, 2) }}</td>
                        <td class="py-2 px-4 text-bankos-muted text-xs">{{ $cheque->cheque_date->format('d M Y') }}</td>
                        <td class="py-2 px-4">
                            <span class="badge badge-pending capitalize">{{ $cheque->status }}</span>
                        </td>
                        <td class="py-2 pl-4">
                            <button @click="open = !open" class="text-bankos-primary text-xs font-medium hover:underline">Action</button>
                            <div x-show="open" x-cloak class="mt-2 flex flex-wrap gap-2">
                                @if ($cheque->status === 'presented')
                                <form action="{{ route('cheques.process', $cheque) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="clear">
                                    <button class="btn btn-primary text-xs py-1 px-3">Clear</button>
                                </form>
                                @endif
                                <form action="{{ route('cheques.process', $cheque) }}" method="POST" x-data="{ reason: '' }">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="return">
                                    <input type="text" name="return_reason" x-model="reason" placeholder="Return reason" class="form-input text-xs py-1 w-40" required>
                                    <button class="btn bg-red-500 hover:bg-red-600 text-white text-xs py-1 px-3 ml-1">Return</button>
                                </form>
                                <form action="{{ route('cheques.process', $cheque) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="stop">
                                    <button class="btn btn-secondary text-xs py-1 px-3" onclick="return confirm('Stop this cheque?')">Stop</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Cheque Books Table ───────────────────────────────────── --}}
        <div class="lg:col-span-2 card p-0 overflow-hidden">
            <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
                <h3 class="font-bold text-base">Cheque Books Issued</h3>
                <span class="text-xs text-bankos-muted">{{ $books->total() }} total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-5 py-3 font-semibold">Account</th>
                            <th class="px-5 py-3 font-semibold">Series</th>
                            <th class="px-5 py-3 font-semibold text-center">Leaves</th>
                            <th class="px-5 py-3 font-semibold">Issued</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse ($books as $book)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-bold text-bankos-primary">{{ $book->account?->account_number }}</p>
                                <p class="text-xs text-bankos-muted">{{ $book->account?->customer?->full_name }}</p>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs">{{ $book->series_start }} — {{ $book->series_end }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-semibold">{{ $book->leaves_remaining }}</span>
                                <span class="text-bankos-muted text-xs">/ {{ $book->leaves }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-bankos-muted">{{ $book->issued_date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if ($book->status === 'active')
                                    <span class="badge badge-active">Active</span>
                                @elseif ($book->status === 'exhausted')
                                    <span class="badge badge-dormant">Exhausted</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($book->status === 'active')
                                <form action="{{ route('cheques.books.cancel', $book) }}" method="POST" onsubmit="return confirm('Cancel this cheque book?')">
                                    @csrf @method('PATCH')
                                    <button class="text-xs text-red-500 hover:underline">Cancel</button>
                                </form>
                                @else
                                    <span class="text-bankos-muted text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-bankos-muted">No cheque books issued yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($books->hasPages())
            <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $books->links() }}
            </div>
            @endif
        </div>

        {{-- ─── Record Cheque Form ───────────────────────────────────── --}}
        <div class="card p-6">
            <h3 class="font-bold text-base mb-4">Record Issued Cheque</h3>
            <form action="{{ route('cheques.store') }}" method="POST" class="space-y-4" x-data="{ bookId: '' }">
                @csrf
                <div>
                    <label class="form-label">Account Number <span class="text-red-500">*</span></label>
                    <input type="text" name="account_id" class="form-input" placeholder="Account UUID" required>
                    <span class="form-hint">Paste the account ID from Accounts module.</span>
                </div>
                <div>
                    <label class="form-label">Cheque Book (optional)</label>
                    <input type="text" name="cheque_book_id" class="form-input" placeholder="Cheque Book UUID">
                </div>
                <div>
                    <label class="form-label">Cheque Number <span class="text-red-500">*</span></label>
                    <input type="text" name="cheque_number" class="form-input" placeholder="e.g. 0001234" maxlength="20" required>
                </div>
                <div>
                    <label class="form-label">Amount (₦) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" class="form-input" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
                <div>
                    <label class="form-label">Payee Name</label>
                    <input type="text" name="payee_name" class="form-input" placeholder="Name on cheque" maxlength="255">
                </div>
                <div>
                    <label class="form-label">Cheque Date <span class="text-red-500">*</span></label>
                    <input type="date" name="cheque_date" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary w-full">Record Cheque</button>
            </form>

            {{-- Present a cheque --}}
            <div class="mt-6 pt-5 border-t border-bankos-border dark:border-bankos-dark-border">
                <h4 class="font-semibold text-sm mb-3">Present a Cheque</h4>
                @foreach (\App\Models\ChequeTransaction::where('tenant_id', auth()->user()->tenant_id)->where('status','issued')->latest()->limit(5)->get() as $c)
                <div class="flex items-center justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border last:border-0">
                    <div>
                        <p class="font-mono text-xs font-bold text-bankos-primary">{{ $c->cheque_number }}</p>
                        <p class="text-[10px] text-bankos-muted">₦{{ number_format($c->amount, 2) }} &bull; {{ $c->cheque_date->format('d M') }}</p>
                    </div>
                    <form action="{{ route('cheques.process', $c) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="present">
                        <button class="btn btn-secondary text-xs py-1 px-2">Present</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ─── Issue Cheque Book Modal ─────────────────────────────────── --}}
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'issue-book') show = true"
        @keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Issue Cheque Book</h3>
                <button @click="show = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('cheques.books.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Account ID <span class="text-red-500">*</span></label>
                    <input type="text" name="account_id" class="form-input" placeholder="Account UUID" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Series Start <span class="text-red-500">*</span></label>
                        <input type="text" name="series_start" class="form-input" placeholder="0000001" required>
                    </div>
                    <div>
                        <label class="form-label">Series End <span class="text-red-500">*</span></label>
                        <input type="text" name="series_end" class="form-input" placeholder="0000025" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Number of Leaves</label>
                    <input type="number" name="leaves" class="form-input" value="25" min="1" max="200">
                </div>
                <div>
                    <label class="form-label">Issue Date <span class="text-red-500">*</span></label>
                    <input type="date" name="issued_date" class="form-input" value="{{ now()->toDateString() }}" required>
                </div>
                <button type="submit" class="btn btn-primary w-full">Issue Book</button>
            </form>
        </div>
    </div>
</x-app-layout>
