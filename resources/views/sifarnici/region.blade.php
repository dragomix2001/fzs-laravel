<title>Region</title>
@extends('layouts.layout')
@section('page_heading','Region')
@section('section')

    <div class="table-responsive">
        <table class="table">
            <thead>
            <th>
                Naziv
            </th>
            <th>
                Akcije
            </th>
            </thead>

            @foreach($region as $region)
                <tr>
                    <td>{{$region->naziv}}</td>
                    <td>
                        <div class="btn-group">
                            <form class="btn" action="region/edit/{{$region->id}}">
                                <input type="submit" class="btn btn-primary" value="Promeni">
                            </form>
                            <form class="btn" action="region/delete/{{$region->id}}">
                                <input type="submit" class="btn btn-primary" value="Izbriši">
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    <form role="form" method="post" action="{{ url('/region/unos') }}">
        {{csrf_field()}}

        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">Region</h3>
            </div>
            <div class="panel-body">
                <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                    <label for="naziv">Naziv:</label>
                    <input name="naziv" type="text" class="form-control">
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group pull-left" style="width: 48%; margin-right: 2%;">
                    <button type="submit" class="btn btn-primary">Dodaj</button>
                </div>
            </div>
        </div>
    </form>

@endsection