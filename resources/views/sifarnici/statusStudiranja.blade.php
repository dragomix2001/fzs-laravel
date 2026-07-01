<title>Статус студирања</title>
@extends('layouts.layout')
@section('page_heading','Статус студирања')
@section('section')
    <div class="mb-4">
        <form class="inline-block" method="GET" action="{{"/"}}statusStudiranja/add">
            <x-button variant="primary">Додавање</x-button>
        </form>
    </div>

    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <th>
                Назив статуса
            </th>
            <th>
                Акције
            </th>
            </thead>

            @foreach($statusStudiranja as $statusStudiranja)
                <tr>
                    <td>{{$statusStudiranja->naziv}}</td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="statusStudiranja/{{$statusStudiranja->id}}/edit">
                                <x-button variant="primary" size="sm">Измени</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="statusStudiranja/{{$statusStudiranja->id}}/delete">
                                <x-button variant="danger" size="sm">Обриши</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>

@endsection
