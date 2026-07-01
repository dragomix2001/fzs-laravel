<title>Предмет</title>
@extends('layouts.layout')
@section('page_heading','Предмет')
@section('section')

    <div class="mb-4">
        <form method="GET" action="{{"/"}}predmet/add" class="inline">
            <x-button variant="primary">Додавање</x-button>
        </form>
    </div>

    <div class="w-full">
        <x-table>
            <thead>
            <tr>
                <th>Назив предмета</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($predmet as $predmet)
                <tr>
                    <td>{{$predmet->naziv}}</td>
                    <td>
                        <div class="flex gap-2">
                            <form action="predmet/{{$predmet->id}}/edit" class="inline">
                                <x-button variant="primary" size="sm">Измени</x-button>
                            </form>
                            <form action="predmet/{{$predmet->id}}/editProgram" class="inline">
                                <x-button variant="success" size="sm">Додај програм</x-button>
                            </form>
                            <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="predmet/{{$predmet->id}}/delete" class="inline">
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
