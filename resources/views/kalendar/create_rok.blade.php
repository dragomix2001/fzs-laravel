@extends('layouts.layout')
@section('page_heading','Нови испитни рок')
@section('section')

    <div class="w-full lg:w-10/12">
        @if (Session::get('errors'))
            <x-card variant="danger" class="mb-4">
                <x-slot:header>
                    <h4 class="text-lg font-semibold">Грешка!</h4>
                </x-slot:header>
                <ul class="list-disc pl-5">
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        @if (Session::get('flash-error'))
            <x-card variant="danger" class="mb-4">
                <x-slot:header>
                    <strong>Грешка!</strong>
                </x-slot:header>
                @if(Session::get('flash-error') === 'create')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @endif
            </x-card>
        @endif

        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Нови испитни рок</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{ url('/kalendar/storeRok') }}">
                {{ csrf_field() }}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-select label="Испитни рок" name="rok_id"
                                   :options="$ispitniRok->pluck('naziv','id')->toArray()" />

                    <x-form-input label="Назив" name="naziv" type="text" />

                    <div>
                        <label for="formatPocetak" class="block text-sm font-medium text-secondary-700 mb-1">Почетак</label>
                        <input id="formatPocetak" class="dateMask block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" type="text" name="formatPocetak" />
                    </div>

                    <div>
                        <label for="formatKraj" class="block text-sm font-medium text-secondary-700 mb-1">Крај</label>
                        <input id="formatKraj" class="dateMask block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" type="text" name="formatKraj" />
                    </div>

                    <input type="hidden" name="pocetak" id="pocetak">
                    <input type="hidden" name="kraj" id="kraj">

                    <x-form-select label="Тип рока" name="tipRoka_id"
                                   :options="['1' => 'Редовни', '2' => 'Ванредни']" />

                    <x-form-input label="Коментар" name="komentar" type="text" />
                </div>

                <div class="flex items-center gap-2 mt-4">
                    <label class="flex items-center gap-2 text-sm font-medium text-secondary-700">
                        <input type="checkbox" name="indikatorAktivan" value="1" class="rounded border-secondary-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        Индикатор активан
                    </label>
                </div>

                <hr class="my-4">

                <div class="text-center">
                    <x-button variant="success">
                        <span class="fa fa-save"></span> Сачувај
                    </x-button>
                </div>

            </form>
        </x-card>
    </div>

    <script>
        $(function () {
            $("#formatPocetak").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#pocetak",
                altFormat: "yy-mm-dd"
            });

            $("#formatKraj").datepicker({
                dateFormat: 'dd.mm.yy.',
                altField: "#kraj",
                altFormat: "yy-mm-dd"
            });
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
