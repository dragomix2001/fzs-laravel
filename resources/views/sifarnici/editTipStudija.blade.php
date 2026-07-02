<title>Измени тип студија</title>
@extends('layouts.layout')
@section('page_heading','Измени тип студија')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}tipStudija/{{$tipStudija->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени тип студија</h3>
                </x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input label="Назив:" name="naziv" type="text" value="{{$tipStudija->naziv}}" />
                    <x-form-input label="Скраћени назив:" name="skrNaziv" type="text" value="{{$tipStudija->skrNaziv}}" />
                    <div class="flex items-start gap-3">
                        @if($tipStudija->indikatorAktivan == 1)
                            <input name="indikatorAktivan" value="1" type="checkbox" checked="true" class="mt-1 rounded border-secondary-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        @else
                            <input name="indikatorAktivan" type="checkbox" class="mt-1 rounded border-secondary-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        @endif
                        <label class="font-semibold text-secondary-700">Активан</label>
                    </div>
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>


@endsection
