<title>Додавање крснe славe</title>
@extends('layouts.layout')
@section('page_heading','Додавање крснe славe')
@section('section')

    <form role="form" method="post" action="{{ url('/krsnaSlava/unos') }}">
        {{csrf_field()}}

        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Крсна слава</h3>
            </x-slot:header>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <x-form-input label="Назив:" name="naziv" type="text" />
                <x-form-input label="Датум:" name="datumSlave" type="text" id="datumSlave" />
            </div>

            <div class="flex gap-3">
                <x-button variant="primary" type="submit">Додај</x-button>
            </div>
        </x-card>
    </form>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.mask.definitions['q'] = '[0-3]';
            $.mask.definitions['w'] = '[0-9]';
            $.mask.definitions['e'] = '[0-1]';
            $('#datumSlave').mask("qw.ew.");
        });
    </script>

@endsection
