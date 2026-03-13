@extends('layouts.layout')
@section('page_heading','Додај обавештење')
@section('section')

<div class="col-sm-12 col-lg-10">
<h2>Додај обавештење</h2>
    
    <form method="POST" action="{{ route('obavestenja.store') }}">
        @csrf
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Наслов *</label>
                    <input type="text" name="naslov" class="form-control" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Тип *</label>
                    <select name="tip" class="form-control" required>
                        @foreach($tipovi as $key => $tip)
                            <option value="{{ $key }}">{{ $tip }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Садржај *</label>
            <textarea name="sadrzaj" class="form-control" rows="6" required></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Датум објаве *</label>
                    <input type="datetime-local" name="datum_objave" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Датум истека</label>
                    <input type="datetime-local" name="datum_isteka" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Професор</label>
                    <select name="profesor_id" class="form-control">
                        <option value="">-- Изаберите професора --</option>
                        @foreach($profesori as $profesor)
                            <option value="{{ $profesor->id }}">{{ $profesor->ime }} {{ $profesor->prezime }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="checkbox">
            <label>
                <input type="checkbox" name="aktivan" value="1" checked>
                Ативно
            </label>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Сачувај</button>
            <a href="{{ route('obavestenja.index') }}" class="btn btn-secondary">Откажи</a>
        </div>
    </form>
</div>
@endsection
