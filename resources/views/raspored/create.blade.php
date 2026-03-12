@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Додај час у распоред</h2>
    
    <form method="POST" action="{{ route('raspored.store') }}">
        @csrf
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Предмет *</label>
                    <select name="predmet_id" class="form-control" required>
                        <option value="">-- Изаберите предмет --</option>
                        @foreach($predmeti as $predmet)
                            <option value="{{ $predmet->id }}">{{ $predmet->naziv }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Професор *</label>
                    <select name="profesor_id" class="form-control" required>
                        <option value="">-- Изаберите професора --</option>
                        @foreach($profesori as $profesor)
                            <option value="{{ $profesor->id }}">{{ $profesor->ime }} {{ $profesor->prezime }}</option>
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
                        <option value="">-- Изаберите програм --</option>
                        @foreach($studijskiProgrami as $program)
                            <option value="{{ $program->id }}">{{ $program->naziv }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Година студија *</label>
                    <select name="godina_studija_id" class="form-control" required>
                        <option value="">-- Изаберите годину --</option>
                        @foreach($godineStudija as $godina)
                            <option value="{{ $godina->id }}">{{ $godina->naziv }}</option>
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
                        <option value="">-- Изаберите семестар --</option>
                        @foreach($semestri as $semestar)
                            <option value="{{ $semestar->id }}">{{ $semestar->naziv }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Школска година *</label>
                    <select name="skolska_godina_id" class="form-control" required>
                        @foreach($skolskeGodine as $godina)
                            <option value="{{ $godina->id }}" {{ $godina->aktivan ? 'selected' : '' }}>
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
                        <option value="">-- Изаберите облик --</option>
                        @foreach($obliciNastave as $oblik)
                            <option value="{{ $oblik->id }}">{{ $oblik->naziv }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Дан *</label>
                    <select name="dan" class="form-control" required>
                        <option value="">-- Изаберите дан --</option>
                        <option value="1">Понедељак</option>
                        <option value="2">Уторак</option>
                        <option value="3">Среда</option>
                        <option value="4">Четвртак</option>
                        <option value="5">Петак</option>
                        <option value="6">Субота</option>
                        <option value="7">Недеља</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Време од *</label>
                    <input type="time" name="vreme_od" class="form-control" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Време до *</label>
                    <input type="time" name="vreme_do" class="form-control" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Просторија</label>
                    <input type="text" name="prostorija" class="form-control" placeholder="нпр. Сала 1">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Група</label>
                    <input type="text" name="grupa" class="form-control" placeholder="нпр. А, Б">
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
