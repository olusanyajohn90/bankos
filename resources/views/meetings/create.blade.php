<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('groups.show', $group) }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Schedule Meeting</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Group: {{ $group->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="card p-6 md:p-8 max-w-2xl mx-auto shadow-md border-t-4 border-t-bankos-primary">
        <form action="{{ route('groups.meetings.store', $group) }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Meeting Date <span class="text-red-500">*</span></label>
                    <input type="date" name="meeting_date" value="{{ old('meeting_date', now()->format('Y-m-d')) }}" class="form-input" required>
                    @error('meeting_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Meeting Time</label>
                    <input type="time" name="meeting_time" value="{{ old('meeting_time') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Location</label>
                <input type="text" name="location" value="{{ old('location', $group->centre?->meeting_location) }}" class="form-input" placeholder="Where will the meeting take place?">
            </div>

            <div>
                <label class="form-label">Conducted By</label>
                <select name="conducted_by" class="form-input">
                    <option value="">— Select officer —</option>
                    @foreach($loanOfficers as $officer)
                        <option value="{{ $officer->id }}" {{ old('conducted_by', $group->loan_officer_id) == $officer->id ? 'selected' : '' }}>
                            {{ $officer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Agenda or additional notes...">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="scheduled" {{ old('status', 'scheduled') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-lg border border-blue-100 dark:border-blue-900/20 text-sm text-bankos-text-sec">
                <p>An attendance register will be pre-populated with the group's <strong class="text-bankos-primary">{{ $group->activeMembers()->count() }} active members</strong>. You can record attendance and collections from the meeting detail page.</p>
            </div>

            <div class="pt-4 border-t border-bankos-border flex justify-end gap-3">
                <a href="{{ route('groups.show', $group) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">Schedule Meeting</button>
            </div>
        </form>
    </div>
</x-app-layout>
