@extends('layouts.app')

@section('title', 'My Certifications & Documents')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Certifications & Documents</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your professional certifications and personal documents on file.</p>
    </div>

    @include('hr.org._tabs')

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Certifications Section --}}
        <div>
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Professional Certifications ({{ $certifications->count() }})</h2>
                </div>
                @if ($certifications->isEmpty())
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400">No certifications on record.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($certifications as $cert)
                            @php
                                $isExpired = $cert->expiry_date && \Carbon\Carbon::parse($cert->expiry_date)->isPast();
                                $expiresSoon = !$isExpired && $cert->expiry_date
                                    && \Carbon\Carbon::parse($cert->expiry_date)->diffInDays(now()) <= 60
                                    && \Carbon\Carbon::parse($cert->expiry_date)->isFuture();
                            @endphp
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $cert->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $cert->issuing_body }}</p>
                                        @if ($cert->cert_number)
                                            <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $cert->cert_number }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4 shrink-0">
                                        @if ($cert->is_verified)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Verified</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Unverified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                                    <span>Issued: {{ \Carbon\Carbon::parse($cert->issue_date)->format('d M Y') }}</span>
                                    @if ($cert->expiry_date)
                                        <span class="{{ $isExpired ? 'text-red-500 font-medium' : ($expiresSoon ? 'text-yellow-600 font-medium' : '') }}">
                                            Expires: {{ \Carbon\Carbon::parse($cert->expiry_date)->format('d M Y') }}
                                            @if ($isExpired) (Expired) @elseif ($expiresSoon) (Expires soon) @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Add Certification Form --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Add Certification</h3>
                <form action="{{ route('hr.certifications.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Certification Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Issuing Body <span class="text-red-500">*</span></label>
                            <input type="text" name="issuing_body" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Certificate Number</label>
                            <input type="text" name="cert_number" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            {{-- spacer --}}
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Issue Date <span class="text-red-500">*</span></label>
                            <input type="date" name="issue_date" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Certification</button>
                </form>
            </div>
        </div>

        {{-- Documents Section --}}
        <div>
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">Documents on File ({{ $documents->count() }})</h2>
                </div>
                @if ($documents->isEmpty())
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400">No documents on record.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($documents as $doc)
                            @php
                                $docTypeLabel = match($doc->document_type) {
                                    'id_card'                  => 'National ID Card',
                                    'passport'                 => 'International Passport',
                                    'drivers_license'          => "Driver's License",
                                    'academic_certificate'     => 'Academic Certificate',
                                    'professional_certificate' => 'Professional Certificate',
                                    'offer_letter'             => 'Offer Letter',
                                    'appointment_letter'       => 'Appointment Letter',
                                    default                    => ucwords(str_replace('_', ' ', $doc->document_type)),
                                };
                            @endphp
                            <div class="px-6 py-4 flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $docTypeLabel }}</p>
                                    @if ($doc->document_number)
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $doc->document_number }}</p>
                                    @endif
                                    @if ($doc->file_url)
                                        <a href="{{ $doc->file_url }}" target="_blank" rel="noopener"
                                           class="text-xs text-blue-600 hover:text-blue-800 mt-0.5 block">View Document</a>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">Added {{ $doc->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="ml-4 shrink-0">
                                    @if ($doc->is_verified)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Verified</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Pending Verification</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Add Document Form --}}
            <div class="card bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Add Document</h3>
                <form action="{{ route('hr.documents.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                        <select name="document_type" required class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select type...</option>
                            <option value="id_card">National ID Card</option>
                            <option value="passport">International Passport</option>
                            <option value="drivers_license">Driver's License</option>
                            <option value="academic_certificate">Academic Certificate</option>
                            <option value="professional_certificate">Professional Certificate</option>
                            <option value="offer_letter">Offer Letter</option>
                            <option value="appointment_letter">Appointment Letter</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Document Number</label>
                        <input type="text" name="document_number" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. A12345678">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">File URL</label>
                        <input type="url" name="file_url" class="form-input w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://...">
                    </div>
                    <button type="submit" class="btn-primary w-full py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Add Document</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
