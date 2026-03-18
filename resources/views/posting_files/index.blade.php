<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Posting Files</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Bulk upload and post transaction files</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('posting-files.template') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Download Template
                </a>
                <a href="{{ route('posting-files.create') }}" class="btn btn-primary flex items-center gap-2 shadow-md hover:-translate-y-0.5 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Upload File
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Reference</th>
                        <th class="px-6 py-4 font-semibold">File Name</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Records</th>
                        <th class="px-6 py-4 font-semibold">Total Amount</th>
                        <th class="px-6 py-4 font-semibold">Uploaded By</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($files as $file)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4 font-mono text-bankos-primary text-xs">{{ $file->reference }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-bankos-text truncate max-w-[200px]">{{ $file->file_name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="capitalize text-bankos-text">{{ $file->type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClass = match($file->status) {
                                    'posted'    => 'badge-active',
                                    'validated' => 'bg-blue-100 text-blue-700',
                                    'failed'    => 'bg-red-100 text-red-700',
                                    'posting'   => 'bg-amber-100 text-amber-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-[10px] uppercase tracking-wider">{{ $file->status }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-bankos-text">
                                <span class="text-green-600 font-semibold">{{ $file->valid_records }}</span>
                                @if($file->invalid_records > 0)
                                    / <span class="text-red-500">{{ $file->invalid_records }} err</span>
                                @endif
                                <span class="text-bankos-text-sec text-xs ml-1">of {{ $file->total_records }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-bankos-text">₦{{ number_format($file->total_amount, 0) }}</td>
                        <td class="px-6 py-4 text-bankos-text-sec text-xs">{{ $file->uploadedBy?->first_name }}</td>
                        <td class="px-6 py-4 text-bankos-text-sec text-xs">{{ $file->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('posting-files.show', $file) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <p class="font-medium text-bankos-text">No posting files uploaded yet.</p>
                            <a href="{{ route('posting-files.create') }}" class="btn btn-primary shadow-sm mt-3 inline-block">Upload First File</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($files->hasPages())
        <div class="p-4 border-t border-bankos-border bg-gray-50/30">{{ $files->links() }}</div>
        @endif
    </div>
</x-app-layout>
