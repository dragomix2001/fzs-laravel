@extends('layouts.layout')
@section('page_heading','Јавна обавештења')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Обавештења</h2>

    @if($obavestenja->count() > 0)
        <div class="mt-4 space-y-4">
            @foreach($obavestenja as $obavestenje)
                <x-card variant="primary">
                    <x-slot:header>
                        <h5 class="text-lg font-semibold text-white">{{ $obavestenje->naslov }}</h5>
                        <small class="text-white text-opacity-75">
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
                    </x-slot:header>
                    <p>{!! nl2br(e($obavestenje->sadrzaj)) !!}</p>
                    @if($obavestenje->profesor)
                        <small class="text-gray-500">
                            Професор: {{ $obavestenje->profesor->ime }} {{ $obavestenje->profesor->prezime }}
                        </small>
                    @endif
                </x-card>
            @endforeach
        </div>
    @else
        <x-card variant="info" class="mt-4">Тренутно нема активних обавештења.</x-card>
    @endif

    <div class="mt-4">
        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm font-medium hover:bg-gray-300">Почетна</a>
    </div>
</div>
@endsection
