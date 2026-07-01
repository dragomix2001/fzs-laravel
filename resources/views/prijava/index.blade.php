@extends('layouts.layout')
@section('page_heading','Испити')
@section('section')
    <div class="w-full">
        @if (Session::get('errors'))
            <x-alert type="danger" :dismissible="true">
                <x-slot:title>Грешка!</x-slot:title>
                <ul>
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        {{-- Student Info Card --}}
        <x-card class="mb-6">
            <h4 class="text-base font-semibold text-gray-700 mb-3">Подаци о студенту</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">Број Индекса</dt>
                    <dd class="font-medium text-gray-900">{{ $kandidat->brojIndeksa }}</dd>
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <dt class="text-sm text-gray-500">Име (име родитеља) презиме</dt>
                    <dd class="font-medium text-gray-900">{{ $kandidat->imeKandidata . " (" . $kandidat->imePrezimeJednogRoditelja . ") " . $kandidat->prezimeKandidata }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">ЈМБГ</dt>
                    <dd class="font-medium text-gray-900">{{ $kandidat->jmbg }}</dd>
                </div>
                @if(!empty($kandidat->datumRodjenja))
                    <div>
                        <dt class="text-sm text-gray-500">Датум рођења</dt>
                        <dd class="font-medium text-gray-900">{{ $kandidat->datumRodjenja->format('d.m.Y') }}</dd>
                    </div>
                @endif
            </dl>
        </x-card>

        {{-- Exam Applications Panel --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold">Пријава за полагање испита</h3>
            </x-slot:header>
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{"/"}}prijava/student/{{$kandidat->id}}">
                    <x-button variant="primary">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Нова пријава
                    </x-button>
                </a>
                <a href="{{"/"}}priznavanjeIspita/{{$kandidat->id}}">
                    <x-button variant="info">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Признати испити
                    </x-button>
                </a>
                <a href="{{"/"}}prijava/unosPrivremeni/{{$kandidat->id}}">
                    <x-button variant="warning">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Додај испите
                    </x-button>
                </a>
            </div>

            <div id="messages">
                @if (Session::get('flash-error'))
                    <x-alert type="danger" :dismissible="true">
                        <x-slot:title>Грешка!</x-slot:title>
                        @if(Session::get('flash-error') === 'update')
                            Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                        @elseif(Session::get('flash-error') === 'delete')
                            Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                        @elseif(Session::get('flash-error') === 'upis')
                            Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину и покушајте поново.
                        @endif
                    </x-alert>
                @elseif(Session::get('flash-success'))
                    <x-alert type="success" :dismissible="true">
                        <x-slot:title>Успех!</x-slot:title>
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

            <hr class="my-4 border-gray-200">

            <div class="overflow-x-auto">
                <table id="tabela" class="w-full text-sm text-left">
                    <thead>
                    <tr>
                        <th>Кандидат</th>
                        <th>Број Индекса</th>
                        <th>Предмет</th>
                        <th>Рок</th>
                        <th>Број Полагања</th>
                        <th>Датум</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($prijave as $index => $prijava)
                        <tr>
                            <td>{{$kandidat->imeKandidata . " " . $kandidat->prezimeKandidata}}</td>
                            <td>{{$kandidat->brojIndeksa}}</td>
                            <td>{{$prijava->predmet?->predmet?->naziv ?? '-'}}</td>
                            <td>{{$prijava->rok?->naziv ?? '-'}}</td>
                            <td>{{$prijava->brojPolaganja}}</td>
                            <td data-order="{{$prijava->datum->timestamp}}">{{$prijava->datum->format('d.m.Y')}}</td>
                            <td>
                                <a href="{{"/"}}prijava/delete/{{ $prijava->id }}?prijava=student"
                                   onclick="return confirm('Да ли сте сигурни да желите да обришете ову пријаву?');"
                                   class="inline-flex items-center px-3 py-1.5 bg-danger-600 text-white text-sm font-medium rounded-lg hover:bg-danger-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Бриши
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Diploma Section --}}
        <x-card variant="success" class="mt-6">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Дипломирање</h3>
            </x-slot:header>
            @if($diplomskiRadTema != null || $diplomskiRadPolaganje != null || $diplomskiRadOdbrana != null)
                <div class="overflow-x-auto">
                    <table id="tabela" class="w-full text-sm text-left">
                        <thead>
                        <tr>
                            <th>ВРСТА</th>
                            <th>Назив</th>
                            <th>Предмет</th>
                            <th>Тему одобрио професор</th>
                            <th>Одбрану одобрио професор</th>
                            <th>Одобрена</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($diplomskiRadTema != null)
                            <tr>
                                <td>Пријава ТЕМЕ</td>
                                <td>{{$diplomskiRadTema->nazivTeme}}</td>
                                <td>{{$diplomskiRadTema->predmet?->predmet?->naziv ?? '-'}}</td>
                                <td>{{($diplomskiRadTema->profesor?->ime ?? '') . " " . ($diplomskiRadTema->profesor?->prezime ?? '')}}</td>
                                <td>-</td>
                                <td>
                                    @if($diplomskiRadTema->indikatorOdobreno == 1)
                                        <x-badge variant="success">ДА</x-badge>
                                    @else
                                        <x-badge variant="danger">НЕ</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-flex gap-1">
                                        <a href="{{"/"}}prijava/diplomskiTema/{{ $kandidat->id }}/edit" class="inline-flex items-center p-2 bg-warning-500 text-white rounded-lg hover:bg-warning-600 transition-colors" title="Измена">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <a href="{{"/"}}deleteDiplomskiTema/{{ $kandidat->id }}/delete"
                                           onclick="return confirm('Да ли сте сигурни да желите да обришете?');"
                                           class="inline-flex items-center p-2 bg-danger-600 text-white rounded-lg hover:bg-danger-700 transition-colors" title="Брисање">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @if($diplomskiRadOdbrana != null)
                            <tr>
                                <td>Пријава ОДБРАНЕ</td>
                                <td>{{$diplomskiRadOdbrana->nazivTeme}}</td>
                                <td>{{$diplomskiRadOdbrana->predmet?->predmet?->naziv ?? '-'}}</td>
                                <td>{{($diplomskiRadOdbrana->odobrioTemuProfesor?->ime ?? '') . " " . ($diplomskiRadOdbrana->odobrioTemuProfesor?->prezime ?? '')}}</td>
                                <td>{{($diplomskiRadOdbrana->odobrioOdbranuProfesor?->ime ?? '') . " " . ($diplomskiRadOdbrana->odobrioOdbranuProfesor?->prezime ?? '')}}</td>
                                <td>
                                    @if($diplomskiRadOdbrana->indikatorOdobreno == 1)
                                        <x-badge variant="success">ДА</x-badge>
                                    @else
                                        <x-badge variant="danger">НЕ</x-badge>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-flex gap-1">
                                        <a href="{{"/"}}prijava/diplomskiOdbrana/{{ $kandidat->id }}/edit" class="inline-flex items-center p-2 bg-warning-500 text-white rounded-lg hover:bg-warning-600 transition-colors" title="Измена">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <a href="{{"/"}}deleteDiplomskiOdbrana/{{ $kandidat->id }}/delete"
                                           onclick="return confirm('Да ли сте сигурни да желите да обришете?');"
                                           class="inline-flex items-center p-2 bg-danger-600 text-white rounded-lg hover:bg-danger-700 transition-colors" title="Брисање">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @if($diplomskiRadPolaganje != null)
                            <tr>
                                <td>Пријава ПОЛАГАЊА</td>
                                <td>{{$diplomskiRadPolaganje->nazivTeme}}</td>
                                <td>{{$diplomskiRadPolaganje->predmet?->predmet?->naziv ?? '-'}}</td>
                                <td>{{($diplomskiRadPolaganje->profesor?->ime ?? '') . " " . ($diplomskiRadPolaganje->profesor?->prezime ?? '')}}</td>
                                <td>-</td>
                                <td>
                                    @if($diplomskiRadPolaganje->brojBodova > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Оцена: {{$diplomskiRadPolaganje->ocena}}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-flex gap-1">
                                        <a href="{{"/"}}prijava/diplomskiPolaganje/{{ $kandidat->id }}/edit" class="inline-flex items-center p-2 bg-warning-500 text-white rounded-lg hover:bg-warning-600 transition-colors" title="Измена">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <a href="{{"/"}}deleteDiplomskiPolaganje/{{ $kandidat->id }}/delete"
                                           onclick="return confirm('Да ли сте сигурни да желите да обришете?');"
                                           class="inline-flex items-center p-2 bg-danger-600 text-white rounded-lg hover:bg-danger-700 transition-colors" title="Брисање">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="flex flex-wrap gap-2 mt-4">
                <a href="{{"/"}}prijava/diplomskiTema/{{$kandidat->id}}"
                   class="inline-flex items-center px-4 py-2 bg-success-600 text-white text-sm font-semibold rounded-lg hover:bg-success-700 transition-colors {{ $diplomskiRadTema != null ? 'opacity-50 pointer-events-none' : '' }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Пријава теме дипломског рада
                </a>
                <a href="{{"/"}}prijava/diplomskiOdbrana/{{$kandidat->id}}"
                   class="inline-flex items-center px-4 py-2 bg-success-600 text-white text-sm font-semibold rounded-lg hover:bg-success-700 transition-colors {{ $diplomskiRadOdbrana != null ? 'opacity-50 pointer-events-none' : '' }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Пријава одбране дипломског рада
                </a>
                <a href="{{"/"}}prijava/diplomskiPolaganje/{{$kandidat->id}}"
                   class="inline-flex items-center px-4 py-2 bg-success-600 text-white text-sm font-semibold rounded-lg hover:bg-success-700 transition-colors {{ $diplomskiRadPolaganje != null ? 'opacity-50 pointer-events-none' : '' }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Пријава за полагање дипломског испита
                </a>
            </div>
        </x-card>

        {{-- Documents Section --}}
        <x-card variant="success" class="mt-6">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Документа</h3>
            </x-slot:header>
            <input type="hidden" value="{{$kandidat->id}}">
            <div class="flex flex-wrap gap-2">
                <a target="_blank" href="{{"/"}}izvestaji/diplomaStampa/{{$kandidat->id}}">
                    <x-button variant="primary">Штампа уверења о дипломирању</x-button>
                </a>
                <a target="_blank" href="{{"/"}}izvestaji/komisijaStampa/{{$kandidat->id}}">
                    <x-button variant="primary">Комисија</x-button>
                </a>
                <a target="_blank" href="{{"/"}}izvestaji/polozeniStampa/{{$kandidat->id}}">
                    <x-button variant="primary">Уверење о положеним испитима</x-button>
                </a>
                <a target="_blank" href="{{"/"}}izvestaji/zapisnikDiplomski/{{$kandidat->id}}">
                    <x-button variant="primary">Записник са одбране дипломског</x-button>
                </a>
            </div>
        </x-card>

        {{-- Passed Exams Section --}}
        @if(!empty($ispiti))
            <x-card class="mt-6">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Положени испити</h3>
                </x-slot:header>
                <div class="overflow-x-auto">
                    <table id="tabela2" class="w-full text-sm text-left">
                        <thead>
                        <tr>
                            <th>Предмет</th>
                            <th>Рок</th>
                            <th>Оцена</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ispiti as $index => $ispit)
                            <tr>
                                <td>{{$ispit->predmet?->predmet?->naziv ?? '-'}}</td>
                                <td>{{$ispit->prijava?->rok?->naziv ?? '-'}}</td>
                                <td>{{$ispit->konacnaOcena}}</td>
                                <td>
                                    <div class="inline-flex gap-2">
                                        <a href="{{"/"}}ispit/delete/{{ $ispit->id }}?brisiZapisnik=0"
                                           onclick="return confirm('Ова акција брише само оцену и враћа испит у почетно стање на записнику. Да ли сте сигурни да желите да наставите?');"
                                           class="inline-flex items-center px-3 py-1.5 bg-danger-600 text-white text-sm font-medium rounded-lg hover:bg-danger-700 transition-colors">Бриши оцену</a>
                                        <a href="{{"/"}}ispit/delete/{{ $ispit->id }}?brisiZapisnik=1"
                                           onclick="return confirm('Ова акција брише оцену и упис студента на записнику. Да ли сте сигурни да желите да наставите?');"
                                           class="inline-flex items-center px-3 py-1.5 bg-danger-600 text-white text-sm font-medium rounded-lg hover:bg-danger-700 transition-colors">Бриши оцену и записник</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#tabela2').dataTable({
                "aaSorting": [],
                "oLanguage": {
                    "sProcessing": "Процесирање у току...",
                    "sLengthMenu": "Прикажи _MENU_ елемената",
                    "sZeroRecords": "Није пронађен ниједан резултат",
                    "sInfo": "Приказ _START_ до _END_ од укупно _TOTAL_ елемената",
                    "sInfoEmpty": "Приказ 0 до 0 од укупно 0 елемената",
                    "sInfoFiltered": "(филтрирано од укупно _MAX_ елемената)",
                    "sInfoPostFix": "",
                    "sSearch": "Претрага:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "Почетна",
                        "sPrevious": "Претходна",
                        "sNext": "Следећа",
                        "sLast": "Последња"
                    }
                }
            });
        });
    </script>
@endsection
