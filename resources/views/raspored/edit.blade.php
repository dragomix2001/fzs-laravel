@extends('layouts.layout')

@section('section')
<div class="container">
    <h2>Измени час у распореду</h2>
    
    <form method="POST" action="{{ route('raspored.update', $raspored->id) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Предмет *</label>
                    <select name="predmet_id" class="form-control" required>
                        @foreach($predmeti as $predmet)
                            <option value="{{ $predmet->id }}" {{ $raspored->predmet_id == $predmet->id ? 'selected' : '' }}>
                                {{ $predmet->naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Професор *</label>
                    <select name="profesor_id" class="form-control" required>
                        @foreach($profesori as $profesor)
                            <option value="{{ $profesor->id }}" {{ $raspored->profesor_id == $profesor->id ? 'selected' : '' }}>
                                {{ $profesor->ime }} {{ $profesor->prezime }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Студијски програм *</label>
                    <select name="studijski_program_id" class="form-control" required>
                        @foreach($studijskiProgrami as $program)
                            <option value="{{ $program->id }}" {{ $raspored->studijski_program_id == $program->id ? 'selected' : '' }}>
                                {{ $program->naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Година студија *</label>
                    <select name="godina_studija_id" class="form-control" required>
                        @foreach($godineStudija as $godina)
                            <option value="{{ $godina->id }}" {{ $raspored->godina_studija_id == $godina->id ? 'selected' : '' }}>
                                {{ $godina->naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Семестар *</label>
                    <select name="semestar_id" class="form-control" required>
                        @foreach($semestri as $semestar)
                            <option value="{{ $semestar->id }}" {{ $raspored->semestar_id == $semestar->id ? 'selected' : '' }}>
                                {{ $semestar->naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Школска година *</label>
                    <select name="skolska_godina_id" class="form-control" required>
                        @foreach($skolskeGodine as $godina)
                            <option value="{{ $godina->id }}" {{ $raspored->skolska_godina_id == $godina->id ? 'selected' : '' }}>
                                {{ $godina->godina }}/{{ $godina->godina + 1 }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Облик наставе *</label>
                    <select name="oblik_nastave_id" class="form-control" required>
                        @foreach($obliciNastave as $oblik)
                            <option value="{{ $oblik->id }}" {{ $raspored->oblik_nastave_id == $oblik->id ? 'selected' : '' }}>
                                {{ $oblik->naziv }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Дан *</label>
                    <select name="dan" class="form-control" required>
                        <option value="1" {{ $raspored->dan == 1 ? 'selected' : '' }}>Понедељак</option>
                        <option value="2" {{ $raspored->dan == 2 ? 'selected' : '' }}>Уторак</option>
                        <option value="3" {{ $raspored->dan == 3 ? 'selected' : '' }}>Среда</option>
                        <option value="4" {{ $raspored->dan == 4 ? 'selected' : '' }}>Четвртак</option>
                        <option value="5" {{ $raspored->dan == 5 ? 'selected' : '' }}>Петак</option>
                        <option value="6" {{ $raspored->dan == 6 ? 'selected' : '' }}>Субота</option>
                        <option value="7" {{ $raspored->dan == 7 ? 'selected' : '' }}>Недеља</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Време од *</label>
                    <input type="time" name="vreme_od" class="form-control" value="{{ $raspored->vreme_od }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Време до *</label>
                    <input type="time" name="vreme_do" class="form-control" value="{{ $raspored->vreme_do }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Просторија</label>
                    <input type="text" name="prostorija" class="form-control" value="{{ $raspored->prostorija }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Група</label>
                    <input type="text" name="grupa" class="form-control" value="{{ $raspored->grupa }}">
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Сачувај</button>
            <a href="{{ route('raspored.index') }}" class="btn btn-secondary">Откажи</a>
        </div>
    </form>
</div>
@endsection
