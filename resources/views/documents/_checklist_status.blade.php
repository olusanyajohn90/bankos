{{--
    Partial: documents/_checklist_status.blade.php
    Accepts: $checklistStatus — array of items with keys:
      label, status ('approved'|'pending'|'missing'), is_expired (bool), is_required (bool)
--}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-1.5">
    @foreach ($checklistStatus as $item)
    @php
        if ($item['status'] === 'approved' && ! $item['is_expired']) {
            $classes = 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800';
            $icon    = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        } elseif ($item['status'] === 'pending') {
            $classes = 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800';
            $icon    = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
        } elseif ($item['is_expired']) {
            $classes = 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 border border-orange-200 dark:border-orange-800';
            $icon    = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
        } else {
            $classes = 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800';
            $icon    = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        }
    @endphp
    <div class="flex items-center gap-1.5 text-xs px-2 py-1.5 rounded {{ $classes }}">
        {!! $icon !!}
        <span class="truncate font-medium" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
        @if (! empty($item['is_required']))
            <span class="ml-auto shrink-0 font-semibold opacity-70">*</span>
        @endif
    </div>
    @endforeach
</div>
@if (collect($checklistStatus)->where('status', 'missing')->isNotEmpty() || collect($checklistStatus)->where('is_expired', true)->isNotEmpty())
    <div class="mt-2 flex items-center gap-4 text-xs text-bankos-text-sec">
        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-green-500"></span> Approved</span>
        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span> Pending Review</span>
        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-orange-500"></span> Expired</span>
        <span class="flex items-center gap-1"><span class="inline-block w-2 h-2 rounded-full bg-red-500"></span> Missing</span>
        <span class="ml-2 text-bankos-muted">* = required</span>
    </div>
@endif
