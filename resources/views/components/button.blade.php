@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'bg-primary-600 text-white hover:bg-primary-500 focus:ring-primary-500',
    'secondary-soft' => 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200 focus:ring-secondary-300',
    'danger' => 'bg-danger-600 text-white hover:bg-danger-500 focus:ring-danger-500',
    'success' => 'bg-success-600 text-white hover:bg-success-500 focus:ring-success-500',
    'warning' => 'bg-warning-500 text-white hover:bg-warning-400 focus:ring-warning-400',
    'info' => 'bg-cyan-600 text-white hover:bg-cyan-500 focus:ring-cyan-500',
    'outline' => 'bg-white text-secondary-700 border border-secondary-300 hover:bg-secondary-50 focus:ring-secondary-500',
    'ghost' => 'bg-transparent text-secondary-700 hover:bg-secondary-100 focus:ring-secondary-500',
];

$sizeClasses = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-4 py-2 text-base',
    'xl' => 'px-6 py-3 text-base',
    '2xl' => 'px-6 py-3 text-lg',
];

$classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
