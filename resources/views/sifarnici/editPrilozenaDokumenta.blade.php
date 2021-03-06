<title>Промени приложена документа</title>
@extends('layouts.layout')
@section('page_heading','Промени приложена документа')
@section('section')

    <div class="col-md-9">
        <form role="form" method="post" action="{{$putanja}}/prilozenaDokumenta/{{$dokument->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Промени приложена документа</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <label for="redniBrojDokumenta">Редни број:</label>
                        <input name="redniBrojDokumenta" type="text" class="form-control" value="{{$dokument->redniBrojDokumenta}}">
                    </div>
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <label for="naziv">Назив:</label>
                        <input name="naziv" type="text" class="form-control" value="{{$dokument->naziv}}">
                    </div>
                    <div class="form-group pull-left" style="width: 48%;  margin-right: 2%">
                        <label for="skolskaGodina_id">Школска година:</label>
                        <input type="hidden" id="godinaStudijaHidden" name="godinaStudijaHidden"
                               value="{{$dokument->skolskaGodina_id}}">
                        <select class="form-control" id="skolskaGodina_id" name="skolskaGodina_id">
                            @foreach($godinaStudija as $godinaStudija)
                                <option value="{{$godinaStudija->id}}">{{$godinaStudija->naziv}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <button type="submit" class="btn btn-primary">Измени</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

<script>
        $(document).ready(function () {
            //alert($("#godinaStudijaHidden").val());
            $("#skolskaGodina_id").val($("#godinaStudijaHidden").val());
        });
    </script>


@endsection