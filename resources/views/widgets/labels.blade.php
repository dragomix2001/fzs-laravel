@php
    $labelMap = [
        'default' => 'bg-secondary-100 text-secondary-800',
        'primary' => 'bg-primary-100 text-primary-800',
        'success' => 'bg-success-100 text-success-800',
        'info' => 'bg-cyan-100 text-cyan-800',
        'warning' => 'bg-warning-100 text-warning-800',
        'danger' => 'bg-danger-100 text-danger-800',
    ];
    $labelClass = $labelMap[isset($class) ? $class : 'default'] ?? 'bg-secondary-100 text-secondary-800';
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $labelClass }}">{{ $value }}</span>
