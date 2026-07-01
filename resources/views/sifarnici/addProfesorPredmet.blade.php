<title>Додавање предметa</title>
@extends('layouts.layout')
@section('page_heading','Додавање предметa')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{ url('/profesor/addPredmetUnos') }}">
            {{csrf_field()}}

            <input type="hidden" id="profesor_id" name="profesor_id" value="{{$profesor->id}}">

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
                    <h3 class="text-lg font-semibold">Предмет</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-select label="Предмет:" name="predmet_id" id="predmet_id" class="auto-combobox" :options="$predmet->pluck('predmet.naziv', 'id')->toArray()" />
                    <x-form-select label="Облик наставе:" name="oblikNastave_id" id="oblikNastave_id" :options="$oblik->pluck('naziv', 'id')->toArray()" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>

@endsection
