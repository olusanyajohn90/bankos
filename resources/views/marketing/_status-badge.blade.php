@php
$classes = match($status) {
    'draft'     => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
    'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    'sending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'sent'      => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'paused'    => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    default     => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
};
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $classes }}">
    {{ ucfirst($status) }}
</span>
