@extends('layouts.layout')
@section('page_heading','Оцењивање')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Оцењивање: {{ $aktivnost->naziv }}</h2>

    <x-card variant="info" class="mb-4">
        <strong>Максимално бодова:</strong> {{ $aktivnost->max_bodova }}
    </x-card>

    <form action="{{ route('aktivnost.saveOcenjivanje', $aktivnost->id) }}" method="POST">
        @csrf
        <x-table>
            <thead>
                <tr>
                    <th>Број индекса</th>
                    <th>Име и презиме</th>
                    <th>Освојени бодови</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studenti as $student)
                <tr>
                    <td>{{ $student->brojIndeksa }}</td>
                    <td>{{ $student->ime }} {{ $student->prezime }}</td>
                    <td>
                        <input type="number"
                               name="bodovi[{{ $student->id }}]"
                               value="{{ $ocene[$student->id] ?? '' }}"
                               class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               min="0"
                               max="{{ $aktivnost->max_bodova }}"
                               step="0.01">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-6 flex gap-2">
            <x-button variant="success">Сачувај оцене</x-button>
            <a href="{{ route('aktivnost.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm font-medium hover:bg-gray-300">Одустани</a>
        </div>
    </form>
</div>
@endsection
