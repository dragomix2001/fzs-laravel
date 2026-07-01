@extends('layouts.layout')
@section('page_heading',"Измена године")
@section('section')
    <div class="w-full lg:w-10/12">
        <form action="{{"/"}}student/{{ $upisGodine->id }}/izmenaGodine" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="id" id="id" value="{{ $upisGodine->id }}">
            <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $upisGodine->kandidat_id }}">
            <input type="hidden" name="godina" id="godina" value="{{ $upisGodine->godina }}">
            <input type="hidden" name="pokusaj" id="pokusaj" value="{{ $upisGodine->pokusaj }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="godina" class="block text-sm font-medium text-secondary-700 mb-1">Година</label>
                    <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-secondary-100 cursor-not-allowed" type="text" name="godina" id="godina" value="{{ $upisGodine->godina }}" disabled/>
                </div>
                <div>
                    <label for="pokusaj" class="block text-sm font-medium text-secondary-700 mb-1">Покушај</label>
                    <input class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-secondary-100 cursor-not-allowed" type="text" name="pokusaj" id="pokusaj" value="{{ $upisGodine->pokusaj }}" disabled/>
                </div>
                <div class="md:col-span-2">
                    <x-form-select label="Статус године" name="statusGodine_id"
                                   :options="$statusGodine->pluck('naziv','id')->toArray()"
                                   :selected="$upisGodine->statusGodine_id" />
                </div>
                <div class="md:col-span-2">
                    <x-form-select label="Школска година" name="skolskaGodina_id"
                                   :options="$skolskaGodina->pluck('naziv','id')->toArray()"
                                   :selected="$upisGodine->skolskaGodina_id" />
                </div>
                <div>
                    <label for="datumUpisa_format" class="block text-sm font-medium text-secondary-700 mb-1">Датум уписа</label>
                    <input class="dateMask block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" type="text" name="datumUpisa_format" id="datumUpisa_format"
                           value="@if(!empty($upisGodine->datumUpisa)){{ $upisGodine->datumUpisa->format('d.m.Y.') }}@endif"/>
                </div>
                <div>
                    <label for="datumPromene_format" class="block text-sm font-medium text-secondary-700 mb-1">Датум промене</label>
                    <input class="dateMask block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" type="text" name="datumPromene_format" id="datumPromene_format"
                           value="@if(!empty($upisGodine->datumPromene)){{ $upisGodine->datumPromene->format('d.m.Y.') }}@endif"/>
                </div>
                <input type="hidden" name="datumUpisa" id="datumUpisa" value="{{$upisGodine->datumUpisa}}">
                <input type="hidden" name="datumPromene" id="datumPromene" value="{{$upisGodine->datumPromene}}">
            </div>

            <div class="mt-6">
                <x-button variant="success">Измени</x-button>
            </div>
        </form>
    </div>
    <script>
        var formatStatus = $("#datumUpisa_format");
        formatStatus.datepicker({
            dateFormat: 'dd.mm.yy.',
            altField: "#datumUpisa",
            altFormat: "yy-mm-dd"
        });

        var formatPromena = $("#datumPromene_format");
        formatPromena.datepicker({
            dateFormat: 'dd.mm.yy.',
            altField: "#datumPromene",
            altFormat: "yy-mm-dd"
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
