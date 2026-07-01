<title>Приложена документа</title>
@extends('layouts.layout')
@section('page_heading','Приложена документа')
@section('section')

    <div class="mb-6">
        <form class="inline-block" method="GET" action="{{"/"}}prilozenaDokumenta/add">
            <x-button variant="primary" type="submit">Додавање</x-button>
        </form>
    </div>

    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <tr>
                <th>Редни број</th>
                <th>Назив</th>
                <th>Година</th>
                <th>Акције</th>
            </tr>
            </thead>
            <tbody>
            @foreach($dokument as $dokument)
                <tr>
                    <td>{{$dokument->redniBrojDokumenta}}</td>
                    <td>{{$dokument->naziv}}</td>
                    <td>{{$dokument->godinaStudija->naziv}}</td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="prilozenaDokumenta/{{$dokument->id}}/edit">
                                <x-button variant="primary" size="sm" type="submit">Измени</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="prilozenaDokumenta/{{$dokument->id}}/delete">
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
