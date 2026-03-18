<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Transaction Limits</h2>
            <p class="text-sm text-bankos-text-sec mt-1">KYC-tier based transaction limits</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-bankos-border flex items-center justify-between">
            <p class="font-bold text-sm">Transaction Limits by KYC Tier</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-bankos-dark-bg text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">KYC Tier</th>
                    <th class="px-4 py-3 text-right">Single Debit</th>
                    <th class="px-4 py-3 text-right">Daily Debit</th>
                    <th class="px-4 py-3 text-right">Monthly Debit</th>
                    <th class="px-4 py-3 text-right">Max Balance</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($limits as $limit)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Tier {{ $limit->kyc_tier }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono">₦{{ number_format($limit->single_debit_limit, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono">₦{{ number_format($limit->daily_debit_limit, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono">₦{{ number_format($limit->monthly_debit_limit, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $limit->max_balance_limit ? '₦' . number_format($limit->max_balance_limit, 2) : 'Unlimited' }}</td>
                    <td class="px-4 py-3">
                        <button onclick="openEdit({{ json_encode($limit) }})" class="text-blue-600 hover:underline text-xs font-semibold">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Edit modal --}}
    <div id="editModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display:none">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="font-bold text-lg mb-4">Edit Limit</h3>
            <form method="POST" action="{{ route('aml.limits.update') }}" id="editForm">
                @csrf
                <input type="hidden" name="id" id="edit_id">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Single Debit (₦)</label>
                        <input type="number" name="single_debit_limit" id="edit_single" class="input w-full" step="0.01">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Daily Debit (₦)</label>
                        <input type="number" name="daily_debit_limit" id="edit_daily" class="input w-full" step="0.01">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Monthly Debit (₦)</label>
                        <input type="number" name="monthly_debit_limit" id="edit_monthly" class="input w-full" step="0.01">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Max Balance (₦)</label>
                        <input type="number" name="max_balance_limit" id="edit_balance" class="input w-full" step="0.01">
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1">Save Changes</button>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn flex-1">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEdit(limit) {
        document.getElementById('edit_id').value      = limit.id;
        document.getElementById('edit_single').value  = limit.single_debit_limit;
        document.getElementById('edit_daily').value   = limit.daily_debit_limit;
        document.getElementById('edit_monthly').value = limit.monthly_debit_limit;
        document.getElementById('edit_balance').value = limit.max_balance_limit;
        document.getElementById('editModal').style.display = 'flex';
    }
    </script>
</x-app-layout>
