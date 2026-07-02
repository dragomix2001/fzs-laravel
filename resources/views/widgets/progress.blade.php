@php
    $barColors = [
        'primary' => 'bg-primary-600',
        'success' => 'bg-success-500',
        'info' => 'bg-cyan-500',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-500',
    ];
    $barClass = $barColors[isset($class) ? $class : 'primary'] ?? 'bg-primary-600';
@endphp
<div class="w-full bg-secondary-200 rounded-full h-2.5 mb-2">
    <div class="{{ $barClass }} @if(isset($striped)) bg-striped @endif @if(isset($animated)) animate-pulse @endif h-2.5 rounded-full transition-all duration-500" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $value }}%">
        @if(isset($badge))<span class="text-xs text-white ml-2">{{ $value }}%</span>@endif
    </div>
</div>
