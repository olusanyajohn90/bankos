<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.campaigns') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Create Campaign</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Set up a new marketing campaign</p>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('marketing.campaigns.store') }}" method="POST" x-data="{
        channel: '{{ old('channel', 'sms') }}',
        useTemplate: {{ old('template_id') ? 'true' : 'false' }},
        scheduleType: '{{ old('scheduled_at') ? 'scheduled' : 'now' }}',
        segmentId: '{{ old('segment_id', '') }}',
    }" class="max-w-4xl space-y-6">
        @csrf

        {{-- Campaign Details --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Campaign Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Campaign Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="e.g. March Savings Promo">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Brief description of campaign goals">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Type *</label>
                    <select name="type" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="broadcast" {{ old('type') === 'broadcast' ? 'selected' : '' }}>Broadcast</option>
                        <option value="cross_sell" {{ old('type') === 'cross_sell' ? 'selected' : '' }}>Cross-sell</option>
                        <option value="event_triggered" {{ old('type') === 'event_triggered' ? 'selected' : '' }}>Event-triggered</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Channel *</label>
                    <select name="channel" x-model="channel" required class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                        <option value="sms">SMS</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Audience --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Audience</h3>
            <div>
                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Target Segment</label>
                <select name="segment_id" x-model="segmentId" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Active Customers</option>
                    @foreach($segments as $segment)
                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ number_format($segment->cached_count) }} customers)</option>
                    @endforeach
                </select>
                <p class="text-xs text-bankos-muted mt-1">Leave blank to target all active customers. Unsubscribed customers are automatically excluded.</p>
            </div>
        </div>

        {{-- Message Content --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Message Content</h3>

            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="useTemplate" class="rounded border-bankos-border text-bankos-primary">
                    <span class="text-sm text-bankos-text dark:text-bankos-dark-text">Use a template</span>
                </label>
            </div>

            <div x-show="useTemplate" x-transition class="mb-4">
                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Template</label>
                <select name="template_id" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">-- Select template --</option>
                    @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" data-channel="{{ $tpl->channel }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                        {{ $tpl->name }} ({{ strtoupper($tpl->channel) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div x-show="!useTemplate" x-transition class="space-y-4">
                <div x-show="channel === 'email'">
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Subject Line</label>
                    <input type="text" name="custom_subject" value="{{ old('custom_subject') }}" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Email subject">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Message Body</label>
                    <textarea name="custom_message" rows="5" class="w-full rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2" placeholder="Type your message here. Use placeholders like {first_name}, {account_number}, {balance}">{{ old('custom_message') }}</textarea>
                    <p class="text-xs text-bankos-muted mt-1">Available placeholders: {first_name}, {last_name}, {full_name}, {phone}, {email}, {account_number}, {balance}</p>
                </div>
            </div>
        </div>

        {{-- Scheduling --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Scheduling</h3>
            <div class="flex items-center gap-6 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="scheduleType" value="now" class="text-bankos-primary">
                    <span class="text-sm text-bankos-text dark:text-bankos-dark-text">Save as draft (send manually)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="scheduleType" value="scheduled" class="text-bankos-primary">
                    <span class="text-sm text-bankos-text dark:text-bankos-dark-text">Schedule for later</span>
                </label>
            </div>
            <div x-show="scheduleType === 'scheduled'" x-transition>
                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Schedule Date & Time</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="rounded-lg border-bankos-border dark:border-bankos-dark-border dark:bg-bankos-dark-bg text-sm px-3 py-2">
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('marketing.campaigns') }}" class="px-4 py-2 text-sm text-bankos-muted hover:text-bankos-text dark:hover:text-white">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                Create Campaign
            </button>
        </div>
    </form>
</x-app-layout>
