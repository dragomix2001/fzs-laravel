@extends('layouts.layout')
@section('page_heading','Аналитика и статистика')
@section('section')

<div class="col-sm-12 col-lg-10">
<div class="row">
    <div class="col-md-8">
        <h2>Аналитика и статистика</h2>
    </div>
    <div class="col-md-4 text-right">
        <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#widgetSettings">
            <span class="fa fa-cog"></span> Виџети
        </button>
    </div>
</div>

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
        @if($widgets['studenti_ukupno'])
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Укупно студената</h5>
                    <h2 class="mb-0">{{ $ukupnoStudenata }}</h2>
                </div>
            </div>
        </div>
        @endif
        @if($widgets['polozeni_ispiti'])
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Положени испити</h5>
                    <h2 class="mb-0">{{ $polozeniIspiti }}</h2>
                </div>
            </div>
        </div>
        @endif
        @if($widgets['prijavljeni_ispiti'])
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Пријављени испити</h5>
                    <h2 class="mb-0">{{ $prijavljeniIspiti }}</h2>
                </div>
            </div>
        </div>
        @endif
        @if($widgets['aktivna_obavestenja'])
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Активна обавештења</h5>
                    <h2 class="mb-0">{{ $aktivnaObavestenja }}</h2>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-4">
        @if($widgets['studenti_po_programu'])
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
                                <td>{{ $sp->program->naziv ?? '-' }}</td>
                                <td>{{ $sp->broj }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        
        @if($widgets['studenti_po_godini'])
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
        @endif
    </div>

    <div class="row mt-4">
        @if($widgets['prolaznost'])
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
        @endif
        
        @if($widgets['neuspesni_predmeti'])
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
        @endif
    </div>
    <div class="mt-4">
        <a href="{{ route('dashboard.studenti') }}" class="btn btn-primary">Детаљни преглед студената</a>
        <a href="{{ route('dashboard.ispiti') }}" class="btn btn-info">Аналитика испита</a>
    </div>
</div>

<div class="modal fade" id="widgetSettings" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('dashboard.widgets') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Подешавање виџета</h4>
                </div>
                <div class="modal-body">
                    <div class="checkbox">
                        <label><input type="checkbox" name="studenti_ukupno" {{ $widgets['studenti_ukupno'] ? 'checked' : '' }}> Укупно студената</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="polozeni_ispiti" {{ $widgets['polozeni_ispiti'] ? 'checked' : '' }}> Положени испити</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="prijavljeni_ispiti" {{ $widgets['prijavljeni_ispiti'] ? 'checked' : '' }}> Пријављени испити</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="aktivna_obavestenja" {{ $widgets['aktivna_obavestenja'] ? 'checked' : '' }}> Активна обавештења</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="studenti_po_programu" {{ $widgets['studenti_po_programu'] ? 'checked' : '' }}> Студенти по програму</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="studenti_po_godini" {{ $widgets['studenti_po_godini'] ? 'checked' : '' }}> Студенти по години</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="prolaznost" {{ $widgets['prolaznost'] ? 'checked' : '' }}> Пролазност</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="neuspesni_predmeti" {{ $widgets['neuspesni_predmeti'] ? 'checked' : '' }}> Неуспешни предмети</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Сачувај</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Откажи</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
