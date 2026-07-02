@extends('layouts.layout')
@section('page_heading','Континуирано оцењивање')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Континуирано оцењивање</h2>

    <div class="flex gap-2 mb-4">
        <x-button variant="success" size="md" href="{{ route('aktivnost.create') }}">Нова активност</x-button>
        <x-button variant="primary" size="md" href="{{ route('aktivnost.rezime') }}">Преглед свих активности</x-button>
    </div>

    <x-table>
        <thead>
            <tr>
                <th>Предмет</th>
                <th>Назив</th>
                <th>Тип</th>
                <th>Бодови</th>
                <th>Датум</th>
                <th>Акције</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aktivnosti as $aktivnost)
            <tr>
                <td>{{ $aktivnost->predmet->naziv }}</td>
                <td>{{ $aktivnost->naziv }}</td>
                <td>{{ ucfirst($aktivnost->tip) }}</td>
                <td>{{ $aktivnost->max_bodova }}</td>
                <td>{{ $aktivnost->datum }}</td>
                <td>
                    <div class="inline-flex gap-1">
                        <a href="{{ route('aktivnost.show', $aktivnost->id) }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded hover:bg-primary-700">Прикажи</a>
                        <a href="{{ route('aktivnost.ocenjivanje', $aktivnost->id) }}" class="inline-flex items-center px-3 py-1.5 bg-warning-500 text-white text-xs font-medium rounded hover:bg-warning-600">Оцени</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-table>
</div>
@endsection
