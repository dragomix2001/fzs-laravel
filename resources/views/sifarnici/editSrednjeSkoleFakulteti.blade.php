<title>Izmeni školu/fakultet</title>
@extends('layouts.layout')
@section('page_heading','Izmeni školu/fakultet')
@section('section')

    <div class="w-full lg:w-9/12">
        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Izmeni školu/fakultet</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{"/"}}srednjeSkoleFakulteti/{{$srednjeSkoleFakulteti->id}}">
                {{csrf_field()}}
                {{method_field('PATCH')}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input label="Naziv:" name="naziv" type="text" :value="$srednjeSkoleFakulteti->naziv" />
                    <x-form-select label="Škola/Fakultet:" name="indSkoleFakulteta"
                                   id="indSkoleFakulteta"
                                   :options="['1'=>'Škola','2'=>'Fakultet']"
                                   :selected="$srednjeSkoleFakulteti->indSkoleFakulteta" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Izmeni</x-button>
                </div>
            </form>
        </x-card>
    </div>

@endsection
