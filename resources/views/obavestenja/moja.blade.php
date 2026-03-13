@extends('layouts.layout')
@section('page_heading','Моја обавештења')
@section('section')

<div class="col-sm-12 col-lg-10">
<h2>Моја обавештења</h2>

    @if($obavestenja->count() > 0)
        <div class="timeline mt-4">
            @foreach($obavestenja as $obavestenje)
                <div class="timeline-item mb-4">
                    <div class="card">
                        <div class="card-header {{ $obavestenje->aktivan ? 'bg-primary' : 'bg-secondary' }} text-white">
                            <h5 class="mb-0">{{ $obavestenje->naslov }}</h5>
                            <small>
                                @switch($obavestenje->tip)
                                    @case('opste') Опште @break
                                    @case('ispit') Испит @break
                                    @case('raspored') Распоред @break
                                    @case('upis') Упис @break
                                    @case('Ocena') Оцена @break
                                    @case('stipendija') Стипендија @break
                                    @default {{ $obavestenje->tip }}
                                @endswitch
                                | {{ \Carbon\Carbon::parse($obavestenje->datum_objave)->format('d.m.Y.') }}
                            </small>
                        </div>
                        <div class="card-body">
                            <p>{!! nl2br(e($obavestenje->sadrzaj)) !!}</p>
                            @if($obavestenje->profesor)
                                <small class="text-muted">
                                    Професор: {{ $obavestenje->profesor->ime }} {{ $obavestenje->profesor->prezime }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info mt-4">
            Нема обавештења за вас.
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ url('/') }}" class="btn btn-secondary">Почетна</a>
    </div>
</div>
@endsection
