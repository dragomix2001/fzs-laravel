@extends('layouts.layout')
@section('page_heading','Активни студенти основних студија')
@section('section')
    <div class="col-lg-12">
        <div id="messages">
            @if (Session::get('flash-error'))
                <div class="alert alert-dismissible alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Грешка!</strong>
                    @if(Session::get('flash-error') === 'update')
                        Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                    @elseif(Session::get('flash-error') === 'delete')
                        Дошло је до грешке при брисању података! Молимо вас покушајте поново.
                    @elseif(Session::get('flash-error') === 'upis')
                        Дошло је до грешке при упису кандидата! Молимо вас проверите да ли је кандидат уплатио школарину
                        и покушајте поново.
                    @endif
                </div>
            @elseif(Session::get('flash-success'))
                <div class="alert alert-dismissible alert-success">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Успех!</strong>
                    @if(Session::get('flash-success') === 'update')
                        Подаци о кандидату су успешно сачувани.
                    @elseif(Session::get('flash-success') === 'delete')
                        Подаци о кандидату су успешно обрисани.
                    @elseif(Session::get('flash-success') === 'upis')
                        Упис кандидата је успешно извршен.
                    @endif
                </div>
            @endif
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ (Request::input('godina') == '1' || Request::input('godina') == null) ? 'active' : '' }}" 
                           href="?godina=1&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Прва година</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::input('godina') == '2' ? 'active' : '' }}" 
                           href="?godina=2&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Друга година</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::input('godina') == '3' ? 'active' : '' }}" 
                           href="?godina=3&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Трећа година</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::input('godina') == '4' ? 'active' : '' }}" 
                           href="?godina=4&studijskiProgramId={{ Request::input('studijskiProgramId') }}">Четврта година</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-pills card-header-pills" role="tablist">
                    @foreach($studijskiProgrami as $program)
                        <li class="nav-item">
                            <a class="nav-link {{ Request::input('studijskiProgramId') == $program->id ? 'active' : '' }}" 
                               href="?godina={{ Request::input('godina') }}&studijskiProgramId={{ $program->id }}">
                                {{ $program->naziv }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        
        <hr>
        <form id="formaKandidatiOdabir" action="" method="post">
            {{ csrf_field() }}
            <table id="tabela" class="table">
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
                        <td><input type="checkbox" id="odabir" name="odabir[{{ $index }}]" value="{{ $kandidat->id }}">
                        </td>
                        <td>{{$kandidat->imeKandidata}}</td>
                        <td>{{$kandidat->prezimeKandidata}}</td>
                        <td>{{$kandidat->jmbg}}</td>
                        <td>{{$kandidat->brojIndeksa}}</td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="{{ url('/kandidat/' . $kandidat->id . '/edit') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                        <td>
                            <a class="btn btn-danger btn-sm" href="{{ url('/kandidat/' . $kandidat->id . '/delete') }}" onclick="return confirm('Да ли сте сигурни?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a class="btn btn-primary btn-sm" href="{{ url('/student/' . $kandidat->id . '/upis') }}">
                                <i class="fas fa-user-plus"></i> Упис
                            </a>
                            <a class="btn btn-info btn-sm" href="{{ url('/prijava/zaStudenta/' . $kandidat->id) }}">
                                <i class="fas fa-file-alt"></i> Пријаве
                            </a>
                            <a class="btn btn-secondary btn-sm" href="{{ url('/izvestaji/potvrdeStudent/' . $kandidat->id) }}">
                                <i class="fas fa-file-pdf"></i> Потврда
                            </a>
                            <a class="btn btn-success btn-sm" href="{{ url('/skolarina/' . $kandidat->id) }}">
                                <i class="fas fa-money-bill"></i> Школарина
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </form>
        <br>
        <hr>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">За одабране кандидате</h3>
            </div>
            <div class="panel-body">
                <div id="masovnaUplata" class="btn btn-primary">Уплатили школарину за следећу годину</div>
                <div id="masovniUpis" class="btn btn-success">Упис у следећу годину</div>
            </div>
        </div>
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
