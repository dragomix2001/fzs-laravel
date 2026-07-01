<title>Измени професора</title>
@extends('layouts.layout')
@section('page_heading','Измени професора')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{"/"}}profesor/{{$profesor->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Професор</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="ЈМБГ:" name="jmbg" type="text" :value="$profesor->jmbg" />
                    <x-form-input label="Име:" name="ime" type="text" :value="$profesor->ime" />
                    <x-form-input label="Презиме:" name="prezime" type="text" :value="$profesor->prezime" />
                    <x-form-input label="Телефон:" name="telefon" type="text" :value="$profesor->telefon" />
                    <x-form-input label="Е-маил:" name="mail" type="text" :value="$profesor->mail" />
                    <div>
                        <input type="hidden" id="statusHidden" value="{{$profesor->status_id}}">
                        <x-form-select label="Статус:" name="status_id" id="status_id" :options="$status->pluck('naziv', 'id')->toArray()" />
                    </div>
                    <x-form-input label="Кабинет:" name="kabinet" type="text" :value="$profesor->kabinet" />
                    <x-form-input label="Звање:" name="zvanje" type="text" :value="$profesor->jmbg" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $("#status_id").val($("#statusHidden").val());

            //$("#tabs").tabs();

            $('#tabela').dataTable({
                "aaSorting": [],
                "oLanguage": {
                    "sProcessing": "Процесирање у току...",
                    "sLengthMenu": "Прикажи _MENU_ елемената",
                    "sZeroRecords": "Није пронађен ниједан резултат",
                    "sInfo": "Приказ _START_ до _END_ од укупно _TOTAL_ елемената",
                    "sInfoEmpty": "Приказ 0 до 0 од укупно 0 елемената",
                    "sInfoFiltered": "(филтрирано од укупно _MAX_ елемената)",
                    "sInfoPostFix": "",
                    "sSearch": "Претрага:",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "Почетна",
                        "sPrevious": "Претходна",
                        "sNext": "Следећа",
                        "sLast": "Последња"
                    }
                }
            });

        });
    </script>

@endsection
