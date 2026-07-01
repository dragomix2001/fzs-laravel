@extends('layouts.layout')
@section('page_heading','Пријава испита')
@section('section')
    <div id="messages">
        @if (Session::get('flash-error'))
            <x-card variant="danger" class="mb-4">
                <x-slot:header>
                    <strong>Грешка!</strong>
                </x-slot:header>
                @if(Session::get('flash-error') === 'update')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @elseif(Session::get('flash-error') === 'delete')
                    Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                @elseif(Session::get('flash-error') === 'upis')
                    Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину и
                    покушајте поново.
                @endif
            </x-card>
        @endif
    </div>

    <a href="{{"/"}}kalendar/createRok/" class="inline-flex items-center gap-1 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm font-medium">
        <span class="fa fa-plus"></span> Нови рок
    </a>

    <div class="mt-4">
        @if(!empty($ispitniRokovi))
            <x-table id="tabela">
                <thead>
                <tr>
                    <th>Основни рок</th>
                    <th>Назив</th>
                    <th>Почетак</th>
                    <th>Крај</th>
                    <th>Тип рока</th>
                    <th>Коментар</th>
                    <th></th>
                </tr>
                </thead>
                @foreach($ispitniRokovi as $rok)
                    <tr>
                        <td>{{$rok->nadredjeniRok->naziv ?? '-'}}</td>
                        <td>{{$rok->naziv}}</td>
                        <td>{{\Carbon\Carbon::parse($rok->pocetak)->format('d.m.Y.')}}</td>
                        <td>{{\Carbon\Carbon::parse($rok->kraj)->format('d.m.Y.')}}</td>
                        <td>{{\App\Models\AktivniIspitniRokovi::tipRoka($rok->tipRoka_id)}}</td>
                        <td>{{$rok->komentar}}</td>
                        <td>
                            <div class="inline-flex gap-1">
                                <a class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600" href="{{"/"}}kalendar/editRok/{{ $rok->id }}">
                                    <span class="fa fa-edit" title="Измена"></span>
                                </a>
                                <a class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700" href="{{"/"}}kalendar/deleteRok/{{ $rok->id }}"
                                   onclick="return confirm('Да ли сте сигурни да желите да обришете испитни рок?');">
                                    <span class="fa fa-trash" title="Брисање"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
