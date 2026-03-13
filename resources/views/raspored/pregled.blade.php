@extends('layouts.layout')

@section('section')
<div class="container">
    <h2>Преглед распореда часова</h2>
    
    <form method="GET" action="{{ route('raspored.pregled') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Школска година</label>
                <select name="skolska_godina_id" class="form-control">
                    @foreach($skolskeGodine as $godina)
                        <option value="{{ $godina->id }}">
                            {{ $godina->godina }}/{{ $godina->godina + 1 }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Прикажи</button>
            </div>
        </div>
    </form>

    <div class="row mt-4">
        @foreach($rasporedPoDanima as $dan => $data)
            @if($data['casovi']->count() > 0)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ $data['naziv'] }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Време</th>
                                        <th>Предмет</th>
                                        <th>Професор</th>
                                        <th>Облик</th>
                                        <th>Прост.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['casovi'] as $cas)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($cas->vreme_od)->format('H:i') }}<br>{{ \Carbon\Carbon::parse($cas->vreme_do)->format('H:i') }}</td>
                                        <td>
                                            {{ $cas->predmet->naziv ?? '-' }}
                                            @if($cas->grupa)
                                                <br><small class="text-muted">Група: {{ $cas->grupa }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cas->profesor->ime ?? '' }} {{ $cas->profesor->prezime ?? '' }}</td>
                                        <td>{{ $cas->oblikNastave->naziv ?? '-' }}</td>
                                        <td>{{ $cas->prostorija ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-4">
        <a href="{{ route('raspored.index') }}" class="btn btn-secondary">Назад на управљање</a>
    </div>
</div>
@endsection
