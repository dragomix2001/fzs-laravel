<title>Додавање године студија</title>
@extends('layouts.layout')
@section('page_heading','Додавање године студија')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/godinaStudija/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Година студија</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                    <x-form-input label="Римски назив:" name="nazivRimski" type="text" />
                    <x-form-input label="Назив у падежу:" name="nazivSlovimaUPadezu" type="text" />
                    <x-form-input label="Редослед приказивања:" name="redosledPrikazivanja" type="text" />
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
