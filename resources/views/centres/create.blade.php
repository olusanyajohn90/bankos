<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('centres.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">New Centre</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Create a new group lending centre</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-6 md:p-8 max-w-2xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ route('centres.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Centre Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" required placeholder="e.g. Oshodi Market Centre">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Centre Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-input font-mono" placeholder="e.g. CTR-001">
                </div>
            </div>

            <div>
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-input">
                    <option value="">— No branch —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Meeting Location</label>
                <input type="text" name="meeting_location" value="{{ old('meeting_location') }}" class="form-input" placeholder="e.g. Community Hall, Block C">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Meeting Day</label>
                    <select name="meeting_day" class="form-input">
                        <option value="">— Select day —</option>
                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <option value="{{ $day }}" {{ old('meeting_day') == $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Meeting Time</label>
                    <input type="time" name="meeting_time" value="{{ old('meeting_time') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="pt-4 border-t border-bankos-border flex justify-end gap-3">
                <a href="{{ route('centres.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">Create Centre</button>
            </div>
        </form>
    </div>
</x-app-layout>
