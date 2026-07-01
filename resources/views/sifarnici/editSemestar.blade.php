<title>Измени семестар</title>
@extends('layouts.layout')
@section('page_heading','Измени семестар')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}semestar/{{$semestar->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени семестар</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$semestar->naziv" />
                    <x-form-input label="Назив римски:" name="nazivRimski" type="text" :value="$semestar->nazivRimski" />
                    <x-form-input label="Назив бројчано:" name="nazivBrojcano" type="number" :value="$semestar->nazivBrojcano" />
                </div>

                <div class="mb-6">
                    <label class="flex items-start gap-3">
                        <input name="indikatorAktivan" value="1" type="checkbox" @if($semestar->indikatorAktivan == 1) checked @endif class="mt-1">
                        <span>Активан</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

@endsection
