<title>Додавање програма</title>
@extends('layouts.layout')
@section('page_heading','Додавање програма')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{ url('/predmet/addProgramUnos') }}">
            {{csrf_field()}}

            <input type="hidden" id="predmet_id" name="predmet_id" value="{{$predmet->id}}">

            @if (Session::get('errors'))
                <x-alert type="danger" class="mb-6">
                    <h4 class="font-semibold mb-2">Грешка!</h4>
                    <ul class="list-disc list-inside">
                        @foreach (Session::get('errors')->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Програм</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-select label="Програм:" name="program_id" id="program_id" class="auto-combobox" required :options="$programi->pluck('naziv', 'id')->toArray()" />
                    <x-form-select label="Година:" name="godinaStudija_id" id="godinaStudija_id" :options="$godinaStudija->pluck('naziv', 'id')->toArray()" />
                    <x-form-select label="Семестар:" name="semestar" id="semestar" :options="['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10']" />
                    <x-form-select label="Тип предмета:" name="tipPredmeta_id" id="tipPredmeta_id" :options="$tipPredmeta->pluck('naziv', 'id')->toArray()" />
                    <x-form-select label="Школска година:" name="skolskaGodina_id" id="skolskaGodina_id" :options="$skolskaGodina->pluck('naziv', 'id')->toArray()" />
                    <x-form-input label="ЕСПБ:" name="espb" type="number" />
                    <x-form-input label="Часови предавања:" name="predavanja" type="number" />
                    <x-form-input label="Часови вежби:" name="vezbe" type="number" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>

@endsection
