@extends('layouts.app')

@section('title', isset($message) ? 'Edit Message' : 'Compose Message')

@section('content')
@php
    $isEdit      = isset($message);
    $formAction  = $isEdit ? route('comms.messages.update', $message) : route('comms.messages.store');
    $formMethod  = $isEdit ? 'PUT' : 'POST';

    $selectedScopeType = old('scope_type', $message->scope_type ?? 'all');
    $selectedScopeId   = old('scope_id', $message->scope_id ?? '');
@endphp

<div class="max-w-7xl mx-auto"
     x-data="{
         scopeType: '{{ $selectedScopeType }}',
         requiresAck: {{ (old('requires_ack', $message->requires_ack ?? false)) ? 'true' : 'false' }},
         publishNow: false,
         bodyContent: @js(old('body', $message->body ?? '')),
         attachments: [],
         addFiles(e) {
             for (const f of e.target.files) {
                 this.attachments.push({ name: f.name, size: (f.size / 1024).toFixed(1) + ' KB', file: f });
             }
         },
         removeAttachment(index) {
             this.attachments.splice(index, 1);
         }
     }">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-bankos-text-sec mb-4">
        <a href="{{ route('comms.messages.index') }}" class="hover:text-bankos-primary flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Communications
        </a>
        <span>/</span>
        <span class="text-bankos-text font-medium">{{ $isEdit ? 'Edit Draft' : 'Compose' }}</span>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            <p class="font-semibold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        @if ($isEdit) @method('PUT') @endif
        <input type="hidden" name="publish" :value="publishNow ? '1' : '0'">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ===== LEFT: Message Body (2/3) ===== --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text mb-5">
                        {{ $isEdit ? 'Edit Draft Message' : 'Compose New Message' }}
                    </h2>

                    {{-- Subject --}}
                    <div class="mb-5">
                        <label for="subject" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="subject" name="subject"
                               value="{{ old('subject', $message->subject ?? '') }}"
                               required placeholder="Enter message subject..."
                               class="form-input w-full @error('subject') border-red-400 @enderror">
                        @error('subject')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Type & Priority --}}
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label for="type" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                                Message Type <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required class="form-select w-full @error('type') border-red-400 @enderror">
                                <option value="">-- Select type --</option>
                                <option value="memo" {{ old('type', $message->type ?? '') === 'memo' ? 'selected' : '' }}>Memo</option>
                                <option value="circular" {{ old('type', $message->type ?? '') === 'circular' ? 'selected' : '' }}>Circular</option>
                                <option value="announcement" {{ old('type', $message->type ?? '') === 'announcement' ? 'selected' : '' }}>Announcement</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select id="priority" name="priority" required class="form-select w-full @error('priority') border-red-400 @enderror">
                                <option value="">-- Select priority --</option>
                                <option value="normal" {{ old('priority', $message->priority ?? '') === 'normal' ? 'selected' : '' }}
                                        class="text-gray-600">Normal</option>
                                <option value="urgent" {{ old('priority', $message->priority ?? '') === 'urgent' ? 'selected' : '' }}
                                        class="text-amber-600">Urgent</option>
                                <option value="critical" {{ old('priority', $message->priority ?? '') === 'critical' ? 'selected' : '' }}
                                        class="text-red-600">Critical</option>
                            </select>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                            Message Body <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-bankos-border dark:border-bankos-dark-border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-bankos-primary focus-within:border-bankos-primary transition-shadow">
                            {{-- Toolbar --}}
                            <div class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border px-3 py-2 flex items-center gap-1">
                                <button type="button" onclick="document.execCommand('bold')"
                                        class="p-1.5 rounded hover:bg-bankos-border text-bankos-text-sec hover:text-bankos-text transition-colors" title="Bold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>
                                </button>
                                <button type="button" onclick="document.execCommand('italic')"
                                        class="p-1.5 rounded hover:bg-bankos-border text-bankos-text-sec hover:text-bankos-text transition-colors" title="Italic">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"></line><line x1="14" y1="20" x2="5" y2="20"></line><line x1="15" y1="4" x2="9" y2="20"></line></svg>
                                </button>
                                <button type="button" onclick="document.execCommand('underline')"
                                        class="p-1.5 rounded hover:bg-bankos-border text-bankos-text-sec hover:text-bankos-text transition-colors" title="Underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path><line x1="4" y1="21" x2="20" y2="21"></line></svg>
                                </button>
                                <span class="w-px h-4 bg-bankos-border mx-1"></span>
                                <button type="button" onclick="document.execCommand('insertUnorderedList')"
                                        class="p-1.5 rounded hover:bg-bankos-border text-bankos-text-sec hover:text-bankos-text transition-colors" title="Bullet List">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                </button>
                                <button type="button" onclick="document.execCommand('insertOrderedList')"
                                        class="p-1.5 rounded hover:bg-bankos-border text-bankos-text-sec hover:text-bankos-text transition-colors" title="Numbered List">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4"></path><path d="M4 10h2"></path><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path></svg>
                                </button>
                            </div>
                            {{-- Editable div --}}
                            <div id="body-editor"
                                 contenteditable="true"
                                 @input="bodyContent = $event.target.innerHTML"
                                 class="min-h-[200px] p-4 text-sm text-bankos-text dark:text-bankos-dark-text bg-white dark:bg-bankos-dark-surface focus:outline-none prose prose-sm max-w-none"
                                 x-init="$el.innerHTML = bodyContent">
                            </div>
                        </div>
                        <textarea name="body" class="hidden" x-bind:value="bodyContent" required></textarea>
                        @error('body')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Attachments --}}
                    <div>
                        <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-2">Attachments</label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-bankos-primary border border-dashed border-bankos-primary/40 rounded-lg px-4 py-3 hover:bg-bankos-light/50 transition-colors w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Attach Files (max 10 MB each)
                            <input type="file" name="attachments[]" multiple class="hidden" @change="addFiles($event)">
                        </label>

                        {{-- Existing attachments (edit mode) --}}
                        @if ($isEdit && $message->attachments->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                <p class="text-xs font-medium text-bankos-text-sec">Existing attachments:</p>
                                @foreach ($message->attachments as $att)
                                <div class="flex items-center gap-2 text-xs text-bankos-text-sec py-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                    {{ $att->file_name }}
                                    <span class="text-bankos-muted">({{ number_format($att->file_size_kb) }} KB)</span>
                                </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Newly selected files --}}
                        <div class="mt-2 space-y-1" x-show="attachments.length > 0">
                            <template x-for="(att, i) in attachments" :key="i">
                                <div class="flex items-center gap-2 text-xs bg-gray-50 dark:bg-bankos-dark-bg/40 rounded px-3 py-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <span x-text="att.name" class="text-bankos-text dark:text-bankos-dark-text"></span>
                                    <span x-text="att.size" class="text-bankos-muted ml-1"></span>
                                    <button type="button" @click="removeAttachment(i)"
                                            class="ml-auto text-red-400 hover:text-red-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT: Scope & Options (1/3) ===== --}}
            <div class="space-y-5">
                <div class="card p-5 lg:sticky lg:top-24">
                    <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Delivery Options</h3>

                    {{-- Scope Type --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-bankos-text-sec mb-2 uppercase tracking-wide">Send To</label>
                        <div class="space-y-2">
                            @foreach (['all' => 'All Staff', 'branch' => 'Branch', 'department' => 'Department', 'team' => 'Team', 'role' => 'Role', 'individual' => 'Individual User'] as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="radio" name="scope_type" value="{{ $val }}"
                                       x-model="scopeType"
                                       {{ old('scope_type', $message->scope_type ?? 'all') === $val ? 'checked' : '' }}
                                       class="h-4 w-4 text-bankos-primary border-bankos-border">
                                <span class="text-bankos-text dark:text-bankos-dark-text">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Conditional scope_id selectors --}}
                    <div class="mb-4">
                        {{-- Branch --}}
                        <div x-show="scopeType === 'branch'" x-cloak>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Select Branch</label>
                            <select name="scope_id" class="form-select w-full text-sm">
                                <option value="">-- All Branches --</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('scope_id', $message->scope_id ?? '') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Department --}}
                        <div x-show="scopeType === 'department'" x-cloak>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Select Department</label>
                            <select name="scope_id" class="form-select w-full text-sm">
                                <option value="">-- Select Department --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('scope_id', $message->scope_id ?? '') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Team --}}
                        <div x-show="scopeType === 'team'" x-cloak>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Select Team</label>
                            <select name="scope_id" class="form-select w-full text-sm">
                                <option value="">-- Select Team --</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}"
                                        {{ old('scope_id', $message->scope_id ?? '') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Role --}}
                        <div x-show="scopeType === 'role'" x-cloak>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Select Role</label>
                            <select name="scope_id" class="form-select w-full text-sm">
                                <option value="">-- Select Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('scope_id', $message->scope_id ?? '') == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Individual --}}
                        <div x-show="scopeType === 'individual'" x-cloak>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Select User</label>
                            <select name="scope_id" class="form-select w-full text-sm">
                                <option value="">-- Select User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('scope_id', $message->scope_id ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="border-bankos-border dark:border-bankos-dark-border my-4">

                    {{-- Requires Acknowledgement Toggle --}}
                    <div class="mb-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">Requires Acknowledgement</p>
                                <p class="text-xs text-bankos-muted mt-0.5">Recipients must confirm they read this message</p>
                            </div>
                            <div class="relative ml-3 shrink-0">
                                <input type="checkbox" name="requires_ack" value="1"
                                       x-model="requiresAck"
                                       {{ old('requires_ack', $message->requires_ack ?? false) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div @click="requiresAck = !requiresAck"
                                     :class="requiresAck ? 'bg-bankos-primary' : 'bg-gray-200 dark:bg-gray-700'"
                                     class="w-10 h-5 rounded-full cursor-pointer transition-colors relative">
                                    <div :class="requiresAck ? 'translate-x-5' : 'translate-x-0'"
                                         class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform"></div>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- ACK Deadline --}}
                    <div x-show="requiresAck" x-cloak class="mb-4">
                        <label for="ack_deadline" class="block text-xs font-medium text-bankos-text-sec mb-1">Acknowledgement Deadline</label>
                        <input type="date" id="ack_deadline" name="ack_deadline"
                               value="{{ old('ack_deadline', isset($message) ? optional($message->ack_deadline)->format('Y-m-d') : '') }}"
                               class="form-input w-full text-sm">
                    </div>

                    <hr class="border-bankos-border dark:border-bankos-dark-border my-4">

                    {{-- Action Buttons --}}
                    <div class="space-y-2">
                        <button type="submit" @click="publishNow = false"
                                class="btn btn-secondary w-full justify-center flex items-center gap-2 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Save as Draft
                        </button>
                        <button type="submit" @click="publishNow = true"
                                class="btn btn-primary w-full justify-center flex items-center gap-2 text-sm"
                                onclick="this.form.querySelector('[name=publish]').value='1'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            Save &amp; Publish
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
