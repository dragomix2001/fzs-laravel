@props([
    'name',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
])

@php
$textareaClasses = 'block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-secondary-100 disabled:cursor-not-allowed';
if ($error) {
    $textareaClasses = 'block w-full rounded-lg border-danger-300 text-danger-900 placeholder-danger-300 focus:border-danger-500 focus:ring-danger-500';
}
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-secondary-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-danger-600">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except('class')->merge(['class' => $textareaClasses]) }}
    >{{ old($name, $value) }}</textarea>

    @if($error)
        <p class="mt-1 text-sm text-danger-600">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1 text-sm text-secondary-500">{{ $hint }}</p>
    @endif
</div>
