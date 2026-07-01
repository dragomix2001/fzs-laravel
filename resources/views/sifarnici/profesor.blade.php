<title>Професори</title>
@extends('layouts.layout')
@section('page_heading','Професори')
@section('section')

    <div class="mb-4">
        <form method="GET" action="{{"/"}}profesor/add" class="inline">
            <x-button variant="primary">Додавање</x-button>
        </form>
    </div>

    <div class="w-full">
        <x-table>
            <thead>
            <tr>
                <th>Име</th>
                <th>Презиме</th>
                <th>Телефон</th>
                <th>Е - мејл</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($profesor as $profesor)
                <tr>
                    <td>{{$profesor->ime}}</td>
                    <td>{{$profesor->prezime}}</td>
                    <td>{{$profesor->telefon}}</td>
                    <td>{{$profesor->mail}}</td>
                    <td>
                        <div class="flex gap-2">
                            <form action="profesor/{{$profesor->id}}/edit" class="inline">
                                <x-button variant="primary" size="sm">Измени</x-button>
                            </form>
                            <form action="profesor/{{$profesor->id}}/editPredmet" class="inline">
                                <x-button variant="success" size="sm">Додај предмет</x-button>
                            </form>
                            <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="profesor/{{$profesor->id}}/delete" class="inline">
                                <x-button variant="danger" size="sm">Обриши</x-button>
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
