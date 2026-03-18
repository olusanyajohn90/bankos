<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">Notification Templates</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Customise message templates per event and channel. Use <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">&#123;&#123;variable&#125;&#125;</code> for dynamic values.</p>
            </div>
            <a href="{{ route('notifications.index') }}" class="btn btn-secondary text-sm">View Log</a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Existing Templates -->
        <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
            <div class="px-6 py-4 border-b border-bankos-border">
                <h3 class="font-semibold text-bankos-text">Configured Templates ({{ $templates->count() }})</h3>
            </div>
            @forelse($templates as $tpl)
            <div class="px-6 py-4 border-b border-bankos-border last:border-b-0">
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="badge uppercase tracking-wider text-[10px] bg-blue-100 text-blue-700">{{ $tpl->channel }}</span>
                            <span class="font-semibold text-bankos-text text-sm">{{ $events[$tpl->event] ?? $tpl->event }}</span>
                            @if(!$tpl->active)
                                <span class="badge bg-gray-100 text-gray-500 text-[10px] uppercase">Disabled</span>
                            @endif
                        </div>
                        @if($tpl->subject)
                            <p class="text-xs text-bankos-text-sec mt-1">Subject: {{ $tpl->subject }}</p>
                        @endif
                        <p class="text-sm text-bankos-text mt-1 line-clamp-2">{{ $tpl->body }}</p>
                    </div>
                    <form action="{{ route('notifications.templates.destroy', $tpl) }}" method="POST" class="ml-4 flex-shrink-0" onsubmit="return confirm('Delete this template?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-bankos-text-sec text-sm">No templates configured yet. Add one using the form.</div>
            @endforelse
        </div>

        <!-- Add/Edit Template Form -->
        <div class="card p-6 shadow-sm border border-bankos-border">
            <h3 class="font-semibold text-bankos-text mb-4">Add / Update Template</h3>
            <form action="{{ route('notifications.templates.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Event <span class="text-red-500">*</span></label>
                        <select name="event" class="form-input text-sm" required>
                            <option value="">Select event...</option>
                            @foreach($events as $key => $label)
                                <option value="{{ $key }}" {{ old('event') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Channel <span class="text-red-500">*</span></label>
                        <select name="channel" class="form-input text-sm" required>
                            <option value="">Select channel...</option>
                            @foreach($channels as $ch)
                                <option value="{{ $ch }}" {{ old('channel') == $ch ? 'selected' : '' }}>{{ ucfirst($ch) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Subject <span class="text-xs text-bankos-text-sec">(email only)</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-input text-sm" placeholder="e.g. Your loan has been disbursed">
                </div>

                <div>
                    <label class="form-label">Message Body <span class="text-red-500">*</span></label>
                    <textarea name="body" rows="5" class="form-input text-sm font-mono" required placeholder="Dear @{{customer_name}}, your loan of ₦@{{amount}} has been disbursed...">{{ old('body') }}</textarea>
                    <p class="text-xs text-bankos-text-sec mt-1">Available variables: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;customer_name&#125;&#125;</code> <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;amount&#125;&#125;</code> <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;loan_number&#125;&#125;</code> <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;account_number&#125;&#125;</code> <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">&#123;&#123;due_date&#125;&#125;</code></p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" id="active" value="1"
                        class="w-4 h-4 text-bankos-primary border-gray-300 rounded focus:ring-bankos-primary"
                        {{ old('active', '1') ? 'checked' : '' }}>
                    <label for="active" class="text-sm text-bankos-text cursor-pointer">Template active</label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary w-full shadow-md">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
