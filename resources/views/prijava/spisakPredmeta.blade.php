@extends('layouts.layout')
@section('page_heading','Пријава испита')
@section('section')
    <div id="messages">
        @if (Session::get('flash-error'))
            <x-alert type="danger">
                @if(Session::get('flash-error') === 'update')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @elseif(Session::get('flash-error') === 'delete')
                    Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                @elseif(Session::get('flash-error') === 'upis')
                    Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину и
                    покушајте поново.
                @endif
            </x-alert>
        @elseif(Session::get('flash-success'))
            <x-alert type="success">
                @if(Session::get('flash-success') === 'update')
                    Подаци о кандидату су успешно сачувани.
                @elseif(Session::get('flash-success') === 'delete')
                    Подаци о кандидату су успешно обрисани.
                @elseif(Session::get('flash-success') === 'upis')
                    Упис кандидата је успешно извршен.
                @endif
            </x-alert>
        @endif
    </div>
    <ul class="flex flex-wrap gap-2 mb-4">
        @foreach($tipStudija as $tip)
            <li>
                <a href="?tipStudijaId={{ $tip->id }}"
                   class="inline-block px-4 py-2 rounded-lg text-sm font-semibold transition-colors duration-150
                   {{ Request::input('tipStudijaId') == $tip->id ? 'bg-primary-600 text-white shadow-sm' : 'bg-primary-50 text-primary-700 hover:bg-primary-100' }}">
                    {{ $tip->naziv }}
                </a>
            </li>
        @endforeach
    </ul>
    <br>
    <div class="w-full lg:w-10/12">
        @if(!empty($predmeti))
            <x-table>
                <thead>
                <th>Назив предмета</th>
                <th>Акције</th>
                </thead>
                @foreach($predmeti as $predmet)
                    <tr>
                        <td>{{$predmet->naziv}}</td>
                        <td>
                            <div class="inline-flex gap-2">
                                <a href="prijava/zaPredmet/{{$predmet->id}}"><x-button variant="primary" size="sm">Пријава испита</x-button></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        @endif
        <br/>
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
@endsection
