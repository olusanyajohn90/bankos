@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    selectedFile: null,
    handleFile(e) {
        const f = e.target.files[0];
        if (f) {
            this.selectedFile = {
                name: f.name,
                size: (f.size / 1024).toFixed(1) + ' KB'
            };
        }
    }
}">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-bankos-text-sec mb-4">
        <a href="{{ route('documents.index', ['documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}"
           class="hover:text-bankos-primary flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Documents
        </a>
        <span>/</span>
        <span class="text-bankos-text font-medium">Upload Document</span>
    </div>

    <div class="card p-6">
        <h2 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text mb-1">Upload Document</h2>
        <p class="text-sm text-bankos-text-sec mb-6">Add a new compliance or operational document.</p>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
                <p class="font-semibold mb-1">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
            <input type="hidden" name="documentable_id" value="{{ $documentableId }}">

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Document Title <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       placeholder="e.g. National ID Card — John Doe"
                       class="form-input w-full @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Document Type --}}
            <div>
                <label for="document_type" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Document Type <span class="text-red-500">*</span>
                </label>
                @if ($checklistItems->isNotEmpty())
                    <select id="document_type" name="document_type" required class="form-select w-full @error('document_type') border-red-400 @enderror">
                        <option value="">-- Select document type --</option>
                        @foreach ($checklistItems as $item)
                            <option value="{{ $item->document_type }}" {{ old('document_type') === $item->document_type ? 'selected' : '' }}>
                                {{ $item->document_label }}
                                @if ($item->is_required) (Required) @endif
                            </option>
                        @endforeach
                        <option value="other" {{ old('document_type') === 'other' ? 'selected' : '' }}>Other / Unlisted</option>
                    </select>
                @else
                    <input type="text" id="document_type" name="document_type" value="{{ old('document_type') }}" required
                           placeholder="e.g. national_id, utility_bill, cac_certificate"
                           class="form-input w-full @error('document_type') border-red-400 @enderror">
                @endif
                @error('document_type')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Document Category --}}
            <div>
                <label for="document_category" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="document_category" name="document_category" required class="form-select w-full @error('document_category') border-red-400 @enderror">
                    <option value="">-- Select category --</option>
                    @foreach (['identity', 'financial', 'legal', 'compliance', 'operational', 'other'] as $cat)
                        <option value="{{ $cat }}" {{ old('document_category') === $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
                @error('document_category')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Description
                </label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Optional notes about this document..."
                          class="form-input w-full resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Expiry Date --}}
            <div>
                <label for="expiry_date" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Expiry Date
                </label>
                <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}"
                       min="{{ now()->addDay()->format('Y-m-d') }}"
                       class="form-input w-full @error('expiry_date') border-red-400 @enderror">
                <p class="mt-1 text-xs text-bankos-muted">Leave blank if this document has no expiry date.</p>
                @error('expiry_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Alert Days Before --}}
            <div>
                <label for="alert_days_before" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Alert Days Before Expiry
                </label>
                <input type="number" id="alert_days_before" name="alert_days_before"
                       value="{{ old('alert_days_before', 30) }}" min="1" max="365"
                       class="form-input w-40">
                <p class="mt-1 text-xs text-bankos-muted">Number of days before expiry to trigger an alert. Default: 30.</p>
            </div>

            {{-- Is Required --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" id="is_required" name="is_required" value="1"
                       {{ old('is_required') ? 'checked' : '' }}
                       class="h-4 w-4 text-bankos-primary border-bankos-border rounded">
                <label for="is_required" class="text-sm text-bankos-text dark:text-bankos-dark-text cursor-pointer">
                    This document is required for compliance
                </label>
            </div>

            {{-- File Upload with Alpine.js preview --}}
            <div>
                <label for="file" class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    File <span class="text-red-500">*</span>
                </label>
                <div class="relative border-2 border-dashed border-bankos-border dark:border-bankos-dark-border rounded-lg p-6 text-center hover:border-bankos-primary transition-colors @error('file') border-red-400 @enderror">
                    <input type="file" id="file" name="file" required
                           @change="handleFile($event)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div x-show="!selectedFile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-bankos-muted mb-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <p class="text-sm text-bankos-text-sec">Click to browse or drag and drop a file</p>
                        <p class="text-xs text-bankos-muted mt-1">PDF, JPG, PNG, DOCX, XLSX &mdash; Max 20 MB</p>
                    </div>
                    <div x-show="selectedFile" class="flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text" x-text="selectedFile?.name"></p>
                            <p class="text-xs text-bankos-muted" x-text="selectedFile?.size"></p>
                        </div>
                    </div>
                </div>
                @error('file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('documents.index', ['documentable_type' => $documentableType, 'documentable_id' => $documentableId]) }}"
                   class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
