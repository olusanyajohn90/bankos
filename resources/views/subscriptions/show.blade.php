<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('subscriptions.index') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-bankos-dark-text transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $tenant->name }}</h1>
                <p class="text-sm text-bankos-muted mt-0.5">Subscription detail · {{ $subscription->plan?->name ?? 'Unknown' }} plan</p>
            </div>
        </div>
    </x-slot>

    <div x-data="{ paymentModal: false, planModal: false, suspendModal: false }">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Column: Subscription Info + Usage -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Subscription Card -->
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Subscription</h2>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $subscription->statusBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button @click="planModal = true" class="px-4 py-2 text-sm border border-bankos-border dark:border-bankos-dark-border rounded-xl hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors font-medium">
                                Change Plan
                            </button>
                            @if($tenant->suspended_at)
                            <form method="POST" action="{{ route('subscriptions.unsuspend', $tenant->id) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition-colors">Unsuspend</button>
                            </form>
                            @else
                            <button @click="suspendModal = true" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors">Suspend</button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Current Plan</p>
                            <p class="font-bold text-bankos-text dark:text-bankos-dark-text">{{ $subscription->plan?->name ?? '—' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Billing Cycle</p>
                            <p class="font-bold text-bankos-text dark:text-bankos-dark-text capitalize">{{ $subscription->billing_cycle }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Period End</p>
                            <p class="font-bold text-bankos-text dark:text-bankos-dark-text">
                                {{ $subscription->current_period_end?->format('d M Y') ?? '—' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Trial Ends</p>
                            <p class="font-bold text-bankos-text dark:text-bankos-dark-text">
                                {{ $subscription->trial_ends_at?->format('d M Y') ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Total Paid</p>
                            <p class="font-bold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($subscription->amount_paid, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Paystack Code</p>
                            <p class="font-mono text-xs text-bankos-text dark:text-bankos-dark-text truncate">
                                {{ $subscription->paystack_subscription_code ?? 'Not set' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Usage Metrics -->
                @if($currentUsage || $subscription->plan)
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-4">
                        Usage — {{ now()->format('F Y') }}
                    </h2>
                    @php
                        $plan  = $subscription->plan;
                        $usage = $currentUsage;
                        $metrics = [
                            ['label' => 'Customers', 'used' => $usage?->customer_count ?? 0, 'max' => $plan?->max_customers],
                            ['label' => 'Staff Users', 'used' => $usage?->staff_count ?? 0, 'max' => $plan?->max_staff_users],
                            ['label' => 'Branches', 'used' => $usage?->branch_count ?? 0, 'max' => $plan?->max_branches],
                            ['label' => 'Transactions', 'used' => $usage?->transaction_count ?? 0, 'max' => $plan?->max_transactions_monthly],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach($metrics as $m)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $m['label'] }}</span>
                                <span class="text-bankos-muted">
                                    {{ number_format($m['used']) }} / {{ $m['max'] ? number_format($m['max']) : '∞' }}
                                </span>
                            </div>
                            @if($m['max'])
                                @php $pct = min(100, round(($m['used'] / $m['max']) * 100)); @endphp
                                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-green-500') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-xs text-bankos-muted mt-0.5">{{ $pct }}% used</p>
                            @else
                                <div class="h-2 bg-green-200 rounded-full"></div>
                                <p class="text-xs text-bankos-muted mt-0.5">Unlimited</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Invoice History -->
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b border-bankos-border dark:border-bankos-dark-border">
                        <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">Invoice History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-muted uppercase tracking-wider">Invoice #</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-muted uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-muted uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-muted uppercase tracking-wider">Due Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-muted uppercase tracking-wider">Paid At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                                @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/40 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs font-medium text-bankos-text dark:text-bankos-dark-text">{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-3 font-semibold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($invoice->amount, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $cls = match($invoice->status) {
                                                'paid'    => 'bg-green-100 text-green-800',
                                                'overdue' => 'bg-red-100 text-red-800',
                                                'sent'    => 'bg-blue-100 text-blue-800',
                                                'void'    => 'bg-gray-100 text-gray-600',
                                                default   => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">{{ ucfirst($invoice->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-bankos-muted">{{ $invoice->due_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-bankos-muted">{{ $invoice->paid_at?->format('d M Y') ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-bankos-muted">No invoices found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Record Payment -->
            <div class="space-y-6">
                <!-- Record Payment -->
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Record Payment</h2>

                    @if(session('success'))
                    <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 rounded-xl p-3 text-sm text-green-700 dark:text-green-400">
                        {{ session('success') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('subscriptions.record-payment', $tenant->id) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Amount (₦)</label>
                                <input type="number" name="amount" step="0.01" min="1" placeholder="45000.00"
                                       class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-3 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Billing Period</label>
                                <select name="billing_period" class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-3 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-bankos-muted mb-1.5">Payment Reference</label>
                                <input type="text" name="reference" placeholder="PSK_xxxxx or manual ref"
                                       class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-3 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">
                                Record Payment & Activate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tenant Info -->
                <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                    <h2 class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text mb-4 uppercase tracking-wider">Tenant Info</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-bankos-muted">Type</dt>
                            <dd class="font-medium text-bankos-text dark:text-bankos-dark-text capitalize">{{ $tenant->type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bankos-muted">Contact Email</dt>
                            <dd class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $tenant->contact_email ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bankos-muted">Phone</dt>
                            <dd class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $tenant->contact_phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bankos-muted">Status</dt>
                            <dd>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($tenant->status) }}
                                </span>
                            </dd>
                        </div>
                        @if($tenant->suspended_at)
                        <div>
                            <dt class="text-xs text-bankos-muted">Suspended</dt>
                            <dd class="text-xs text-red-600">{{ $tenant->suspended_at->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-bankos-muted">Reason</dt>
                            <dd class="text-xs text-bankos-text dark:text-bankos-dark-text">{{ $tenant->suspension_reason ?? '—' }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Suspend Modal -->
        <div x-show="suspendModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display:none">
            <div @click.outside="suspendModal = false" class="bg-white dark:bg-bankos-dark-surface rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Suspend {{ $tenant->name }}</h3>
                <form method="POST" action="{{ route('subscriptions.suspend', $tenant->id) }}">
                    @csrf
                    <textarea name="reason" rows="3" required placeholder="Reason for suspension..."
                              class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-red-500 resize-none mb-4"></textarea>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="suspendModal = false" class="px-5 py-2 text-sm rounded-xl border border-bankos-border hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-colors">Suspend</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Plan Modal -->
        <div x-show="planModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display:none">
            <div @click.outside="planModal = false" class="bg-white dark:bg-bankos-dark-surface rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-4">Change Plan</h3>
                <form method="POST" action="{{ route('subscriptions.change-plan', $tenant->id) }}">
                    @csrf
                    <div class="space-y-4 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-bankos-muted mb-1.5">New Plan</label>
                            <select name="plan_id" class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" {{ $subscription->plan_id === $p->id ? 'selected' : '' }}>
                                        {{ $p->name }} — {{ $p->formattedMonthlyPrice() }}/mo
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-bankos-muted mb-1.5">Billing Cycle</label>
                            <select name="billing_cycle" class="w-full border border-bankos-border dark:border-bankos-dark-border rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-bankos-dark-bg focus:outline-none focus:ring-2 focus:ring-bankos-primary">
                                <option value="monthly" {{ $subscription->billing_cycle === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $subscription->billing_cycle === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="planModal = false" class="px-5 py-2 text-sm rounded-xl border border-bankos-border hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
