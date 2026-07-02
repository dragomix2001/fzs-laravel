@php
    $alertColors = [
        'success' => 'bg-success-50 border-success-200 text-success-800',
        'info' => 'bg-cyan-50 border-cyan-200 text-cyan-800',
        'warning' => 'bg-warning-50 border-warning-200 text-warning-800',
        'danger' => 'bg-danger-50 border-danger-200 text-danger-800',
    ];
    $iconColors = [
        'success' => 'text-success-500',
        'info' => 'text-cyan-500',
        'warning' => 'text-warning-500',
        'danger' => 'text-danger-500',
    ];
    $colorClass = $alertColors[$class] ?? 'bg-secondary-50 border-secondary-200 text-secondary-800';
    $iconColor = $iconColors[$class] ?? 'text-secondary-500';
@endphp
<div x-data="{ show: true }" x-show="show" class="flex items-start gap-3 px-4 py-3 rounded-lg border {{ $colorClass }}" role="alert">
    @if(isset($dismissable))
    <button type="button" class="ml-auto shrink-0 {{ $iconColor }} hover:opacity-75" @click="show = false" aria-label="Close">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>
    @endif
    <i class="fas fa-{{ (isset($icon)) ? $icon : $class }} {{ $iconColor }} mt-0.5"></i>
    <div class="text-sm flex-1">
        @if(isset($link)) <a href="#" class="font-medium underline">{{ $link }}</a> @endif
        {{ $message }}.
    </div>
</div>
