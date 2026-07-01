<title>Измени регион</title>
@extends('layouts.layout')
@section('page_heading','Измени регион')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}region/{{$region->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Регион</h3>
                </x-slot:header>
                <div class="space-y-4">
                    <x-form-input label="Назив:" name="naziv" type="text" value="{{$region->naziv}}" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
