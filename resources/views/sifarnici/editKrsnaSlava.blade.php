<title>Измени крсну славу</title>
@extends('layouts.layout')
@section('page_heading','Измени крсну славу')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}krsnaSlava/{{$krsnaSlava->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени крсну славу</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$krsnaSlava->naziv" />
                    <x-form-input label="Датум:" name="datumSlave" type="text" id="datumSlave" :value="$krsnaSlava->datumSlave" />
                </div>

                <div class="mb-6">
                    <label class="flex items-start gap-3">
                        <input name="indikatorAktivan" value="1" type="checkbox" @if($krsnaSlava->indikatorAktivan == 1) checked @endif class="mt-1">
                        <span>Активан</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <x-button variant="primary" type="submit">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $.mask.definitions['q'] = '[0-3]';
        $.mask.definitions['w'] = '[0-9]';
        $.mask.definitions['e'] = '[0-1]';
        $('#datumSlave').mask("qw.ew");
    </script>

@endsection
