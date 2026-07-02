@extends('layouts.layout')
@section('page_heading','Детаљи активности')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>{{ $aktivnost->naziv }}</h2>

    <div class="mb-4 space-y-1">
        <p><strong>Предмет:</strong> {{ $aktivnost->predmet->naziv ?? '' }}</p>
        <p><strong>Тип:</strong> {{ ucfirst($aktivnost->tip) }}</p>
        <p><strong>Максимално бодова:</strong> {{ $aktivnost->max_bodova }}</p>
        <p><strong>Датум:</strong> {{ \Carbon\Carbon::parse($aktivnost->datum)->format('d.m.Y.') }}</p>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('aktivnost.ocenjivanje', $aktivnost->id) }}" class="inline-flex items-center px-4 py-2 bg-warning-500 text-white rounded text-sm font-medium hover:bg-warning-600">Оцени студенте</a>
        <x-button variant="secondary-soft" size="md" href="{{ route('aktivnost.index') }}">Назад на листу</x-button>
    </div>

    <h4 class="text-md font-semibold mt-4">Оцене студената</h4>

    @if(count($ocene) > 0)
    <x-table class="mt-3">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Број индекса</th>
                <th>Освојени бодови</th>
                <th>Оцена</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ocene as $ocena)
            <tr>
                <td>{{ $ocena->student->ime ?? '' }} {{ $ocena->student->prezime ?? '' }}</td>
                <td>{{ $ocena->student->brojIndeksa ?? '' }}</td>
                <td>{{ $ocena->bodovi }}</td>
                <td>{{ $ocena->ocena }}</td>
            </tr>
            @endforeach
        </tbody>
    </x-table>
    @else
    <x-card variant="info" class="mt-3">Тренутно нема унетих оцена за ову активност.</x-card>
    @endif
</div>
@endsection
