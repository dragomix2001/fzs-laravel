@extends('layouts.layout')
@section('page_heading','Приказ обавештења')
@section('section')

<div class="w-full lg:w-10/12">
    <x-card>
        <x-slot:header>
            <h3 class="text-lg font-semibold">{{ $obavestenje->naslov }}</h3>
        </x-slot:header>
        <div class="space-y-3">
            <div>
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
            <div>
                <strong>Датум објаве:</strong> {{ \Carbon\Carbon::parse($obavestenje->datum_objave)->format('d.m.Y. H:i') }}
            </div>
            @if($obavestenje->datum_isteka)
                <div>
                    <strong>Датум истека:</strong> {{ \Carbon\Carbon::parse($obavestenje->datum_isteka)->format('d.m.Y. H:i') }}
                </div>
            @endif
            @if($obavestenje->profesor)
                <div>
                    <strong>Професор:</strong> {{ $obavestenje->profesor->ime }} {{ $obavestenje->profesor->prezime }}
                </div>
            @endif
            <div>
                <strong>Статус:</strong>
                @if($obavestenje->aktivan)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">Активно</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800">Неактивно</span>
                @endif
            </div>
            <hr class="border-secondary-200">
            <div class="obavestenje-sadrzaj">
                {!! nl2br(e($obavestenje->sadrzaj)) !!}
            </div>
        </div>
        <x-slot:footer>
            <div class="flex gap-2">
                <x-button variant="secondary-soft" size="md" href="{{ route('obavestenja.index') }}">Назад</x-button>
                <a href="{{ url('/obavestenja/' . ($obavestenje->id ?? '0') . '/edit') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded text-sm font-medium hover:bg-primary-700">Измени</a>
            </div>
        </x-slot:footer>
    </x-card>
</div>
@endsection
