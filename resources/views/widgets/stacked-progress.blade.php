@php
    $mapClasses = function($c) {
        if(str_contains($c, 'success')) return 'bg-success-500';
        if(str_contains($c, 'warning')) return 'bg-warning-500';
        if(str_contains($c, 'danger')) return 'bg-danger-500';
        if(str_contains($c, 'info')) return 'bg-cyan-500';
        return 'bg-primary-600';
    };
@endphp
<div class="w-full bg-secondary-200 rounded-full h-4 flex overflow-hidden">
    <div class="{{ $mapClasses($class1 ?? '') }} h-full flex items-center justify-center text-xs text-white font-medium" style="width: {{ $value1 }}%">
        @if(($value1 ?? 0) > 15){{ $value1 }}% @endif
    </div>
    <div class="{{ $mapClasses($class2 ?? '') }} h-full flex items-center justify-center text-xs text-white font-medium" style="width: {{ $value2 }}%">
        @if(($value2 ?? 0) > 15){{ $value2 }}% @endif
    </div>
    <div class="{{ $mapClasses($class3 ?? '') }} h-full flex items-center justify-center text-xs text-white font-medium" style="width: {{ $value3 }}%">
        @if(($value3 ?? 0) > 15){{ $value3 }}% @endif
    </div>
</div>
