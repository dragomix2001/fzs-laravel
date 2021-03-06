@extends('layouts.layout')
@section('page_heading','Пријава за полагање испита')
@section('section')
    <div class="col-lg-10">
        {{--GRESKE--}}
        @if (Session::get('errors'))
            <div class="alert alert-dismissable alert-danger">
                <h4>Грешка!</h4>
                <ul>
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (Session::get('flash-error'))
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Грешка!</strong>
                @if(Session::get('flash-error') === 'create')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @endif
            </div>
        @endif
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Пријава за полагање испита</h3>
            </div>
            <div class="panel-body">
                <form role="form" method="post" action="{{ url('/prijava/') }}">
                    {{ csrf_field() }}
                    @if(!empty($kandidat))
                        <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">
                        <input type="hidden" name="tipStudija_id" id="tipStudija_id"
                               value="{{ $kandidat->tipStudija_id }}">
                        <input type="hidden" name="studijskiProgram_id" id="studijskiProgram_id"
                               value="{{ $kandidat->studijskiProgram_id }}">

                        {{--<div class="form-group" style="width: 30%;">--}}
                        {{--<label for="brojIndeksa">Број Индекса</label>--}}
                        {{--<input id="brojIndeksa" class="form-control" type="text" name="brojIndeksa"--}}
                        {{--value="{{ $kandidat->brojIndeksa }}" />--}}
                        {{--</div>--}}

                        <div class="form-group pull-left" style="width: 30%;">
                            <label for="brojIndeksa">Број Индекса</label>
                            <select class="form-control auto-combobox" id="brojIndeksa" name="brojIndeksa">
                                @foreach($brojeviIndeksa as $item)
                                    <option value="{{ $item->id }}" {{ ($kandidat->studijskiProgram_id == $item->id ? "selected":"") }}>{{ $item->naziv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group pull-left" style="margin-right: 50%">
                            <label for="asdasd">&nbsp;</label><br>
                            <button type="button" name="" id="asdasd" class="btn btn-success"><span
                                        style="font-size: 20px" class="fa fa-check"></span></button>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-5">
                                <label for="jmbg">ЈМБГ</label>
                                <input id="jmbg" class="form-control" type="text" name="jmbg"
                                       value="{{ $kandidat->jmbg }}" disabled/>
                            </div>

                            <div class="form-group col-lg-6">
                                <label for="StudijskiProgram">Студијски програм</label>
                                <select class="form-control" id="StudijskiProgram" name="StudijskiProgram" disabled>
                                    @foreach($studijskiProgram as $item)
                                        <option value="{{ $item->id }}" {{ ($kandidat->studijskiProgram_id == $item->id ? "selected":"") }}>{{ $item->naziv }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-5">
                                <label for="imeKandidata">Име</label>
                                <input id="imeKandidata" class="form-control" type="text" name="imeKandidata"
                                       value="{{ $kandidat->imeKandidata }}" disabled/>
                            </div>

                            <div class="form-group col-lg-5">
                                <label for="prezimeKandidata">Презиме</label>
                                <input id="prezimeKandidata" class="form-control" type="text" name="prezimeKandidata"
                                       value="{{ $kandidat->prezimeKandidata }}" disabled/>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr>
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label for="predmet_id">Пријављујем се за полагање испита из предмета</label>
                                <select class="form-control" id="predmet_id" name="predmet_id">
                                    @foreach($predmeti as $item)
                                        <option value="{{ $item->id }}">{{ "Семестар " . $item->semestar . ': ' . $item->predmet->naziv }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-4">
                                <label for="tipPredmeta_id">Тип предмета:</label>
                                <select class="form-control" id="tipPredmeta_id" name="tipPredmeta_id" disabled>
                                    @foreach($tipPredmeta as $tip)
                                        <option value="{{$tip->id}}">{{$tip->naziv}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="godinaStudija_id">Година студија</label>
                                <select class="form-control" id="godinaStudija_id" name="godinaStudija_id" disabled>
                                    @foreach($godinaStudija as $item)
                                        <option value="{{ $item->id }}" {{ ($kandidat->godinaStudija_id == $item->id ? "selected":"") }}>{{ $item->naziv }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-lg-5">
                                <label for="tipStudija_id">Тип студија:</label>
                                <select class="form-control" id="tipStudija_id" name="tipStudija_id" disabled>
                                    @foreach($tipStudija as $tip)
                                        <option value="{{$tip->id}}" {{ ($kandidat->tipStudija_id == $tip->id ? "selected":"") }}>{{$tip->naziv}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <hr>

                        <div class="row">
                            <div class="form-group col-lg-8">
                                <label for="profesor_id">Професор</label>
                                <select class="form-control" id="profesor_id" name="profesor_id">
                                    @foreach($profesor as $tip)
                                        <option value="{{$tip->id}}">{{$tip->zvanje . " " .$tip->ime . " " . $tip->prezime}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{--//--}}
                        {{--KANDIDAT KRAJ--}}
                        {{--//--}}
                    @else
                        {{--//--}}
                        {{--PREDMET POCETAK--}}
                        {{--//--}}
                        <input type="hidden" name="prijava_za_predmet" value="1">
                        <input type="hidden" name="kandidat_id" id="kandidat_id" value="">
                        <input type="hidden" name="tipStudija_id" id="tipStudija_id"
                               value="{{ $predmet->tipStudija_id }}">
                        <input type="hidden" name="studijskiProgram_id" id="studijskiProgram_id"
                               value="{{ $predmet->studijskiProgram_id }}">

                        <div class="form-group pull-left" style="width: 30%;">
                            <label for="brojIndeksa">Број Индекса</label>
                            <select class="form-control auto-combobox" id="brojIndeksa" name="brojIndeksa">
                                <option value=""></option>
                                @foreach($brojeviIndeksa as $item)
                                    <option value="{{ $item->id }}">{{ $item->naziv }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group pull-left" style="margin-right: 50%">
                            <label for="asdasd">&nbsp;</label><br>
                            <button type="button" name="" id="asdasd" class="btn btn-success"><span
                                        style="font-size: 20px" class="fa fa-check"></span></button>
                        </div>

                        <div class="form-group pull-left" style="width: 30%; margin-right: 2%">
                            <label for="jmbg">ЈМБГ</label>
                            <input id="jmbg" class="form-control" type="text" name="jmbg" value="" disabled/>
                        </div>

                        <div class="form-group pull-left" style="width: 50%;">
                            <label for="StudijskiProgram">Студијски програм</label>
                            <select class="form-control" id="StudijskiProgram" name="StudijskiProgram" disabled>
                                @foreach($studijskiProgram as $item)
                                    <option value="{{ $item->id }}"{{ ($predmet->program->id == $item->id ? "selected":"") }}>{{ $item->naziv }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group pull-left" style="width: 40%; margin-right: 2%">
                            <label for="imeKandidata">Име</label>
                            <input id="imeKandidata" class="form-control" type="text" name="imeKandidata" value=""
                                   disabled/>
                        </div>

                        <div class="form-group pull-left" style="width: 40%;">
                            <label for="prezimeKandidata">Презиме</label>
                            <input id="prezimeKandidata" class="form-control" type="text" name="prezimeKandidata"
                                   value="" disabled/>
                        </div>

                        <div class="clearfix"></div>
                        <hr>

                        <div class="form-group" style="width: 50%;">
                            <label for="predmet_id">Пријављујем се за полагање испита из предмета</label>
                            <select class="form-control" id="predmet_id" name="predmet_id">
                                <option value="{{ $predmet->id }}">{{ "Семестар " . $predmet->semestar . ': ' . $predmet->predmet->naziv }}</option>
                            </select>
                        </div>

                        <div class="form-group pull-left" style="width: 40%;  margin-right: 2%">
                            <label for="tipPredmeta_id">Тип предмета:</label>
                            <select class="form-control" id="tipPredmeta_id" name="tipPredmeta_id" disabled>
                                @foreach($tipPredmeta as $tip)
                                    <option value="{{$tip->id}}" {{ ($predmet->tipPredmeta_id == $tip->id ? "selected":"") }}>{{$tip->naziv}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group pull-left" style="width: 10%; margin-right: 2%;">
                            <label for="godinaStudija_id">Година студија</label>
                            <select class="form-control" id="godinaStudija_id" name="godinaStudija_id" disabled>
                                @foreach($godinaStudija as $item)
                                    <option value="{{ $item->id }}" {{ ($predmet->godinaStudija_id == $item->id ? "selected":"") }}>{{ $item->naziv }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group pull-left" style="width: 40%;">
                            <label for="tipStudija_id">Тип студија:</label>
                            <select class="form-control" id="tipStudija_id" name="tipStudija_id" disabled>
                                <option value="{{$predmet->tipStudija_id}}">{{$predmet->tipStudija->naziv}}</option>
                            </select>
                        </div>

                        <div class="clearfix"></div>
                        <hr>

                        <div class="form-group" style="width: 80%;">
                            <label for="profesor_id">Професор</label>
                            <select class="form-control" id="profesor_id" name="profesor_id">
                                @foreach($profesor as $tip)
                                    <option value="{{$tip->id}}">{{$tip->zvanje . " " .$tip->ime . " " . $tip->prezime}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif


                    <div class="row">
                        <div class="form-group col-lg-4">
                            <label for="rok_id">Испитни рок</label>
                            <select class="form-control" id="rok_id" name="rok_id">
                                @foreach($ispitniRok as $tip)
                                    <option value="{{$tip->id}}">{{$tip->naziv}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-4">
                            <label for="brojPolaganja">Ипит полажем (редни број полагања)</label>
                            <input id="brojPolaganja" class="form-control" type="text" name="brojPolaganja" value="1"/>
                        </div>

                        <div class="form-group col-lg-4">
                            <label for="formatDatum">Датум</label>
                            <input id="formatDatum" class="form-control dateMask" type="text" name="formatDatum"
                                   value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"/>
                        </div>
                    </div>

                    <input type="hidden" name="datum" id="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                    <div class="clearfix"></div>
                    <hr>


                    <div class="form-group text-center">
                        <button type="submit" name="Submit" class="btn btn-success btn-lg"><span
                                    class="fa fa-save"></span> Сачувај
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <script>
        $(function () {
            var formatDatum = $("#formatDatum");
            formatDatum.datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum",
                altFormat: "yy-mm-dd"
            });

            formatDatum.on('input', function () {
                var date = moment(formatDatum.val(), "dd.mm.yy");
                $("#datum").val(date.format('YYYY-MM-DD'));
            });


            $(window).keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        $(document).ready(function () {
            var pathname = window.location.pathname;
            $('#asdasd').click(function () {
                $.ajax({
                    url: '{{$putanja}}/prijava/vratiKandidataPrijava',
                    type: 'post',
                    data: {
                        id: $('#brojIndeksa').val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        //Azuriranje Studenta
                        $('#kandidat_id').val(result['student'].id);
                        $('#jmbg').val(result['student'].jmbg);
                        $('#imeKandidata').val(result['student'].imeKandidata);
                        $('#prezimeKandidata').val(result['student'].prezimeKandidata);
                        $('#studijskiProgram_id').val(result['student'].studijskiProgram_id);

                        //Azuriranje Liste Predmeta
                        if (pathname.indexOf('/prijava/predmet/') == -1) {
                            $('#predmet_id').html(result['predmeti']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            });

            $('#predmet_id').change(function () {
                $.ajax({
                    url: '{{$putanja}}/prijava/vratiPredmetPrijava',
                    type: 'post',
                    data: {
                        id: $('#predmet_id').val(),
                        kandidat: $('#kandidat_id').val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $('#tipPredmeta_id').val(result['tipPredmeta']);
                        $('#godinaStudija_id').val(result['godinaStudija']);
                        $('#tipStudija_id').val(result['tipStudija']);
                        $('#profesor_id').html(result['profesori']);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            });
        });
    </script>
    <script type="text/javascript" src="{{ $putanja }}/js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{ $putanja }}/js/dateMask.js"></script>
@endsection