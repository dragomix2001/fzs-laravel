@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Распоред часова</h2>
    
    <form method="GET" action="{{ route('raspored.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label>Студијски програм</label>
                <select name="studijski_program_id" class="form-control">
                    <option value="">-- Сви програми --</option>
                    @foreach($studijskiProgrami as $program)
                        <option value="{{ $program->id }}" {{ request('studijski_program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Семестар</label>
                <select name="semestar_id" class="form-control">
                    <option value="">-- Сви семестри --</option>
                    @foreach($semestri as $semestar)
                        <option value="{{ $semestar->id }}" {{ request('semestar_id') == $semestar->id ? 'selected' : '' }}>
                            {{ $semestar->naziv }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Школска година</label>
                <select name="skolska_godina_id" class="form-control">
                    @foreach($skolskeGodine as $godina)
                        <option value="{{ $godina->id }}" {{ request('skolska_godina_id') == $godina->id ? 'selected' : '' }}>
                            {{ $godina->godina }}/{{ $godina->godina + 1 }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block">Филтрирај</button>
            </div>
        </div>
    </form>

    @if($raspored->count() > 0)
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Дан</th>
                    <th>Време</th>
                    <th>Предмет</th>
                    <th>Професор</th>
                    <th>Облик наставе</th>
                    <th>Година</th>
                    <th>Просторија</th>
                    <th>Група</th>
                    <th>Акције</th>
                </tr>
            </thead>
            <tbody>
                @foreach($raspored as $r)
                <tr>
                    <td>
                        @switch($r->dan)
                            @case(1) Понедељак @break
                            @case(2) Уторак @break
                            @case(3) Среда @break
                            @case(4) Четвртак @break
                            @case(5) Петак @break
                            @case(6) Субота @break
                            @case(7) Недеља @break
                        @endswitch
                    </td>
                    <td>{{ \Carbon\Carbon::parse($r->vreme_od)->format('H:i') }} - {{ \Carbon\Carbon::parse($r->vreme_do)->format('H:i') }}</td>
                    <td>{{ $r->predmet->naziv ?? '-' }}</td>
                    <td>{{ $r->profesor->ime ?? '' }} {{ $r->profesor->prezime ?? '' }}</td>
                    <td>{{ $r->oblikNastave->naziv ?? '-' }}</td>
                    <td>{{ $r->godinaStudija->naziv ?? '-' }}</td>
                    <td>{{ $r->prostorija ?? '-' }}</td>
                    <td>{{ $r->grupa ?? '-' }}</td>
                    <td>
                        <a href="{{ route('raspored.edit', $r->id) }}" class="btn btn-sm btn-primary">Измени</a>
                        <form action="{{ route('raspored.destroy', $r->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Да ли сте сигурни?')">Обриши</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info mt-4">
            Нема унесених часова за изабране критеријуме.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('raspored.create') }}" class="btn btn-success">Додај час</a>
        <a href="{{ route('raspored.pregled') }}" class="btn btn-info">Преглед распореда</a>
    </div>
</div>
@endsection
