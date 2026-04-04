<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Create Regulatory Report</h2></div>
            <a href="{{ route('regulatory.index') }}" class="btn btn-outline text-sm">Back</a>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('regulatory.store') }}" class="card p-6 space-y-5">
            @csrf
            <div><label class="label">Report Type</label><select name="report_type" class="input w-full" required><option value="cbn_returns">CBN Returns</option><option value="ndic_premium">NDIC Premium</option><option value="nfiu_ctr">NFIU CTR</option><option value="nfiu_str">NFIU STR</option><option value="prudential_guidelines">Prudential Guidelines</option></select></div>
            <div><label class="label">Report Name</label><input type="text" name="report_name" value="{{ old('report_name') }}" class="input w-full" required placeholder="e.g. Monthly CBN Returns - March 2026"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="label">Period</label><input type="text" name="period" value="{{ old('period') }}" class="input w-full" required placeholder="e.g. 2026-03, 2026-Q1"></div>
                <div><label class="label">Due Date</label><input type="date" name="due_date" value="{{ old('due_date') }}" class="input w-full" required></div>
            </div>
            <div><label class="label">Notes</label><textarea name="notes" class="input w-full" rows="3">{{ old('notes') }}</textarea></div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('regulatory.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Report</button>
            </div>
        </form>
    </div>
</x-app-layout>
