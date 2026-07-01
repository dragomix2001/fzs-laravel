<title>Додавање приложеног документа</title>
@extends('layouts.layout')
@section('page_heading','Додавање приложеног документа')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/prilozenaDokumenta/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Приложена документа</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Редни број:" name="redniBrojDokumenta" type="text" />
                    <x-form-input label="Назив:" name="naziv" type="text" />
                    <x-form-select label="Школска година:" name="skolskaGodina_id" id="skolskaGodina_id" :options="$godinaStudija->pluck('naziv', 'id')->toArray()" />
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
