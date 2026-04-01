@props(['type' => 'info', 'dismissible' => true])

@php
$typeClasses = [
    'success' => 'bg-green-100 border-green-400 text-green-700',
    'danger' => 'bg-red-100 border-red-400 text-red-700',
    'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
    'info' => 'bg-blue-100 border-blue-400 text-blue-700',
][$type] ?? 'bg-blue-100 border-blue-400 text-blue-700';

$iconClasses = [
    'success' => 'fas fa-check-circle',
    'danger' => 'fas fa-exclamation-circle',
    'warning' => 'fas fa-exclamation-triangle',
    'info' => 'fas fa-info-circle',
][$type] ?? 'fas fa-info-circle';
@endphp

<div x-data="{ show: true }" x-show="show" class="border px-4 py-3 rounded relative mb-4 {{ $typeClasses }}" role="alert">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="{{ $iconClasses }} mt-1"></i>
        </div>
        <div class="ml-3 w-full">
            {{ $slot }}
        </div>
        @if($dismissible)
            <div class="ml-auto pl-3">
                <button type="button" @click="show = false" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ str_replace('text', 'hover:bg-opacity-20 hover:bg', $typeClasses) }}">
                    <span class="sr-only">Zatvori</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
</div>
