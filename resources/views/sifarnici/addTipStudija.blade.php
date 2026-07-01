<title>Додавање типа студија</title>
@extends('layouts.layout')
@section('page_heading','Додавање типа студија')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/tipStudija/unos') }}">
            {{csrf_field()}}


            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Тип студија</h3>
                </x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                    <x-form-input label="Скраћени назив:" name="skrNaziv" type="text" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>


@endsection
