@extends('layouts.layout')
@section('page_heading','Записник о полагању испита')
@section('section')
    <div class="col-lg-10">
        {{-- GRESKE --}}
        @if (Session::get('errors'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <h4>Грешка!</h4>
                <ul>
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (Session::get('flash-error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong>Грешка!</strong>
                @if(Session::get('flash-error') === 'create')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @endif
            </div>
        @endif
        <div class="card border-primary mb-3">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Записник о полагању испита</h3>
            </div>
            <div class="card-body">
                <form role="form" method="post" action="{{ url('/zapisnik/storeZapisnik') }}">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="form-group col-lg-5">
                            <label for="rok_id">Испитни рок</label>
                            <select class="form-control" id="rok_id"
                                    name="rok_id">
                                @if(!empty($aktivniIspitniRok))
                                    @foreach($aktivniIspitniRok as $tip)
                                        <option value="{{$tip->id}}" {{ (!empty($rok_id) && $rok_id == $tip->id) ? 'selected' : '' }}>{{$tip->naziv}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-lg-5">
                            <label for="predmet_id">Предмет</label>
                            <select class="form-control" id="predmet_id"
                                    name="predmet_id">
                                @foreach($predmeti as $item)
                                    <option value="{{$item->id}}">{{ $item->naziv }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-lg-5">
                            <label for="profesor_id">Професор</label>
                            <select class="form-control" id="profesor_id"
                                    name="profesor_id">
                                @foreach($profesori as $item)
                                    <option value="{{$item->id}}">{{ $item->ime . " " . $item->prezime }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="ajaxSubmitPrijava">&nbsp;</label><br>
                            <button type="button" id="ajaxSubmitPrijava" class="btn btn-success w-100">
                                <i class="fas fa-search"></i> Прикажи студенте
                            </button>
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="addStudentLink">&nbsp;</label><br>
                            <button type="button" id="addStudentLink" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i> Додај студента
                            </button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <hr>

                    <input type="hidden" id="prijavaIspita_id" name="prijavaIspita_id" value="">

                    <input type="hidden" id="datum" name="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                    <input type="hidden" id="datum2" name="datum2" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                    <h3>Студенти који су пријавили испит у испитном року</h3>

                    <div class="clearfix"></div>
                    <hr>
                    <div class="row">
                        <div class="form-group col-lg-3">
                            <label for="formatDatum">Датум</label>
                            <input type="text" id="formatDatum" name="formatDatum" class="form-control dateMask"
                                   value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="formatDatum">Датум 2</label>
                            <input type="text" id="formatDatum2" name="formatDatum2" class="form-control dateMask"
                                   value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="vreme">Време</label>
                            <input type="text" id="vreme" name="vreme" class="form-control">
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="ucionica">Учионица</label>
                            <input type="text" id="ucionica" name="ucionica" class="form-control">
                        </div>
                    </div>

                    <table id="tabela" class="table table-striped table-hover">
                        <thead class="table-dark">
                        <tr>
                            <th>Полагао</th>
                            <th>Број Индекса</th>
                            <th>Име и презиме</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <div id="messageEmpty">
                    </div>

                    <div class="form-group text-center mt-4">
                        <button type="submit" name="Submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Сачувај
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            console.log('Document ready - jQuery version: ' + $.fn.jquery);
            
            $('#addStudentLink').click(function(){
                console.log('Add student clicked, predmet_id: ' + $('#predmet_id').val());
                window.location = "/prijava/zaPredmet/" + $('#predmet_id').val();
            });

            $('#rok_id').change(function () {
                console.log('Rok changed');
                var rok = $('#rok_id');
                $.ajax({
                    url: '/zapisnik/vratiZapisnikPredmet',
                    method: 'get',
                    data: {
                        rokId: rok.val()
                    },
                    success: function (result) {
                        console.log('Predmeti loaded:', result);
                        var selectList = $('#predmet_id');
                        selectList.empty();
                        $.each(result['predmeti'], function () {
                            selectList.append($("<option />").val(this.id).text(this.naziv));
                        });
                        selectList = $('#profesor_id');
                        selectList.empty();
                        $.each(result['profesori'], function () {
                            selectList.append($("<option />").val(this.id).text(this.ime + ' ' + this.prezime));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading predmeti:', error);
                    }
                });
            });

            $('#ajaxSubmitPrijava').click(function () {
                console.log('Show students clicked');
                var rok = $('#rok_id').val();
                var predmet = $('#predmet_id').val();
                var profesor = $('#profesor_id').val();
                
                console.log('rok:', rok, 'predmet:', predmet, 'profesor:', profesor);
                
                $.ajax({
                    url: '/zapisnik/vratiZapisnikStudenti',
                    method: 'get',
                    data: {
                        rok_id: rok,
                        predmet_id: predmet,
                        profesor_id: profesor
                    },
                    success: function (result) {
                        console.log('Studenti loaded:', result);

                        if(result['message'].length > 0){
                            $('#messageEmpty').html(result['message']);
                        }else{
                            $('#messageEmpty').html("");
                        }
                        $("#tabela tbody").empty();
                        $.each(result['kandidati'], function (e) {
                            $('#tabela tbody').append('<tr><td>' + '<input type="checkbox" name="odabir[' + this.id + ']" value="' + this.id + '" checked>' +
                                    '</td><td>' + this.brojIndeksa +
                                    '</td><td>' + this.imeKandidata + ' ' + this.prezimeKandidata + '</td></tr>');
                        });
                        $('#prijavaIspita_id').val(result['prijavaId']);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading studenti:', error);
                    }
                });
            });

            // Datepicker
            $("#formatDatum").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum",
                altFormat: "yy-mm-dd"
            });

            $("#formatDatum2").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#datum2",
                altFormat: "yy-mm-dd"
            });

            // Prevent form submission on Enter
            $(window).keydown(function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endsection