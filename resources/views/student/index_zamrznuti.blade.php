@extends('layouts.layout')
@section('page_heading','Студенти у статусу мировања студија')
@section('section')
    <div class="col-span-12">
        <div id="messages">
            @if (Session::get('flash-error'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
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
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM3.707 9.293a1 1 0 00-1.414 1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
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
                                <a class="inline-flex items-center px-2.5 py-1.5 bg-warning-500 hover:bg-warning-400 text-white text-xs font-medium rounded-lg transition-colors" href="{{"/"}}{{ $kandidat->tipStudija_id == 1 ? 'kandidat' : 'master' }}/{{ $kandidat->id }}/edit" title="Измена">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a class="inline-flex items-center px-2.5 py-1.5 bg-danger-600 hover:bg-danger-500 text-white text-xs font-medium rounded-lg transition-colors" href="{{"/"}}kandidat/{{ $kandidat->id }}/delete" title="Брисање"
                                   onclick="return confirm('Да ли сте сигурни да желите да обришете податке овог студента?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors" href="{{"/"}}student/{{ $kandidat->id }}/upis">
                                    Статус
                                </a>
                                <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors"
                                   href="{{"/"}}prijava/zastudenta/{{ $kandidat->id }}">
                                    Испити
                                </a>
                                <a class="inline-flex items-center px-2.5 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-medium rounded-lg transition-colors"
                                   href="{{"/"}}izvestaji/potvrdeStudent/{{$kandidat->id}}">
                                    Потврде
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </x-table>
        </form>
        <br>
        <hr class="my-4 border-secondary-200">
    </div>
    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
    <script>
        var forma = $('#formaKandidatiOdabir');

        $('#masovnaUplata').click(function () {
            forma.attr("action", "{{"/"}}student/masovnaUplata");
            forma.submit();
        });

        $('#masovniUpis').click(function () {
            forma.attr("action", "{{"/"}}student/masovniUpis");
            forma.submit();
        });
    </script>
@endsection
