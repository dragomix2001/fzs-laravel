@extends('layouts.layout')

@section('section')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>{{ $obavestenje->naslov }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Тип:</strong> 
                        @switch($obavestenje->tip)
                            @case('opste') Опште @break
                            @case('ispit') Испит @break
                            @case('raspored') Распоред @break
                            @case('upis') Упис @break
                            @case('Ocena') Оцена @break
                            @case('stipendija') Стипендија @break
                            @default {{ $obavestenje->tip }}
                        @endswitch
                    </div>
                    <div class="mb-3">
                        <strong>Датум објаве:</strong> {{ \Carbon\Carbon::parse($obavestenje->datum_objave)->format('d.m.Y. H:i') }}
                    </div>
                    @if($obavestenje->datum_isteka)
                        <div class="mb-3">
                            <strong>Датум истека:</strong> {{ \Carbon\Carbon::parse($obavestenje->datum_isteka)->format('d.m.Y. H:i') }}
                        </div>
                    @endif
                    @if($obavestenje->profesor)
                        <div class="mb-3">
                            <strong>Професор:</strong> {{ $obavestenje->profesor->ime }} {{ $obavestenje->profesor->prezime }}
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>Статус:</strong>
                        @if($obavestenje->aktivan)
                            <span class="label label-success">Ативно</span>
                        @else
                            <span class="label label-default">Неактивно</span>
                        @endif
                    </div>
                    <hr>
                    <div class="obavestenje-sadrzaj">
                        {!! nl2br(e($obavestenje->sadrzaj)) !!}
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('obavestenja.index') }}" class="btn btn-secondary">Назад</a>
                    <a href="{{ route('obavestenja.edit', $obavestenje->id) }}" class="btn btn-primary">Измени</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
