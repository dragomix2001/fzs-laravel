@props([
    'padding' => true,
    'shadow' => true,
])

@php
$cardClasses = 'bg-white rounded-lg border border-secondary-200 overflow-hidden';
if ($shadow) {
    $cardClasses .= ' shadow-sm hover:shadow-md transition-shadow duration-200';
}
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-secondary-200 bg-secondary-50">
            <h3 class="text-lg font-semibold text-secondary-900">
                {{ $header }}
            </h3>
        </div>
    @endisset

    <div class="{{ $padding ? 'p-6' : '' }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-secondary-200 bg-secondary-50">
            {{ $footer }}
        </div>
    @endisset
</div>
