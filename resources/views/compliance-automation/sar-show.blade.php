<x-app-layout>
    <x-slot name="header">SAR Detail - {{ $sar->reference }}</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex items-center justify-between">
            <a href="{{ route('compliance-auto.sar') }}" class="text-bankos-primary hover:underline text-sm">&larr; Back to SAR Reports</a>
            @if(in_array($sar->status, ['draft', 'pending_review']))
            <form method="POST" action="{{ route('compliance-auto.sar.approve', $sar->id) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Approve for Filing</button>
            </form>
            @endif
        </div>

        {{-- SAR Info --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Report Info</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-bankos-muted">Reference</dt><dd class="font-mono">{{ $sar->reference }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Type</dt><dd>{{ $sar->report_type }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Status</dt><dd>
                        @php $sc = match($sar->status) { 'filed' => 'bg-green-100 text-green-700', 'approved' => 'bg-blue-100 text-blue-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc }}">{{ strtoupper(str_replace('_', ' ', $sar->status)) }}</span>
                    </dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Total Amount</dt><dd class="font-mono">NGN {{ number_format($sar->total_amount, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Category</dt><dd>{{ ucfirst(str_replace('_', ' ', $sar->suspicion_category ?? 'N/A')) }}</dd></div>
                    @if($sar->filing_reference)
                    <div class="flex justify-between"><dt class="text-bankos-muted">Filing Ref</dt><dd class="font-mono">{{ $sar->filing_reference }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Customer</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-bankos-muted">Name</dt><dd>{{ $sar->customer->first_name ?? '' }} {{ $sar->customer->last_name ?? '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Customer No</dt><dd class="font-mono">{{ $sar->customer->customer_number ?? '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">KYC Status</dt><dd>{{ ucfirst($sar->customer->kyc_status ?? 'N/A') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
                <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Workflow</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-bankos-muted">Prepared By</dt><dd>{{ $sar->preparer->name ?? 'System' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Approved By</dt><dd>{{ $sar->approver->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Filed At</dt><dd>{{ $sar->filed_at?->format('M d, Y H:i') ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-bankos-muted">Created</dt><dd>{{ $sar->created_at->format('M d, Y H:i') }}</dd></div>
                </dl>
            </div>
        </div>

        {{-- Narrative --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">AI-Generated Narrative</h3>
            <div class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-wrap text-sm text-bankos-text dark:text-bankos-dark-text">{{ $sar->narrative }}</div>
        </div>

        {{-- Transaction IDs --}}
        @if(!empty($sar->transactions_involved))
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="text-sm font-semibold text-bankos-muted uppercase mb-3">Transactions Involved ({{ count($sar->transactions_involved) }})</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($sar->transactions_involved as $txnId)
                <span class="px-2 py-1 bg-gray-100 dark:bg-bankos-dark-bg rounded text-xs font-mono">{{ $txnId }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
