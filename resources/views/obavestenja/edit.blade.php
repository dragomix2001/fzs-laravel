@extends('layouts.layout')
@section('page_heading','Измени обавештење')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Измени обавештење</h2>

    <form method="POST" action="{{ url('/obavestenja/' . $obavestenje->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-input label="Наслов *" name="naslov" type="text" :value="$obavestenje->naslov" required />
            <x-form-select label="Тип *" name="tip" required
                           :options="$tipovi"
                           :selected="$obavestenje->tip" />
        </div>

        <div class="mt-4">
            <label for="sadrzaj" class="block text-sm font-medium text-secondary-700 mb-1">Садржај *</label>
            <textarea name="sadrzaj" id="sadrzaj" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" rows="6" required>{{ $obavestenje->sadrzaj }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            <x-form-input label="Датум објаве *" name="datum_objave" type="datetime-local" :value="\Carbon\Carbon::parse($obavestenje->datum_objave)->format('Y-m-d\TH:i')" required />
            <x-form-input label="Датум истека" name="datum_isteka" type="datetime-local" :value="$obavestenje->datum_isteka ? \Carbon\Carbon::parse($obavestenje->datum_isteka)->format('Y-m-d\TH:i') : ''" />
            <x-form-select label="Професор" name="profesor_id"
                           :options="collect(['' => '-- Изаберите професора --'] + $profesori->mapWithKeys(function($p) { return [$p->id => $p->ime.' '.$p->prezime]; })->toArray())->toArray()"
                           :selected="$obavestenje->profesor_id" />
        </div>

        <div class="flex items-center gap-2 mt-4">
            <label class="flex items-center gap-2 text-sm font-medium text-secondary-700">
                <input type="checkbox" name="aktivan" value="1" class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500" {{ $obavestenje->aktivan ? 'checked' : '' }}>
                Активно
            </label>
        </div>

        <div class="mt-6 flex gap-2">
            <x-button variant="primary">Сачувај</x-button>
            <a href="{{ route('obavestenja.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm font-medium hover:bg-gray-300">Откажи</a>
        </div>
    </form>
</div>
@endsection
