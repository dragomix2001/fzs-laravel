<title>Измени општину</title>
@extends('layouts.layout')
@section('page_heading','Измени општину')
@section('section')

    <form role="form" method="post" action="{{"/"}}opstina/{{$opstina->id}}">
        {{csrf_field()}}
        {{method_field('PATCH')}}

        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Општина</h3>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <x-form-input label="Назив:" name="naziv" type="text" :value="$opstina->naziv" />
                <x-form-select label="Регион:" name="region_id" id="region_id" :options="$region->pluck('naziv', 'id')->toArray()" :value="$opstina->region_id" />
            </div>

            <div class="flex gap-3">
                <x-button variant="primary" type="submit">Измени</x-button>
            </div>
        </x-card>
    </form>

    <script>
        $(document).ready(function () {
            $("#region_id").val($("#region_id").data('value'));
        });
    </script>

@endsection
