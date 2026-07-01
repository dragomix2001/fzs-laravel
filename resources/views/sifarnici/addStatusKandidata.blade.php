<title>Додавање статуса кандидата</title>
@extends('layouts.layout')
@section('page_heading','Додавање статуса кандидата')
@section('section')

    <div class="w-full lg:w-9/12">
        <form role="form" method="post" action="{{ url('/statusKandidata/unos') }}">
            {{csrf_field()}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Статус кандидата</h3>
                </x-slot:header>
                <div class="space-y-4">
                    <x-form-input label="Назив:" name="naziv" type="text" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Додај</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $.mask.definitions['q'] = '[0-3]';
            $.mask.definitions['w'] = '[0-9]';
            $.mask.definitions['e'] = '[0-1]';
            $('#datum').mask("qw.ew.9999.");
        });
    </script>

@endsection
