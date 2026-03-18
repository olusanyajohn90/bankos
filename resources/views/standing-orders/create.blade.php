<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('standing-orders.index') }}"
               class="text-bankos-muted hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Create Standing Order
                </h2>
                <p class="text-sm text-bankos-text-sec mt-0.5">Set up a recurring automated transfer</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto"
         x-data="{
            transferType: '{{ old('transfer_type', 'internal') }}',
            accounts: @js($accounts->map(fn($a) => [
                'id'               => $a->id,
                'account_number'   => $a->account_number,
                'customer_name'    => $a->customer ? $a->customer->first_name . ' ' . $a->customer->last_name : 'Unknown',
                'available_balance'=> (float) $a->available_balance,
            ])),
         }">

        <form action="{{ route('standing-orders.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm font-semibold text-red-700 dark:text-red-300 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-600 dark:text-red-400">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Step 1: Source Account --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">1</span>
                    Source Account
                </h3>
                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                        Debit Account <span class="text-red-500">*</span>
                    </label>
                    <select name="source_account_id" class="form-select w-full" required>
                        <option value="">Select account to debit...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}"
                                    {{ old('source_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_number }}
                                — {{ $account->customer?->first_name }} {{ $account->customer?->last_name }}
                                — ₦{{ number_format($account->available_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Step 2: Transfer Type & Beneficiary --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">2</span>
                    Transfer Type & Beneficiary
                </h3>

                {{-- Transfer Type Toggle --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-3">Transfer Type <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition-all flex-1"
                               :class="transferType === 'internal' ? 'border-bankos-primary bg-blue-50/50 dark:bg-blue-900/10' : 'border-bankos-border dark:border-bankos-dark-border hover:border-gray-300'">
                            <input type="radio" name="transfer_type" value="internal"
                                   x-model="transferType"
                                   {{ old('transfer_type', 'internal') === 'internal' ? 'checked' : '' }}
                                   class="text-bankos-primary focus:ring-bankos-primary">
                            <div>
                                <p class="font-semibold text-sm text-bankos-text dark:text-white">Internal Transfer</p>
                                <p class="text-xs text-bankos-muted mt-0.5">Transfer to another account within the institution</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-4 rounded-lg border-2 transition-all flex-1"
                               :class="transferType === 'external' ? 'border-bankos-primary bg-blue-50/50 dark:bg-blue-900/10' : 'border-bankos-border dark:border-bankos-dark-border hover:border-gray-300'">
                            <input type="radio" name="transfer_type" value="external"
                                   x-model="transferType"
                                   {{ old('transfer_type') === 'external' ? 'checked' : '' }}
                                   class="text-bankos-primary focus:ring-bankos-primary">
                            <div>
                                <p class="font-semibold text-sm text-bankos-text dark:text-white">External Transfer</p>
                                <p class="text-xs text-bankos-muted mt-0.5">Transfer to a bank account at another institution</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Internal: Destination Account --}}
                <div x-show="transferType === 'internal'" x-transition>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                        Destination Account <span class="text-red-500">*</span>
                    </label>
                    <select name="internal_dest_account_id" class="form-select w-full"
                            :required="transferType === 'internal'">
                        <option value="">Select destination account...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}"
                                    {{ old('internal_dest_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_number }}
                                — {{ $account->customer?->first_name }} {{ $account->customer?->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-bankos-muted mt-1">Select the account to credit on each run.</p>
                </div>

                {{-- External: Beneficiary Details --}}
                <div x-show="transferType === 'external'" x-transition class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                                Beneficiary Account Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="beneficiary_account_number"
                                   value="{{ old('beneficiary_account_number') }}"
                                   class="form-input w-full font-mono"
                                   placeholder="0123456789"
                                   maxlength="20"
                                   :required="transferType === 'external'">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                                Bank Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="beneficiary_bank_code"
                                   value="{{ old('beneficiary_bank_code') }}"
                                   class="form-input w-full font-mono"
                                   placeholder="058"
                                   maxlength="10"
                                   :required="transferType === 'external'">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Beneficiary Name
                        </label>
                        <input type="text" name="beneficiary_name"
                               value="{{ old('beneficiary_name') }}"
                               class="form-input w-full"
                               placeholder="e.g. John Doe"
                               maxlength="150">
                    </div>
                </div>
            </div>

            {{-- Step 3: Transfer Details --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">3</span>
                    Transfer Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Amount (₦) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount"
                               value="{{ old('amount') }}"
                               step="0.01" min="1"
                               class="form-input w-full"
                               placeholder="e.g. 50000"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Frequency <span class="text-red-500">*</span>
                        </label>
                        <select name="frequency" class="form-select w-full" required>
                            @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $val => $label)
                                <option value="{{ $val }}" {{ old('frequency', 'monthly') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Narration / Reference
                        </label>
                        <input type="text" name="narration"
                               value="{{ old('narration') }}"
                               class="form-input w-full"
                               placeholder="e.g. Monthly savings transfer"
                               maxlength="255">
                    </div>
                </div>
            </div>

            {{-- Step 4: Schedule --}}
            <div class="card p-6">
                <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-bankos-primary text-white flex items-center justify-center text-xs font-bold">4</span>
                    Schedule
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="start_date"
                               value="{{ old('start_date', now()->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="form-input w-full"
                               required>
                        <p class="text-xs text-bankos-muted mt-1">First run date</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            End Date
                        </label>
                        <input type="date" name="end_date"
                               value="{{ old('end_date') }}"
                               class="form-input w-full">
                        <p class="text-xs text-bankos-muted mt-1">Optional — leave blank to run indefinitely</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">
                            Max Runs
                        </label>
                        <input type="number" name="max_runs"
                               value="{{ old('max_runs') }}"
                               min="1"
                               class="form-input w-full"
                               placeholder="e.g. 12">
                        <p class="text-xs text-bankos-muted mt-1">Optional — maximum number of executions</p>
                    </div>
                </div>

                {{-- Schedule summary --}}
                <div class="mt-5 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-medium mb-1">How this works</p>
                    <p class="text-xs">The standing order will execute automatically on the start date, then repeat at the chosen frequency. If the source account has insufficient balance, the order will be marked as failed for that run. Runs continue until the end date, max runs limit, or until manually cancelled.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-4">
                <a href="{{ route('standing-orders.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Create Standing Order
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
