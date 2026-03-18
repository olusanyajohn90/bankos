@extends('layouts.app')

@section('title', 'Upload Bureau Report PDF')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Upload Bureau Report</h1>
        <p class="text-sm text-gray-500 mt-1">Upload a PDF credit report from FirstCentral, CRC/CreditRegistry, or XDS. The bureau will be auto-detected.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('bureau.upload.process') }}" method="POST" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf

        {{-- PDF File --}}
        <div>
            <label class="form-label">Bureau Report PDF <span class="text-red-500">*</span></label>
            <div class="mt-1 flex items-center justify-center w-full"
                 x-data="{ dragging: false, fileName: '' }"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="dragging = false; fileName = $event.dataTransfer.files[0]?.name; $refs.fileInput.files = $event.dataTransfer.files">
                <label :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
                       class="w-full flex flex-col items-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg cursor-pointer transition-colors">
                    <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm text-gray-600" x-text="fileName || 'Click to select or drag & drop PDF'"></span>
                    <span class="text-xs text-gray-400 mt-1">PDF only · max 10 MB</span>
                    <input x-ref="fileInput" type="file" name="pdf_file" accept=".pdf,application/pdf" class="sr-only" required
                           @change="fileName = $event.target.files[0]?.name">
                </label>
            </div>
        </div>

        {{-- Supported bureaus --}}
        <div class="flex gap-3 flex-wrap">
            @foreach([['FirstCentral','bg-blue-100 text-blue-800','Detailed Credit Profile'],['CRC / CreditRegistry','bg-green-100 text-green-800','Full Credit Report'],['XDS / CreditInfo','bg-purple-100 text-purple-800','Credit Report']] as [$name,$cls,$type])
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                {{ $name }} &mdash; {{ $type }}
            </span>
            @endforeach
        </div>

        <hr class="border-gray-100">

        {{-- Optional: link to customer --}}
        <div>
            <label class="form-label">Link to Customer <span class="text-gray-400 font-normal">(optional)</span></label>
            <select name="customer_id" class="form-input w-full">
                <option value="">— Select customer to link —</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                    {{ $c->first_name }} {{ $c->last_name }}
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Linking enables cross-bureau comparison and appears on the customer's profile.</p>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('bureau.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload &amp; Analyse
            </button>
        </div>
    </form>

    <div class="text-xs text-gray-400 bg-gray-50 rounded-lg p-4 space-y-1">
        <p class="font-medium text-gray-500">How it works</p>
        <p>1. Upload any PDF credit report from a Nigerian credit bureau.</p>
        <p>2. The system extracts text, auto-detects the bureau format, and parses the report.</p>
        <p>3. You are redirected to the <strong>Analytics Dashboard</strong> showing risk assessment, account breakdown, and derogatory history.</p>
        <p>4. If multiple reports are uploaded for the same customer, the system shows a <strong>cross-bureau comparison</strong>.</p>
    </div>
</div>
@endsection
