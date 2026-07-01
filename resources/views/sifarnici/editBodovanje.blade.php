<title>Измени бодовање</title>
@extends('layouts.layout')
@section('page_heading','Измени бодовање')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{"/"}}bodovanje/{{$bodovanje->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени бодовање</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Описна оцена:" name="opisnaOcena" type="text" :value="$bodovanje->opisnaOcena" />
                    <x-form-input label="Минимум поена:" name="poeniMin" type="number" :value="$bodovanje->poeniMin" />
                    <x-form-input label="Максимум поена:" name="poeniMax" type="number" :value="$bodovanje->poeniMax" />
                    <x-form-input label="Оцена:" name="ocena" type="number" :value="$bodovanje->ocena" />
                </div>

                <div class="mb-6">
                    <label class="flex items-start gap-3">
                        <input name="indikatorAktivan" value="1" type="checkbox" @if($bodovanje->indikatorAktivan == 1) checked @endif class="mt-1">
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
