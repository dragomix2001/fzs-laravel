@extends('layouts.layout')
@section('page_heading','Континуирано оцењивање')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Континуирано оцењивање</h2>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('aktivnost.create') }}" class="inline-flex items-center text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded text-sm font-medium">Нова активност</a>
        <a href="{{ route('aktivnost.rezime') }}" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">Преглед свих активности</a>
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
                        <a href="{{ route('aktivnost.show', $aktivnost->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">Прикажи</a>
                        <a href="{{ route('aktivnost.ocenjivanje', $aktivnost->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600">Оцени</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-table>
</div>
@endsection
