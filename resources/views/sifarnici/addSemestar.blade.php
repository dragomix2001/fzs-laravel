<title>Додавање семестра</title>
@extends('layouts.layout')
@section('page_heading','Додавање семестра')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/semestar/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Семестар</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                    <x-form-input label="Назив римски:" name="nazivRimski" type="text" />
                    <x-form-input label="Назив бројчано:" name="nazivBrojcano" type="number" />
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
