<title>Додавање статуса испита</title>
@extends('layouts.layout')
@section('page_heading','Додавање статуса испита')
@section('section')

    <div class="col-md-9">
        <form role="form" method="post" action="{{ url('/statusIspita/unos') }}">
            {{csrf_field()}}

            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">Додавање статуса испита</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <label for="naziv">Назив:</label>
                        <input name="naziv" type="text" class="form-control">
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                        <button type="submit" class="btn btn-primary">Додај</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection