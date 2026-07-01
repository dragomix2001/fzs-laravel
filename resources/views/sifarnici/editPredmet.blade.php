<title>Измени предмет</title>
@extends('layouts.layout')
@section('page_heading','Измени предмет')
@section('section')

    <div class="max-w-2xl">
        <form method="post" action="{{"/"}}predmet/{{$predmet->id}}">
            {{csrf_field()}}
            {{method_field('PATCH')}}

            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Измени предмет</h3>
                </x-slot:header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-form-input label="Назив:" name="naziv" type="text" :value="$predmet->naziv" />
                </div>

                <div class="flex gap-2">
                    <x-button variant="primary" type="submit">Измени</x-button>
                </div>
            </x-card>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $("#tabs").tabs();

            $("#tipStudija_id").val($("#tipStudijaHidden").val());
            $("#studijskiProgram_id").val($("#studijskiProgramHidden").val());
            $("#godinaStudija_id").val($("#godinaStudijaHidden").val());
            $("#tipPredmeta_id").val($("#tipPredmetaHidden").val());
        });

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

    </script>

@endsection
