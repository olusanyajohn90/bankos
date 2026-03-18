@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showNew: false }">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Knowledge Base</h1>
            <p class="text-sm text-gray-500 mt-0.5">Internal articles and guides for support agents.</p>
        </div>
        <button @click="showNew = !showNew" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">+ New Article</button>
    </div>

    @if(session('success'))<div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- New Article Form --}}
    <div x-show="showNew" x-transition class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">New Article</h2>
        <form action="{{ route('support.kb.store') }}" method="POST" class="space-y-4">
            @csrf
            <div><label class="block text-xs text-gray-500 mb-1">Title *</label>
                <input type="text" name="title" required class="form-input w-full text-sm" placeholder="Article headline"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Category</label>
                <input type="text" name="category" class="form-input w-full text-sm" placeholder="e.g. Account Issues, Cards, Loans"></div>
            <div><label class="block text-xs text-gray-500 mb-1">Content *</label>
                <textarea name="body" rows="10" required class="form-input w-full text-sm font-mono resize-none" placeholder="Write the article content here. Markdown supported."></textarea></div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="publish" value="1" class="rounded border-gray-300"> Publish immediately
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save Article</button>
                <button type="button" @click="showNew = false" class="btn text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles…" class="form-input flex-1 text-sm">
        <select name="status" class="form-input text-sm">
            <option value="">All</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <button type="submit" class="btn text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Search</button>
    </form>

    {{-- Articles --}}
    <div class="space-y-3">
        @forelse($articles as $article)
        <div class="card p-4" x-data="{ expanded: false }">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $article->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst($article->status) }}
                        </span>
                        @if($article->category)<span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">{{ $article->category }}</span>@endif
                    </div>
                    <button @click="expanded = !expanded" class="text-left font-semibold text-gray-900 hover:text-blue-700 text-sm">{{ $article->title }}</button>
                    <p class="text-xs text-gray-400 mt-0.5">By {{ $article->createdBy?->name }} · {{ $article->created_at->format('d M Y') }} · {{ number_format($article->view_count) }} views</p>
                </div>
                <div class="flex items-center gap-2 flex-none">
                    @if($article->status === 'draft')
                    <form action="{{ route('support.kb.publish', $article) }}" method="POST">
                        @csrf
                        <button class="text-xs text-green-600 hover:text-green-800 font-medium">Publish</button>
                    </form>
                    @endif
                    <form action="{{ route('support.kb.destroy', $article) }}" method="POST" onsubmit="return confirm('Delete article?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>
            <div x-show="expanded" x-transition class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $article->body }}</p>
            </div>
        </div>
        @empty
            <div class="card p-12 text-center text-gray-400">No articles yet. Create your first knowledge base article.</div>
        @endforelse
    </div>

    @if($articles->hasPages())
        <div>{{ $articles->links() }}</div>
    @endif

</div>
@endsection
