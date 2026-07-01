<title>Додавање студијског програма</title>
@extends('layouts.layout')
@section('page_heading','Додавање студијског програма')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{ url('/studijskiProgram/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Студијски програм</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                    <x-form-input label="Скраћени назив:" name="skrNazivStudijskogPrograma" type="text" />
                    <x-form-input label="Звање:" name="zvanje" type="text" />
                    <x-form-select label="Тип студијског програма:" name="tipStudija_id" id="tipStudija_id" :options="$tipStudija->pluck('naziv', 'id')->toArray()" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $("#tipStudija_id").val($("#tipStudijaHidden").val());
        });
    </script>

@endsection
