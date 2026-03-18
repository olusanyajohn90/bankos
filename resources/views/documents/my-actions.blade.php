@extends('layouts.app')
@section('title', 'My Document Actions')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <a href="{{ route('documents.index') }}" class="text-xs text-gray-400 hover:text-gray-600">← Documents</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">My Pending Actions</h1>
        <p class="text-sm text-gray-500 mt-0.5">Documents awaiting your review, approval, or signature.</p>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Document</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Workflow</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Step / Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Deadline</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pending as $action)
                <tr class="hover:bg-gray-50 {{ $action->isOverdue() ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('documents.show', $action->instance->document_id) }}" class="font-medium text-blue-700 hover:text-blue-900">
                            {{ $action->instance->document->title ?? 'Unknown' }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $action->instance->workflow->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-gray-800">{{ $action->step->name ?? '—' }}</span>
                        <span class="ml-2 px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600">{{ ucfirst($action->step->action_type ?? '') }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs {{ $action->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                        {{ $action->deadline_at ? $action->deadline_at->diffForHumans() : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('documents.show', $action->instance->document_id) }}" class="btn text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg">Review & Act</a>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No pending actions. You're all caught up!</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($pending->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $pending->links() }}</div>
        @endif
    </div>

</div>
@endsection
