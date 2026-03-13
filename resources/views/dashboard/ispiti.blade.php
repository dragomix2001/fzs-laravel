@extends('layouts.layout')
@section('page_heading','Аналитика испита')
@section('section')

<div class="col-sm-12 col-lg-10">
<h2>Аналитика испита</h2>

    <form method="GET" action="{{ route('dashboard.ispiti') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label>Година</label>
                <select name="godina" class="form-control" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $godina == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </form>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Положени испити по месецима</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Месец</th>
                                <th>Број</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $meseci = ['', 'Јануар', 'Фебруар', 'Март', 'Април', 'Мај', 'Јун', 'Јул', 'Август', 'Септембар', 'Октобар', 'Новембар', 'Децембар'];
                            @endphp
                            @for($m = 1; $m <= 12; $m++)
                            <tr>
                                <td>{{ $meseci[$m] }}</td>
                                <td>{{ $polozeniPoMesecima->firstWhere('mesec', $m)->broj ?? 0 }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Пријаве испита по месецима</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Месец</th>
                                <th>Број</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($m = 1; $m <= 12; $m++)
                            <tr>
                                <td>{{ $meseci[$m] }}</td>
                                <td>{{ $prijavePoMesecima->firstWhere('mesec', $m)->broj ?? 0 }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Успех по предметима ({{ $godina }})</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Предмет</th>
                                <th>Укупно</th>
                                <th>Положено</th>
                                <th>Пролазност</th>
                                <th>Просечна оцена</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($uspehPoPredmetu as $up)
                            <tr>
                                <td>{{ $up->predmet->naziv ?? '-' }}</td>
                                <td>{{ $up->ukupno }}</td>
                                <td>{{ $up->polozeni }}</td>
                                <td>{{ $up->ukupno > 0 ? round(($up->polozeni / $up->ukupno) * 100, 1) : 0 }}%</td>
                                <td>{{ $up->prosek ? round($up->prosek, 2) : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Нема података</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">Назад</a>
    </div>
</div>
@endsection
