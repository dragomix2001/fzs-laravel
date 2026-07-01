<title>Додавање бодовања</title>
@extends('layouts.layout')
@section('page_heading','Додавање бодовања')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/bodovanje/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Бодовање</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Описна оцена:" name="opisnaOcena" type="text" />
                    <x-form-input label="Минимум поена:" name="poeniMin" type="number" />
                    <x-form-input label="Максимум поена:" name="poeniMax" type="number" />
                    <x-form-input label="Оцена:" name="ocena" type="number" />
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
