<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">AML Rules</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Configure automated money laundering detection rules</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Add Rule --}}
        <div class="card">
            <h3 class="font-bold mb-4">Add Rule</h3>
            <form method="POST" action="{{ route('aml.rules.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Rule Name *</label>
                    <input type="text" name="name" class="input w-full" placeholder="Large Cash Deposit" required>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Rule Type *</label>
                    <select name="rule_type" class="input w-full" required>
                        <option value="large_amount">Large Amount Threshold</option>
                        <option value="velocity">Velocity (Frequency)</option>
                        <option value="structuring">Structuring Detection</option>
                        <option value="round_amount">Round Amount Pattern</option>
                        <option value="dormant_spike">Dormant Account Spike</option>
                        <option value="cross_border">Cross-Border Frequency</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Threshold Amount (₦)</label>
                    <input type="number" name="threshold_amount" class="input w-full" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Max Transactions (count)</label>
                    <input type="number" name="threshold_count" class="input w-full">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Time Window (minutes)</label>
                    <input type="number" name="time_window_minutes" class="input w-full" placeholder="60">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Risk Score (1-100)</label>
                    <input type="number" name="risk_score" class="input w-full" min="1" max="100" value="60">
                </div>
                <div class="mb-4">
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="auto_block" value="1" class="rounded"> Auto-block transaction
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-full">Add Rule</button>
            </form>
        </div>

        {{-- Rules list --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse($rules as $rule)
            <div class="card flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-sm">{{ $rule->name }}</p>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $rule->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        @if($rule->auto_block)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Auto-Block</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">
                        Type: {{ str_replace('_', ' ', ucfirst($rule->rule_type)) }}
                        @if($rule->threshold_amount) · Threshold: ₦{{ number_format($rule->threshold_amount, 0) }} @endif
                        @if($rule->threshold_count) · Max: {{ $rule->threshold_count }} txns @endif
                        @if($rule->time_window_minutes) · Window: {{ $rule->time_window_minutes }}min @endif
                        · Score: {{ $rule->risk_score }}/100
                    </p>
                </div>
                <div class="flex gap-2 ml-4">
                    <form method="POST" action="{{ route('aml.rules.toggle', $rule->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $rule->is_active ? 'bg-yellow-50 text-yellow-700' : 'btn-primary' }}">
                            {{ $rule->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('aml.rules.destroy', $rule->id) }}" onsubmit="return confirm('Delete rule?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red-50 text-red-600">Del</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="card text-center text-gray-400 py-12">No AML rules configured yet.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
