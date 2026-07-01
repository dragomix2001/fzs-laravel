<title>Додавање општинe</title>
@extends('layouts.layout')
@section('page_heading','Додавање општинe')
@section('section')

    <form role="form" method="post" action="{{ url('/opstina/unos') }}">
        {{csrf_field()}}

        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Општина</h3>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <x-form-input label="Назив:" name="naziv" type="text" />
                <x-form-select label="Регион:" name="region_id" id="region_id" :options="$region->pluck('naziv', 'id')->toArray()" />
            </div>

            <div class="flex gap-3">
                <x-button variant="primary" type="submit">Додај</x-button>
            </div>
        </x-card>
    </form>

@endsection
