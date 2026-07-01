<title>Izmeni mesto</title>
@extends('layouts.layout')
@section('page_heading','Izmeni mesto')
@section('section')

    <div class="w-full lg:w-9/12">
        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Mesto</h3>
            </x-slot:header>
            <form role="form" method="post" action="/mesto/{{$mesto->id}}">
                {{csrf_field()}}
                {{method_field('PATCH')}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input label="Naziv:" name="naziv" type="text" :value="$mesto->naziv" />
                    <x-form-select label="Opština:" name="opstina_id"
                                   id="opstina_id"
                                   :options="$opstina->pluck('naziv','id')->toArray()"
                                   :selected="$mesto->opstina_id" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Izmeni</x-button>
                </div>
            </form>
        </x-card>
    </div>

@endsection
