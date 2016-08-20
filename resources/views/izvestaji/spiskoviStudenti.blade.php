<title>Извештаји</title>
@extends('layouts.layout')
@section('page_heading','Извештаји')
@section('section')

    <div class="col-sm-12 col-lg-12">

        <div class="col-sm-12 col-lg-4">
            <form role="form" target="_blank" method="post" action="{{ url('/izvestaji/spisakZaSmer/') }}">
                {{csrf_field()}}

                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h3 class="panel-title">Списак студената по смеровима</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group pull-left" style="width: 70%;  margin-right: 2%">
                            <label for="program">Студијски програм:</label>
                            <select class="form-control" id="program" name="program">
                                @foreach($program as $program)
                                    <option value="{{$program->id}}">{{$program->naziv}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group pull-left" style="width: 30%;  margin-right: 2%">
                            <label for="godina">Година студија:</label>
                            <select class="form-control" id="godina" name="godina">
                                @foreach($godina as $godina)
                                    <option value="{{$godina->id}}">{{$godina->naziv}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                            <button type="submit" class="btn btn-primary">Штампај</button>
                        </div>
                        <div class="form-group pull-left" style="width: 25%;">
                            <a class="btn btn-primary pull-left" target="_blank" href="{{$putanja}}/izvestaji/spisakPoSmerovimaAktivni">Сви</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-sm-12 col-lg-4">
            <form role="form" method="post" target="_blank" action="{{ url('/izvestaji/spisakPoPredmetima/') }}">
                {{csrf_field()}}

                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h3 class="panel-title">Списак студената по предметима</h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group pull-left" style="width: 70%;  margin-right: 2%">
                            <label for="predmet">Предмет:</label>
                            <select class="form-control auto-combobox" id="predmet" name="predmet">
                                @foreach($predmeti as $predmet)
                                    <option value="{{$predmet->id}}">{{$predmet->naziv}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                            <button type="submit" class="btn btn-primary">Штампај</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript" src="{{ $putanja }}/js/jquery-ui-autocomplete.js"></script>

@endsection