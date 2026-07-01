<title>Статус кандидата</title>
@extends('layouts.layout')
@section('page_heading','Статус кандидата')
@section('section')

    <div class="mb-4">
        <form class="inline-block" method="GET" action="{{ url('/statusKandidata/add') }}">
            <x-button variant="primary">Додавање</x-button>
        </form>
    </div>
    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <th>
                Назив
            </th>
            <th>
                Акције
            </th>
            </thead>

            @foreach($status as $status)
                <tr>
                    <td>{{$status->naziv}}</td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="{{ url('/statusKandidata/' . $status->id . '/edit') }}">
                                <x-button variant="primary" size="sm">Измени</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="{{ url('/statusKandidata/' . $status->id . '/delete') }}">
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
