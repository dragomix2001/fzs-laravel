<title>Студијски програм</title>
@extends('layouts.layout')
@section('page_heading','Студијски програм')
@section('section')

    <div class="mb-4">
        <form method="GET" action="{{"/"}}studijskiProgram/add" class="inline">
            <x-button variant="primary">Додавање</x-button>
        </form>
    </div>

    <div class="max-w-4xl">
        <x-table>
            <thead>
            <tr>
                <th>Назив</th>
                <th>Скраћени назив</th>
                <th>Тип студија</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studijskiProgram as $studijskiProgram)
                <tr>
                    <td>{{$studijskiProgram->naziv}}</td>
                    <td>{{$studijskiProgram->skrNazivStudijskogPrograma}}</td>
                    <td>
                        @if($studijskiProgram->tipStudija)
                            {{$studijskiProgram->tipStudija->naziv}}
                        @else
                            Prazno
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <form action="studijskiProgram/{{$studijskiProgram->id}}/edit" class="inline">
                                <x-button variant="primary" size="sm">Измени</x-button>
                            </form>
                            <form onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="studijskiProgram/{{$studijskiProgram->id}}/delete" class="inline">
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
