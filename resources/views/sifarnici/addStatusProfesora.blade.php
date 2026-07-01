<title>Додавање статуса професора</title>
@extends('layouts.layout')
@section('page_heading','Додавање статуса професора')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/statusProfesora/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Додавање статуса професора</h3>
                </x-slot:header>
                <div class="space-y-4">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
