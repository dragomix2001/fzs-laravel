@extends('layouts.layout')

@section('section')
<div class="container">
    <h2>Аналитика и статистика</h2>

    <form method="GET" action="{{ route('dashboard.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Школска година</label>
                <select name="skolska_godina_id" class="form-control" onchange="this.form.submit()">
                    @foreach($skolskeGodine as $godina)
                        <option value="{{ $godina->id }}" {{ $skolskaGodinaId == $godina->id ? 'selected' : '' }}>
                            {{ $godina->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Укупно студената</h5>
                    <h2 class="mb-0">{{ $ukupnoStudenata }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Положени испити</h5>
                    <h2 class="mb-0">{{ $polozeniIspiti }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Пријављени испити</h5>
                    <h2 class="mb-0">{{ $prijavljeniIspiti }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Активна обавештења</h5>
                    <h2 class="mb-0">{{ $aktivnaObavestenja }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Студенти по студијском програму</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Програм</th>
                                <th>Број</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentiPoProgramu as $sp)
                            <tr>
                                <td>{{ $sp->studijskiProgram->naziv ?? '-' }}</td>
                                <td>{{ $sp->broj }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Студенти по години уписа</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Година</th>
                                <th>Број</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentiPoGodini as $sg)
                            <tr>
                                <td>{{ $sg->godinaUpisa->godina ?? '-' }}/{{ ($sg->godinaUpisa->godina ?? 0) + 1 }}</td>
                                <td>{{ $sg->broj }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>Пролазност</h5>
                </div>
                <div class="card-body text-center">
                    <h1>{{ $prolaznost }}%</h1>
                    <p class="text-muted">Положено / Пријављено</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Најчешће неуспешни предмети (тренутна година)</h5>
                </div>
                <div class="card-body">
                    @if($najcesciNeuspesni->count() > 0)
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Предмет</th>
                                    <th>Број неуспешних</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($najcesciNeuspesni as $np)
                                <tr>
                                    <td>{{ $np->predmet->naziv ?? '-' }}</td>
                                    <td>{{ $np->broj }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Нема података</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('dashboard.studenti') }}" class="btn btn-primary">Детаљни преглед студената</a>
        <a href="{{ route('dashboard.ispiti') }}" class="btn btn-info">Аналитика испита</a>
    </div>
</div>
@endsection
