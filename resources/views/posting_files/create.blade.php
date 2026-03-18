<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('posting-files.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Upload Posting File</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Upload a CSV file for bulk transaction posting</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Format Guide -->
        <div class="card p-5 border border-blue-200 dark:border-blue-900/30 bg-blue-50/50 dark:bg-blue-900/10">
            <h3 class="font-semibold text-bankos-text mb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Required CSV Format
            </h3>
            <div class="overflow-x-auto">
                <table class="text-xs w-full">
                    <thead>
                        <tr class="text-bankos-text-sec uppercase tracking-wider">
                            <th class="text-left py-1 pr-4">Column</th>
                            <th class="text-left py-1 pr-4">Required</th>
                            <th class="text-left py-1">Values</th>
                        </tr>
                    </thead>
                    <tbody class="font-mono text-bankos-text divide-y divide-blue-100 dark:divide-blue-900/30">
                        <tr><td class="py-1.5 pr-4">identifier_type</td><td class="pr-4 text-red-500">Yes</td><td>BVN / NIN / LOAN_ACCOUNT_NUMBER / ACCOUNT_NUMBER</td></tr>
                        <tr><td class="py-1.5 pr-4">identifier_value</td><td class="pr-4 text-red-500">Yes</td><td>The actual BVN, NIN, or account number</td></tr>
                        <tr><td class="py-1.5 pr-4">amount</td><td class="pr-4 text-red-500">Yes</td><td>Numeric (e.g. 5000 or 5000.00)</td></tr>
                        <tr><td class="py-1.5 pr-4">transaction_date</td><td class="pr-4 text-red-500">Yes</td><td>YYYY-MM-DD</td></tr>
                        <tr><td class="py-1.5 pr-4">payment_channel</td><td class="pr-4 text-bankos-text-sec">No</td><td>mobile, pos, branch, ussd</td></tr>
                        <tr><td class="py-1.5 pr-4">narration</td><td class="pr-4 text-bankos-text-sec">No</td><td>Free text description</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="{{ route('posting-files.template') }}" class="text-bankos-primary text-xs font-medium hover:underline">
                    ↓ Download CSV template
                </a>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="card p-6 shadow-md border-t-4 border-t-bankos-primary">
            <form action="{{ route('posting-files.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label">File Type <span class="text-red-500">*</span></label>
                    <select name="type" class="form-input" required>
                        <option value="repayment" {{ old('type', 'repayment') === 'repayment' ? 'selected' : '' }}>Repayment</option>
                        <option value="deposit" {{ old('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="disbursement" {{ old('type') === 'disbursement' ? 'selected' : '' }}>Disbursement</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">CSV File <span class="text-red-500">*</span></label>
                    <div class="border-2 border-dashed border-bankos-border dark:border-bankos-dark-border rounded-lg p-8 text-center hover:border-bankos-primary transition-colors cursor-pointer"
                         onclick="document.getElementById('file_input').click()">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-bankos-text font-medium" id="file_label">Drag and drop or click to browse</p>
                        <p class="text-xs text-bankos-text-sec mt-1">CSV, TXT, XLSX — max 20 MB</p>
                        <input type="file" id="file_input" name="file" accept=".csv,.txt,.xlsx,.xls" class="hidden"
                               onchange="document.getElementById('file_label').textContent = this.files[0]?.name || 'Drag and drop or click to browse'">
                    </div>
                    @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-4 border-t border-bankos-border flex justify-end gap-3">
                    <a href="{{ route('posting-files.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">
                        Upload & Validate
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
