@props([
    'striped' => false,
    'hoverable' => true,
])

@php
$tableClasses = 'min-w-full divide-y divide-secondary-200';
$tbodyClasses = 'bg-white divide-y divide-secondary-200';
if ($striped) {
    $tbodyClasses = 'bg-white divide-y divide-secondary-200';
}
@endphp

<div class="overflow-x-auto rounded-lg border border-secondary-200 shadow-sm">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @isset($header)
            <thead class="bg-secondary-50">
                {{ $header }}
            </thead>
        @endisset

        <tbody class="{{ $tbodyClasses }}">
            {{ $slot }}
        </tbody>

        @isset($footer)
            <tfoot class="bg-secondary-50">
                {{ $footer }}
            </tfoot>
        @endisset
    </table>
</div>

@push('styles')
<style>
    @if($hoverable)
    tbody tr:hover {
        background-color: #f8fafc;
    }
    @endif

    @if($striped)
    tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }
    @endif
</style>
@endpush
