<title>Измени студијски програм</title>
@extends('layouts.layout')
@section('page_heading','Измени студијски програм')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{"/"}}studijskiProgram/{{$studijskiProgram->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени студијски програм</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$studijskiProgram->naziv" />
                    <x-form-input label="Скраћени назив:" name="skrNazivStudijskogPrograma" type="text" :value="$studijskiProgram->skrNazivStudijskogPrograma" />
                    <x-form-input label="Звање:" name="zvanje" type="text" :value="$studijskiProgram->zvanje" />
                    <div>
                        <input type="hidden" id="tipStudijaHidden" value="{{$studijskiProgram->tipStudija_id}}">
                        <x-form-select label="Тип студијског програма:" name="tipStudija_id" id="tipStudija_id" :options="$tipStudija->pluck('naziv', 'id')->toArray()" />
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center gap-2">
                            @if($studijskiProgram->indikatorAktivan == 1)
                                <input name="indikatorAktivan" value="1" type="checkbox" checked="true" class="rounded">
                            @else
                                <input name="indikatorAktivan" type="checkbox" class="rounded">
                            @endif
                            <span>Активан</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Измени</x-button>
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
