<title>Измена године студија</title>
@extends('layouts.layout')
@section('page_heading','Измена године студија')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}godinaStudija/{{$godinaStudija->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измена године студија</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$godinaStudija->naziv" />
                    <x-form-input label="Римски назив:" name="nazivRimski" type="text" :value="$godinaStudija->nazivRimski" />
                    <x-form-input label="Назив у падежу:" name="nazivSlovimaUPadezu" type="text" :value="$godinaStudija->nazivSlovimaUPadezu" />
                    <x-form-input label="Редослед приказивања:" name="redosledPrikazivanja" type="text" :value="$godinaStudija->redosledPrikazivanja" />
                </div>

                <div class="mb-6">
                    <label class="flex items-start gap-3">
                        <input name="indikatorAktivan" value="1" type="checkbox" @if($godinaStudija->indikatorAktivan == 1) checked @endif class="mt-1">
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
