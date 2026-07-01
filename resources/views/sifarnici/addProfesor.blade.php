<title>Додавање професора</title>
@extends('layouts.layout')
@section('page_heading','Додавање професора')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{ url('/profesor/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Професор</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="ЈМБГ:" name="jmbg" type="text" />
                    <x-form-input label="Име:" name="ime" type="text" />
                    <x-form-input label="Презиме:" name="prezime" type="text" />
                    <x-form-input label="Телефон:" name="telefon" type="text" />
                    <x-form-input label="Е-маил:" name="mail" type="text" />
                    <x-form-select label="Статус:" name="status_id" id="status_id" :options="$status->pluck('naziv', 'id')->toArray()" />
                    <x-form-input label="Кабинет:" name="kabinet" type="text" />
                    <x-form-input label="Звање:" name="zvanje" type="text" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
