<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('groups.show', $group) }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Edit Group: {{ $group->name }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Update group settings</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-6 md:p-8 max-w-2xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ route('groups.update', $group) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Group Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $group->name) }}" class="form-input" required>
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Group Code</label>
                    <input type="text" name="code" value="{{ old('code', $group->code) }}" class="form-input font-mono">
                </div>
            </div>

            <div>
                <label class="form-label">Centre</label>
                <select name="centre_id" class="form-input">
                    <option value="">— No centre —</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->id }}" {{ old('centre_id', $group->centre_id) == $centre->id ? 'selected' : '' }}>{{ $centre->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-input">
                    <option value="">— No branch —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $group->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Assigned Loan Officer</label>
                <select name="loan_officer_id" class="form-input">
                    <option value="">— Not assigned —</option>
                    @foreach($loanOfficers as $officer)
                        <option value="{{ $officer->id }}" {{ old('loan_officer_id', $group->loan_officer_id) == $officer->id ? 'selected' : '' }}>
                            {{ $officer->first_name }} {{ $officer->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3 p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-900/20">
                <input type="hidden" name="solidarity_guarantee" value="0">
                <input type="checkbox" name="solidarity_guarantee" id="solidarity_guarantee" value="1"
                    class="w-4 h-4 text-bankos-primary bg-white border-gray-300 rounded focus:ring-bankos-primary"
                    {{ old('solidarity_guarantee', $group->solidarity_guarantee) ? 'checked' : '' }}>
                <div>
                    <label for="solidarity_guarantee" class="font-medium text-bankos-text cursor-pointer">Enable Solidarity Guarantee</label>
                    <p class="text-xs text-bankos-text-sec mt-0.5">Group members can be held liable for a peer's default</p>
                </div>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $group->notes) }}</textarea>
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="active" {{ old('status', $group->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $group->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="dissolved" {{ old('status', $group->status) === 'dissolved' ? 'selected' : '' }}>Dissolved</option>
                </select>
            </div>

            <div class="pt-4 border-t border-bankos-border flex justify-end gap-3">
                <a href="{{ route('groups.show', $group) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">Update Group</button>
            </div>
        </form>
    </div>
</x-app-layout>
