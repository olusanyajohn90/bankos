@php
$steps = [
    1 => 'Institution',
    2 => 'Branding',
    3 => 'Admin User',
    4 => 'Plan',
    5 => 'Review',
];
@endphp

<div class="flex items-center gap-1">
    @foreach($steps as $num => $label)
        @php
            $isComplete = $num < $current;
            $isCurrent  = $num === $current;
        @endphp
        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-colors
                    {{ $isComplete ? 'bg-blue-600 border-blue-600 text-white' : ($isCurrent ? 'border-blue-600 bg-white text-blue-600' : 'border-gray-300 bg-white text-gray-400') }}">
                    @if($isComplete)
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="text-xs mt-1 {{ $isCurrent ? 'text-blue-600 font-semibold' : ($isComplete ? 'text-blue-500' : 'text-gray-400') }}">
                    {{ $label }}
                </span>
            </div>
            @if(!$loop->last)
            <div class="flex-1 h-0.5 mx-2 mt-[-12px] {{ $isComplete ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
            @endif
        </div>
    @endforeach
</div>
