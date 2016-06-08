<title>?????? ?????? ?????????</title>
@extends('layouts.layout')
@section('page_heading','?????? ?????? ?????????')
@section('section')

    <div class="col-md-9">
        <form role="form" method="post" action="/statusStudiranja/{{$statusStudiranja->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}


            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">?????? ?????? ?????????</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <label for="naziv">?????:</label>
                        <input name="naziv" type="text" class="form-control" value="{{$statusStudiranja->naziv}}">
                    </div>
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <div class="checkbox">
                            <label>
                                @if($statusStudiranja->indikatorAktivan == 1)
                                    <input name="indikatorAktivan" value="1" type="checkbox" checked="true">
                                @else
                                    <input name="indikatorAktivan" type="checkbox">
                                @endif
                                ???????</label>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <button type="submit" class="btn btn-primary">??????</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection