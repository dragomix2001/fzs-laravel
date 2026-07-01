@props([
    'variant' => 'secondary',
    'size' => 'md',
    'pill' => false,
])

@php
$variantClasses = [
    'secondary' => 'bg-secondary-100 text-secondary-700 ring-secondary-600/20',
    'primary' => 'bg-primary-100 text-primary-700 ring-primary-600/20',
    'danger' => 'bg-danger-100 text-danger-700 ring-danger-600/20',
    'success' => 'bg-success-100 text-success-700 ring-success-600/20',
    'warning' => 'bg-warning-100 text-warning-700 ring-warning-600/20',
];

$sizeClasses = [
    'sm' => 'px-1.5 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-base',
];

$baseClasses = 'inline-flex items-center font-medium ring-1 ring-inset';
if ($pill) {
    $baseClasses .= ' rounded-full';
} else {
    $baseClasses .= ' rounded-md';
}

$classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['secondary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
