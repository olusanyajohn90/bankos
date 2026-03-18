<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('kyc-review.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text">KYC Upgrade · {{ $req->customer_name }}</h2>
                <p class="text-sm text-bankos-text-sec mt-0.5">Tier {{ $req->current_tier }} → Tier {{ $req->target_tier }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer info --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Customer Information</h3>
                <div class="grid grid-cols-2 gap-y-3 gap-x-6">
                    <div><p class="text-xs text-gray-400 mb-1">Full Name</p><p class="text-sm font-semibold">{{ $req->customer_name }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Phone</p><p class="text-sm font-semibold">{{ $req->customer_phone }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Email</p><p class="text-sm font-semibold">{{ $req->customer_email ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Current KYC Tier</p><p class="text-sm font-bold text-gray-800">Tier {{ $req->current_db_tier }}</p></div>
                    @if($req->bvn)<div><p class="text-xs text-gray-400 mb-1">BVN</p><p class="text-sm font-mono font-semibold">{{ $req->bvn }}</p></div>@endif
                    @if($req->nin)<div><p class="text-xs text-gray-400 mb-1">NIN</p><p class="text-sm font-mono font-semibold">{{ $req->nin }}</p></div>@endif
                </div>
            </div>

            {{-- Documents --}}
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Submitted Documents</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @php
                    $docs = [
                        ['label' => 'ID Document (' . ucfirst(str_replace('_',' ',$req->id_type ?? 'id')) . ')', 'path' => $req->id_document_path],
                        ['label' => 'Selfie / Live Photo',  'path' => $req->selfie_path],
                        ['label' => 'Address Proof',        'path' => $req->address_proof_path],
                    ];
                    @endphp
                    @foreach($docs as $doc)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-50 px-3 py-2 border-b border-gray-200">
                            <p class="text-xs font-semibold text-gray-600">{{ $doc['label'] }}</p>
                        </div>
                        <div class="p-3">
                            @if($doc['path'])
                            @php $ext = pathinfo($doc['path'], PATHINFO_EXTENSION); @endphp
                            @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                            <img src="{{ asset('storage/' . $doc['path']) }}" class="w-full h-40 object-cover rounded-lg" alt="{{ $doc['label'] }}">
                            @else
                            <a href="{{ asset('storage/' . $doc['path']) }}" target="_blank"
                               class="flex items-center gap-2 text-bankos-primary text-sm font-semibold hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                View {{ strtoupper($ext) }} Document
                            </a>
                            @endif
                            @else
                            <p class="text-xs text-gray-400 italic">Not submitted</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Review form --}}
            @if($req->status === 'pending')
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Review Decision</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('kyc-review.approve', $req->id) }}">
                        @csrf
                        <textarea name="reviewer_notes" rows="3" placeholder="Approval notes (optional)..." class="form-input w-full text-sm mb-3"></textarea>
                        <button type="submit" class="btn btn-primary w-full" onclick="return confirm('Approve and upgrade this customer to Tier {{ $req->target_tier }}?')">
                            ✓ Approve & Upgrade to Tier {{ $req->target_tier }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('kyc-review.reject', $req->id) }}">
                        @csrf
                        <textarea name="reviewer_notes" rows="3" placeholder="Rejection reason (required)..." class="form-input w-full text-sm mb-3" required></textarea>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white w-full" onclick="return confirm('Reject this KYC upgrade request?')">
                            ✗ Reject Request
                        </button>
                    </form>
                </div>
            </div>
            @elseif($req->reviewer_notes)
            <div class="card">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Reviewer Notes</h3>
                <p class="text-sm text-gray-700">{{ $req->reviewer_notes }}</p>
                @if($req->reviewed_at)<p class="text-xs text-gray-400 mt-2">Reviewed {{ \Carbon\Carbon::parse($req->reviewed_at)->format('d M Y, H:i') }}</p>@endif
            </div>
            @endif
        </div>

        {{-- Status sidebar --}}
        <div>
            @php $sc = ['pending'=>['bg-amber-100','text-amber-700'],'approved'=>['bg-green-100','text-green-700'],'rejected'=>['bg-red-100','text-red-700']][$req->status]??['bg-gray-100','text-gray-500']; @endphp
            <div class="card text-center">
                <p class="text-xs text-gray-400 mb-2">Request Status</p>
                <span class="inline-block text-sm font-bold px-4 py-2 rounded-full {{ $sc[0] }} {{ $sc[1] }} mb-3">{{ strtoupper($req->status) }}</span>
                <div class="grid grid-cols-2 gap-2 text-center mt-4">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">From Tier</p>
                        <p class="text-2xl font-black text-gray-600">{{ $req->current_tier }}</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400 mb-1">To Tier</p>
                        <p class="text-2xl font-black text-blue-600">{{ $req->target_tier }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-4">Submitted {{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
