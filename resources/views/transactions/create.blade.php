<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Post Transaction') }}
        </h2>
    </x-slot>

    <!-- Alpine component to manage active tab (deposit, withdrawal, transfer) -->
    <div class="max-w-4xl mx-auto" x-data="{ txnType: 'deposit' }">
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap border-b border-bankos-border dark:border-bankos-dark-border mb-6">
            <button @click="txnType = 'deposit'" 
                    :class="txnType === 'deposit' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text'"
                    class="py-3 px-6 font-medium text-sm border-b-2 outline-none transition-colors">
                Cash Deposit
            </button>
            <button @click="txnType = 'withdrawal'" 
                    :class="txnType === 'withdrawal' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text'"
                    class="py-3 px-6 font-medium text-sm border-b-2 outline-none transition-colors">
                Cash Withdrawal
            </button>
            <button @click="txnType = 'transfer'" 
                    :class="txnType === 'transfer' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text'"
                    class="py-3 px-6 font-medium text-sm border-b-2 outline-none transition-colors">
                Internal Transfer
            </button>
        </div>

        <!-- Single Entry Form (Deposit / Withdrawal) -->
        <div x-show="txnType === 'deposit' || txnType === 'withdrawal'" class="card p-8" style="display: none;"
             x-transition>
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" x-model="txnType">
                
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
                    <span x-text="txnType === 'deposit' ? 'Receive Cash Deposit' : 'Process Cash Withdrawal'"></span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Account Number <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" value="{{ request('account_number') }}" class="form-input w-full font-mono text-lg" placeholder="Enter 10-digit NUBAN" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-bankos-muted font-bold">₦</div>
                            <input type="number" step="0.01" min="1" name="amount" class="form-input pl-10 w-full font-bold text-2xl" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Transaction Description <span class="text-red-500">*</span></label>
                        <input type="text" name="description" class="form-input w-full" placeholder="e.g., Cash Deposit by Self" required>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                    <button type="submit" class="btn btn-primary" x-text="'Post ' + (txnType === 'deposit' ? 'Deposit' : 'Withdrawal')"></button>
                </div>
            </form>
        </div>

        <!-- Internal Transfer Form -->
        <div x-show="txnType === 'transfer'" class="card p-8" style="display: none;"
             x-transition>
            <form action="{{ route('transactions.transfer') }}" method="POST">
                @csrf
                
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2">
                    Internal Transfer (P2P)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 items-end">
                    
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Debit Account (Source) <span class="text-red-500">*</span></label>
                        <input type="text" name="from_account" class="form-input w-full font-mono text-lg" placeholder="Source Account Number" required>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Credit Account (Destination) <span class="text-red-500">*</span></label>
                        <input type="text" name="to_account" class="form-input w-full font-mono text-lg" placeholder="Destination Account Number" required>
                    </div>

                    <div class="md:col-span-2 pt-4">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Transfer Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-bankos-muted font-bold">₦</div>
                            <input type="number" step="0.01" min="1" name="amount" class="form-input pl-10 w-full font-bold text-2xl" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Transfer Narration <span class="text-red-500">*</span></label>
                        <input type="text" name="description" class="form-input w-full" placeholder="Reason for transfer..." required>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                    <button type="submit" class="btn btn-primary">Execute Transfer</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
