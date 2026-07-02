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

    <x-button variant="primary" size="md" href="{{"/"}}kalendar/createRok/">
        <span class="fa fa-plus"></span> Нови рок
    </x-button>

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
                                <x-button variant="warning" size="xs" href="/kalendar/rok/{{ $rok->id }}/edit">
                                    <span class="fa fa-edit" title="Измена"></span>
                                </x-button>
                                <x-button variant="danger" size="xs" href="/kalendar/rok/{{ $rok->id }}"
                                   onclick="return confirm('Да ли сте сигурни да желите да обришете испитни рок?');">
                                    <span class="fa fa-trash" title="Брисање"></span>
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
