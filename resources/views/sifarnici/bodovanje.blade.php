<title>Бодовање</title>
@extends('layouts.layout')
@section('page_heading','Бодовање')
@section('section')

    <div class="mb-6">
        <form class="inline-block" method="GET" action="{{"/"}}bodovanje/add">
            <x-button variant="primary" type="submit">Додавање</x-button>
        </form>
    </div>

    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <tr>
                <th>Описна оцена</th>
                <th>Минимум бодова</th>
                <th>Максимум бодова</th>
                <th>Оцена</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bodovanje as $bodovanje)
                <tr>
                    <td>{{$bodovanje->opisnaOcena}}</td>
                    <td>{{$bodovanje->poeniMin}}</td>
                    <td>{{$bodovanje->poeniMax}}</td>
                    <td>{{$bodovanje->ocena}}</td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="bodovanje/{{$bodovanje->id}}/edit">
                                <x-button variant="primary" size="sm" type="submit">Измени</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="bodovanje/{{$bodovanje->id}}/delete">
                                <x-button variant="danger" size="sm" type="submit">Обриши</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </x-table>
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>

@endsection
