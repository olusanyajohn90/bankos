@extends('layouts.app')

@section('title', 'CBN Document Checklist Configuration')

@section('content')
@php
    $entityTypes = ['customer', 'loan', 'account', 'staff_profile', 'branch'];
    $entityLabels = [
        'customer'     => 'Customer / KYC',
        'loan'         => 'Loan / Credit',
        'account'      => 'Account',
        'staff_profile'=> 'Staff / HR',
        'branch'       => 'Branch',
    ];
@endphp

<div class="max-w-7xl mx-auto"
     x-data="{ activeTab: '{{ $grouped->keys()->first() ?? 'customer' }}', editingItem: null, editForm: {} }">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">CBN Document Checklist</h1>
            <p class="text-sm text-bankos-text-sec mt-1">Configure required and optional documents per entity type for compliance tracking.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Tab Bar --}}
    <div class="border-b border-bankos-border dark:border-bankos-dark-border mb-6 flex overflow-x-auto hide-scrollbar">
        @foreach ($entityTypes as $et)
        <button @click="activeTab = '{{ $et }}'"
                :class="activeTab === '{{ $et }}'
                    ? 'border-bankos-primary text-bankos-primary'
                    : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                class="py-3 px-5 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors flex items-center gap-2">
            {{ $entityLabels[$et] ?? ucfirst($et) }}
            @if (isset($grouped[$et]) && $grouped[$et]->isNotEmpty())
                <span class="text-xs bg-bankos-border dark:bg-bankos-dark-border text-bankos-text-sec rounded-full px-1.5 py-0.5">
                    {{ $grouped[$et]->count() }}
                </span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Tab Panels --}}
    @foreach ($entityTypes as $et)
    <div x-show="activeTab === '{{ $et }}'" x-cloak>

        {{-- Items Table --}}
        <div class="card p-0 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                <h3 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">
                    {{ $entityLabels[$et] ?? ucfirst($et) }} Documents
                </h3>
                <span class="text-xs text-bankos-muted">
                    {{ $grouped[$et]?->count() ?? 0 }} items configured
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-4 py-3 font-semibold">Label</th>
                            <th class="px-4 py-3 font-semibold">Type Key</th>
                            <th class="px-4 py-3 font-semibold">Required</th>
                            <th class="px-4 py-3 font-semibold">Applies To</th>
                            <th class="px-4 py-3 font-semibold text-center">Sort</th>
                            <th class="px-4 py-3 font-semibold text-center">Active</th>
                            <th class="px-4 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse ($grouped[$et] ?? [] as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                                {{ $item->document_label }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-bankos-muted">{{ $item->document_type }}</td>
                            <td class="px-4 py-3">
                                @if ($item->is_required)
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Required</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Optional</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-text-sec">
                                {{ $item->applies_to ?? 'All' }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-bankos-muted font-mono">{{ $item->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->is_active)
                                    <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                                @else
                                    <span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                            @click="editingItem = {{ $item->id }}; editForm = {
                                                entity_type: '{{ $item->entity_type }}',
                                                document_type: '{{ $item->document_type }}',
                                                document_label: '{{ addslashes($item->document_label) }}',
                                                is_required: {{ $item->is_required ? 'true' : 'false' }},
                                                applies_to: '{{ $item->applies_to }}',
                                                sort_order: {{ $item->sort_order }},
                                                is_active: {{ $item->is_active ? 'true' : 'false' }}
                                            }"
                                            class="text-xs text-bankos-primary hover:underline font-medium">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('documents.checklists.destroy', $item) }}"
                                          onsubmit="return confirm('Delete this checklist item?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-bankos-muted text-sm">
                                No checklist items configured for {{ $entityLabels[$et] ?? ucfirst($et) }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Item Form --}}
        <div class="card p-6">
            <h4 class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Checklist Item for {{ $entityLabels[$et] ?? ucfirst($et) }}
            </h4>
            <form method="POST" action="{{ route('documents.checklists.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="entity_type" value="{{ $et }}">

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Document Label <span class="text-red-500">*</span></label>
                    <input type="text" name="document_label" required placeholder="e.g. National ID Card"
                           class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Document Type Key <span class="text-red-500">*</span></label>
                    <input type="text" name="document_type" required placeholder="e.g. national_id"
                           class="form-input w-full text-sm font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Applies To</label>
                    <input type="text" name="applies_to" placeholder="e.g. individual, corporate (blank = all)"
                           class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0" class="form-input w-full text-sm">
                </div>
                <div class="flex items-center gap-4 pt-5">
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" name="is_required" value="1" checked
                               class="h-4 w-4 text-bankos-primary border-bankos-border rounded">
                        <span class="text-bankos-text-sec">Required</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="h-4 w-4 text-bankos-primary border-bankos-border rounded">
                        <span class="text-bankos-text-sec">Active</span>
                    </label>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary text-sm w-full sm:w-auto">Add Item</button>
                </div>
            </form>
        </div>

    </div>
    @endforeach

    {{-- Edit Modal --}}
    <div x-show="editingItem !== null"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-transition>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-lg mx-4 p-6"
             @click.outside="editingItem = null">
            <h3 class="text-base font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Edit Checklist Item</h3>
            <template x-for="item in {{ json_encode($checklists->values()) }}" :key="item.id">
                <form x-show="editingItem === item.id"
                      method="POST"
                      :action="`/documents/checklists/${item.id}`"
                      class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-medium text-bankos-text-sec mb-1">Entity Type</label>
                        <select name="entity_type" x-model="editForm.entity_type" class="form-select w-full text-sm">
                            @foreach ($entityTypes as $et)
                                <option value="{{ $et }}">{{ $entityLabels[$et] ?? ucfirst($et) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Document Label</label>
                            <input type="text" name="document_label" x-model="editForm.document_label" required class="form-input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Type Key</label>
                            <input type="text" name="document_type" x-model="editForm.document_type" required class="form-input w-full text-sm font-mono">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Applies To</label>
                            <input type="text" name="applies_to" x-model="editForm.applies_to" class="form-input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Sort Order</label>
                            <input type="number" name="sort_order" x-model="editForm.sort_order" min="0" class="form-input w-full text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="is_required" value="1" :checked="editForm.is_required"
                                   @change="editForm.is_required = $event.target.checked"
                                   class="h-4 w-4 text-bankos-primary border-bankos-border rounded">
                            <span class="text-bankos-text-sec">Required</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" name="is_active" value="1" :checked="editForm.is_active"
                                   @change="editForm.is_active = $event.target.checked"
                                   class="h-4 w-4 text-bankos-primary border-bankos-border rounded">
                            <span class="text-bankos-text-sec">Active</span>
                        </label>
                    </div>
                    <div class="flex gap-2 justify-end pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                        <button type="button" @click="editingItem = null" class="btn btn-secondary text-sm">Cancel</button>
                        <button type="submit" class="btn btn-primary text-sm">Save Changes</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
