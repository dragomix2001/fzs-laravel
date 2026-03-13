@extends('layouts.layout')
@section('page_heading','Измени обавештење')
@section('section')

<div class="col-sm-12 col-lg-10">
<h2>Измени обавештење</h2>
    
    <form method="POST" action="{{ route('obavestenja.update', $obavestenje->id) }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>Наслов *</label>
                    <input type="text" name="naslov" class="form-control" value="{{ $obavestenje->naslov }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Тип *</label>
                    <select name="tip" class="form-control" required>
                        @foreach($tipovi as $key => $tip)
                            <option value="{{ $key }}" {{ $obavestenje->tip == $key ? 'selected' : '' }}>{{ $tip }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Садржај *</label>
            <textarea name="sadrzaj" class="form-control" rows="6" required>{{ $obavestenje->sadrzaj }}</textarea>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Датум објаве *</label>
                    <input type="datetime-local" name="datum_objave" class="form-control" value="{{ \Carbon\Carbon::parse($obavestenje->datum_objave)->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Датум истека</label>
                    <input type="datetime-local" name="datum_isteka" class="form-control" value="{{ $obavestenje->datum_isteka ? \Carbon\Carbon::parse($obavestenje->datum_isteka)->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Професор</label>
                    <select name="profesor_id" class="form-control">
                        <option value="">-- Изаберите професора --</option>
                        @foreach($profesori as $profesor)
                            <option value="{{ $profesor->id }}" {{ $obavestenje->profesor_id == $profesor->id ? 'selected' : '' }}>
                                {{ $profesor->ime }} {{ $profesor->prezime }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="checkbox">
            <label>
                <input type="checkbox" name="aktivan" value="1" {{ $obavestenje->aktivan ? 'checked' : '' }}>
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
