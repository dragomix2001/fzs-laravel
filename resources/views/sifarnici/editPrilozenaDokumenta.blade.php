<title>Промени приложена документа</title>
@extends('layouts.layout')
@section('page_heading','Промени приложена документа')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}prilozenaDokumenta/{{$dokument->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Промени приложена документа</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Редни број:" name="redniBrojDokumenta" type="text" :value="$dokument->redniBrojDokumenta" />
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$dokument->naziv" />
                    <x-form-select label="Школска година:" name="skolskaGodina_id" id="skolskaGodina_id" :options="$godinaStudija->pluck('naziv', 'id')->toArray()" :value="$dokument->skolskaGodina_id" />
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $("#skolskaGodina_id").val($("#godinaStudijaHidden").val());
        });
    </script>

@endsection
