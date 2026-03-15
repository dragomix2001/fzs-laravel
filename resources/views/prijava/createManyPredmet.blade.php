@extends('layouts.layout')
@section('page_heading','Пријава испита за више кандидата')
@section('section')
    <div class="col-lg-9">
        <div id="messages">
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
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Пријава за полагање испита</h3>
            </div>
            <div class="panel-body">
                <form id="formaKandidatiOdabir" action="{{"/"}}prijava/predmetVise" method="post">
                    {{ csrf_field() }}

                    <input type="hidden" name="predmet_id" id="predmet_id_hidden" value="{{ $predmet->id }}">

                    <div class="form-group" style="width: 50%;">
                        <label for="predmet_display">Пријављујем се за полагање испита из предмета</label>
                        <input type="text" class="form-control" id="predmet_display" value="{{ $predmet->naziv }}" disabled>
                    </div>

                    <div class="clearfix"></div>
                    <hr>

                    <div class="form-group" style="width: 80%;">
                        <label for="profesor_id">Професор</label>
                        <select class="form-control auto-combobox" id="profesor_id" name="profesor_id">
                            <option value=""></option>
                            @foreach($profesor as $tip)
                                <option value="{{$tip->id}}">{{$tip->zvanje . " " .$tip->ime . " " . $tip->prezime}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="clearfix"></div>
                    <hr>
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
                            <label for="tipPrijave_id">Тип пријаве</label>
                            <select class="form-control" id="tipPrijave_id" name="tipPrijave_id">
                                @foreach($tipPrijave as $tip)
                                    <option value="{{$tip->id}}">{{$tip->naziv}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-lg-4">
                            <label for="formatDatum">Датум</label>
                            <input id="formatDatum" class="form-control dateMask" type="text" name="formatDatum"
                                   value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"/>
                        </div>
                        <div class="form-group col-lg-4">
                            <label for="formatDatum2">Датум (резервни)</label>
                            <input id="formatDatum2" class="form-control dateMask" type="text" name="formatDatum2"
                                   value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"/>
                        </div>
                    </div>

                    <input type="hidden" name="datum" id="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                    <input type="hidden" name="datum2" id="datum2" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                    <div class="clearfix"></div>
                    <hr>

                    <div class="row">
                        <div class="col-lg-5">
                            <div class="form-group">
                                <label for="studentSearch">Претрага студента ( број индекса )</label>
                                <input type="text" id="studentSearch" class="form-control" placeholder="Унесите број индекса за претрагу...">
                            </div>
                        </div>
                        <input type="hidden" id="addStudentList" name="addStudentList" value="">
                        <div class="col-lg-3" style="margin-top: 25px;">
                            <input type="button" value="Додај студента" name="button" id="addStudentButton" class="btn btn-success btn-block">
                        </div>
                    </div>
                    <table id="tabela" class="table">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Број индекса</th>
                            <th>Име и презиме</th>
                            <th>Година студија</th>
                        </tr>
                        </thead>
                        <tbody id="addStudentTableBody">
                        </tbody>
                    </table>
                    <hr>
                    <div class="form-group text-center">
                        {{--<input type="submit" name="Submit" value="Креирај пријаву" class="btn btn-lg btn-primary">--}}
                        <input type="submit" name="Submit2" value="Креирај пријаву и записник" class="btn btn-lg btn-success">
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
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script>
        var studenti = @json($kandidatiJson);
        
        $(document).ready(function () {
            var forma = $('#formaKandidatiOdabir');

            // Autocomplete za studente
            $("#studentSearch").autocomplete({
                source: function(request, response) {
                    var searchTerm = request.term.toLowerCase();
                    var matchedOptions = studenti.filter(function(s) {
                        return s.label.toLowerCase().indexOf(searchTerm) !== -1;
                    });
                    response(matchedOptions);
                },
                select: function(event, ui) {
                    $("#studentSearch").val(ui.item.label);
                    $("#addStudentList").val(ui.item.value);
                    return false;
                },
                minLength: 1
            });

            $('#addStudentButton').click(function () {
                addStudentToList();
                $('#studentSearch').val('');
                $('#addStudentList').val('');
            });

            $("#studentSearch").keypress(function(e){
                var k=e.keyCode || e.which;
                if(k==13){
                    e.preventDefault();
                    var selectedVal = $('#addStudentList').val();
                    if(selectedVal) {
                        addStudentToList();
                    }
                }
            });

            $(window).keypress(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                }
            });

            function addStudentToList(){
                var studentId = $('#addStudentList').val();
                if(!studentId) {
                    alert('Молимо изаберите студента из листе!');
                    return;
                }
                
                $.ajax({
                    url: '{{"/"}}prijava/vratiKandidataPoBroju',
                    type: 'post',
                    data: {
                        id: studentId,
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $("#tabela tr:last").after(result);
                        $('#studentSearch').val('');
                        $('#addStudentList').val('');
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            }

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

            var formatDatum2 = $("#formatDatum2");
            formatDatum2.datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum2",
                altFormat: "yy-mm-dd"
            });

            formatDatum2.on('input', function () {
                var date = moment(formatDatum2.val(), "dd.mm.yy");
                $("#datum2").val(date.format('YYYY-MM-DD'));
            });
        });

    </script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
