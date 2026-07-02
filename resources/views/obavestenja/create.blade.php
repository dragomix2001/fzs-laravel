@extends('layouts.layout')
@section('page_heading','Додај обавештење')
@section('section')

<div class="w-full lg:w-10/12">
    <h2>Додај обавештење</h2>

    <form method="POST" action="{{ route('obavestenja.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form-input label="Наслов *" name="naslov" type="text" required />
            <x-form-select label="Тип *" name="tip" required
                           :options="$tipovi" />
        </div>

        <div class="mt-4">
            <label for="sadrzaj" class="block text-sm font-medium text-secondary-700 mb-1">Садржај *</label>
            <textarea name="sadrzaj" id="sadrzaj" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" rows="6" required></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            <x-form-input label="Датум објаве *" name="datum_objave" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required />
            <x-form-input label="Датум истека" name="datum_isteka" type="datetime-local" />
            <x-form-select label="Професор" name="profesor_id"
                           :options="collect(['' => '-- Изаберите професора --'] + $profesori->mapWithKeys(function($p) { return [$p->id => $p->ime.' '.$p->prezime]; })->toArray())->toArray()" />
        </div>

        <div class="flex items-center gap-2 mt-4">
            <label class="flex items-center gap-2 text-sm font-medium text-secondary-700">
                <input type="checkbox" name="aktivan" value="1" checked class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                Активно
            </label>
        </div>

        <div class="mt-6 flex gap-2">
            <x-button variant="primary">Сачувај</x-button>
            <x-button variant="secondary-soft" size="md" href="{{ route('obavestenja.index') }}">Откажи</x-button>
        </div>
    </form>
</div>
@endsection
