@extends('layouts.layout')
@section('page_heading','Активни студенти основних студија')
@section('section')
    <div class="col-span-12">
        <div id="messages">
            @if (Session::get('flash-error'))
                <div class="rounded-lg bg-danger-50 border border-danger-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-danger-800">
                                <strong>Грешка!</strong>
                                @if(Session::get('flash-error') === 'update')
                                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                                @elseif(Session::get('flash-error') === 'delete')
                                    Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                                @elseif(Session::get('flash-error') === 'upis')
                                    Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину
                                    и покушајте поново.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @elseif(Session::get('flash-success'))
                <div class="rounded-lg bg-success-50 border border-success-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM3.707 9.293a1 1 0 00-1.414 1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-success-800">
                                <strong>Успех!</strong>
                                @if(Session::get('flash-success') === 'update')
                                    Подаци о кандидату су успешно сачувани.
                                @elseif(Session::get('flash-success') === 'delete')
                                    Подаци о кандидату су успешно обрисани.
                                @elseif(Session::get('flash-success') === 'upis')
                                    Упис кандидата је успешно извршен.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="border-b border-secondary-200 mb-4">
            <nav class="flex gap-1 -mb-px">
                <a class="px-4 py-2 text-sm font-medium rounded-t-lg {{ (Request::input('godina') == '1' || Request::input('godina') == null) ? 'bg-white text-primary-600 border border-secondary-200 border-b-0' : 'text-secondary-500 hover:text-secondary-700' }}" 
                   href="?godina=1&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Прва година</a>
                <a class="px-4 py-2 text-sm font-medium rounded-t-lg {{ Request::input('godina') == '2' ? 'bg-white text-primary-600 border border-secondary-200 border-b-0' : 'text-secondary-500 hover:text-secondary-700' }}" 
                   href="?godina=2&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Друга година</a>
                <a class="px-4 py-2 text-sm font-medium rounded-t-lg {{ Request::input('godina') == '3' ? 'bg-white text-primary-600 border border-secondary-200 border-b-0' : 'text-secondary-500 hover:text-secondary-700' }}" 
                   href="?godina=3&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Трећа година</a>
                <a class="px-4 py-2 text-sm font-medium rounded-t-lg {{ Request::input('godina') == '4' ? 'bg-white text-primary-600 border border-secondary-200 border-b-0' : 'text-secondary-500 hover:text-secondary-700' }}" 
                   href="?godina=4&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Четврта година</a>
            </nav>
        </div>
        
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($studijskiProgrami as $program)
                <a class="px-3 py-1.5 text-sm font-medium rounded-full {{ Request::input('studijskiProgramId') == $program->id ? 'bg-primary-600 text-white' : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200' }}" 
                   href="?godina={{ Request::input('godina') }}&studijskiProgramId={{ $program->id }}">
                    {{ $program->naziv }}
                </a>
            @endforeach
        </div>
        
        <hr class="my-4 border-secondary-200">
        <form id="formaKandidatiOdabir" action="" method="post">
            {{ csrf_field() }}
            <x-table id="tabela">
                <thead>
                <tr>
                    <th>Одабир</th>
                    <th>Име</th>
                    <th>Презиме</th>
                    <th>ЈМБГ</th>
                    <th>Број Индекса</th>
                    <th>Измена</th>
                </tr>
                </thead>
                <tbody>
                @foreach($studenti as $index => $kandidat)
                    <tr>
                        <td><input type="checkbox" id="odabir" name="odabir[{{ $index }}]" value="{{ $kandidat->id }}" class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                        </td>
                        <td>{{$kandidat->imeKandidata}}</td>
                        <td>{{$kandidat->prezimeKandidata}}</td>
                        <td>{{$kandidat->jmbg}}</td>
                        <td>{{$kandidat->brojIndeksa}}</td>
                        <td>
                            <div class="flex gap-1 flex-wrap">
                                <x-button variant="warning" size="xs" href="{{ url('student/' . $kandidat->id . '/edit') }}">
                                    <i class="fas fa-edit"></i>
                                </x-button>
                                <x-button variant="danger" size="xs" href="{{ url('student/' . $kandidat->id . '/delete') }}" onclick="return confirm('Да ли сте сигурни?');">
                                    <i class="fas fa-trash"></i>
                                </x-button>
                                <x-button variant="primary" size="xs" href="{{ url('student/' . $kandidat->id . '/upis') }}">
                                    <i class="fas fa-user-plus mr-1"></i> Упис
                                </x-button>
                                <x-button variant="info" size="xs" href="{{ url('student/' . $kandidat->id) }}">
                                    <i class="fas fa-file-alt mr-1"></i> Пријаве
                                </x-button>
                                <x-button variant="secondary" size="xs" href="{{ url('/izvestaji/potvrdeStudent/' . $kandidat->id) }}">
                                    <i class="fas fa-file-pdf mr-1"></i> Потврда
                                </x-button>
                                <x-button variant="success" size="xs" href="{{ url('student/' . $kandidat->id . '/skolarina') }}">
                                    <i class="fas fa-money-bill mr-1"></i> Школарина
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-table>
        </form>
        <br>
        <hr class="my-4 border-secondary-200">
        <x-card class="border-primary-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">За одабране кандидате</div>
            </x-slot:header>
            <div class="flex gap-2">
                <div id="masovnaUplata" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    <i class="fas fa-money-bill mr-2"></i> Уплатили школарину за следећу годину
                </div>
                <div id="masovniUpis" class="inline-flex items-center px-4 py-2 bg-success-600 hover:bg-success-500 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                    <i class="fas fa-user-check mr-2"></i> Упис у следећу годину
                </div>
            </div>
        </x-card>
        <br>
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
    <script>
        var forma = $('#formaKandidatiOdabir');

        $('#masovnaUplata').click(function () {
            forma.attr("action", "{{ url('/student/masovnaUplata') }}");
            forma.submit();
        });

        $('#masovniUpis').click(function () {
            forma.attr("action", "{{ url('/student/masovniUpis') }}");
            forma.submit();
        });
    </script>
@endsection
