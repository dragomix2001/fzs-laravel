@extends('layouts.layout')
@section('page_heading','Претрага')
@section('section')
    <div class="col-lg-10">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Критеријум за претрагу</h3>
            </div>
            <div class="panel-body">
                <form role="form" method="get" action="{{ url('/pretraga') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Претрага студената ( име, презиме, број индекса, ЈМБГ )</label>
                                <input type="text" class="form-control" id="pretraga" name="pretraga" value="{{ $request->pretraga ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Претрага предмета (назив, шифра)</label>
                                <input type="text" class="form-control" id="pretraga_predmet" name="pretraga_predmet" value="{{ $request->pretraga_predmet ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Студијски програм</label>
                                <select class="form-control" name="studijski_program_id">
                                    <option value="">-- Сви програми --</option>
                                    @foreach($studijskiProgrami as $program)
                                        <option value="{{ $program->id }}" {{ isset($request->studijski_program_id) && $request->studijski_program_id == $program->id ? 'selected' : '' }}>
                                            {{ $program->naziv }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Година студија</label>
                                <select class="form-control" name="godina_studija_id">
                                    <option value="">-- Све године --</option>
                                    @foreach($godineStudija as $godina)
                                        <option value="{{ $godina->id }}" {{ isset($request->godina_studija_id) && $request->godina_studija_id == $godina->id ? 'selected' : '' }}>
                                            {{ $godina->naziv }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Статус</label>
                                <select class="form-control" name="status_upisa_id">
                                    <option value="">-- Сви статуси --</option>
                                    @foreach($statusi as $status)
                                        <option value="{{ $status->id }}" {{ isset($request->status_upisa_id) && $request->status_upisa_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->naziv }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Школска година</label>
                                <select class="form-control" name="skolska_godina_id">
                                    <option value="">-- Све године --</option>
                                    @foreach($skolskeGodine as $godina)
                                        <option value="{{ $godina->id }}" {{ isset($request->skolska_godina_id) && $request->skolska_godina_id == $godina->id ? 'selected' : '' }}>
                                            {{ $godina->naziv }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Тражи">
                        <a href="{{ url('/pretraga') }}" class="btn btn-default">Ресетуј</a>
                    </div>
                </form>
            </div>
        </div>
        
        @if(isset($studenti) || isset($predmeti))
            @if(isset($studenti) && $studenti->count() > 0)
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Резултати претраге - Студенти ({{ $studenti->count() }})</h3>
                </div>
                <div class="panel-body">
                    <table id="tabela" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Број Индекса</th>
                                <th>Име</th>
                                <th>Презиме</th>
                                <th>Програм</th>
                                <th>Година</th>
                                <th>Статус</th>
                                <th>Акције</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studenti as $kandidat)
                                <tr>
                                    <td>{{$kandidat->brojIndeksa}}</td>
                                    <td>{{$kandidat->imeKandidata}}</td>
                                    <td>{{$kandidat->prezimeKandidata}}</td>
                                    <td>{{ optional($kandidat->program)->naziv }}</td>
                                    <td>{{ optional($kandidat->godinaStudija)->naziv }}</td>
                                    <td>{{ optional($kandidat->statusUpisa)->naziv }}</td>
                                    <td>
                                        <a class="btn btn-warning btn-sm" href="{{$putanja}}/{{ $kandidat->tipStudija_id == 1 ? 'kandidat' : 'master' }}/{{ $kandidat->id }}/edit">
                                            <span class="fa fa-edit"></span>
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{$putanja}}/student/{{ $kandidat->id }}/upis">
                                            Статус
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{$putanja}}/prijava/zaStudenta/{{ $kandidat->id }}">
                                            Испити
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{$putanja}}/izvestaji/potvrdeStudent/{{$kandidat->id}}">
                                            Потврде
                                        </a>
                                        <a class="btn btn-primary btn-sm" href="{{$putanja}}/skolarina/{{$kandidat->id}}">
                                            Школарина
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(isset($studenti))
            <div class="alert alert-info">
                Нема резултата за претрагу студената.
            </div>
            @endif
            
            @if(isset($predmeti) && $predmeti->count() > 0)
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Резултати претраге - Предмети ({{ $predmeti->count() }})</h3>
                </div>
                <div class="panel-body">
                    <table id="tabela2" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Шифра</th>
                                <th>Назив</th>
                                <th>ЕСПБ</th>
                                <th>Акције</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($predmeti as $predmet)
                                <tr>
                                    <td>{{$predmet->sifraPredmeta}}</td>
                                    <td>{{$predmet->naziv}}</td>
                                    <td>{{$predmet->espb}}</td>
                                    <td>
                                        <a class="btn btn-warning btn-sm" href="{{$putanja}}/predmet/{{ $predmet->id }}/edit">
                                            <span class="fa fa-edit"></span> Измени
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(isset($predmeti))
            <div class="alert alert-info">
                Нема резултата за претрагу предмета.
            </div>
            @endif
        @endif
    </div>
@endsection
