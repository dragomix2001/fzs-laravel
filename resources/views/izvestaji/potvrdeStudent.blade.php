<title>Потврде</title>
@extends('layouts.layout')
@section('page_heading','Потврде')
@section('section')

    <div class="col-span-9">

        <x-card class="border-success-200">
            <x-slot:header>
                <div class="font-semibold text-secondary-800">Потврде</div>
            </x-slot:header>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="hidden" value="{{$student->id}}">
                    <a class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors" target="_blank"
                       href="{{"/"}}izvestaji/diplomaStampa/{{$student->id}}">
                        <span class="fa fa-print mr-2"></span> Штампа уверења
                    </a>
                </div>
                <div>
                    <a target="_blank" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors" href="{{"/"}}izvestaji/komisijaStampa/{{$student->id}}">
                        <span class="fa fa-print mr-2"></span> Комисија
                    </a>
                </div>

                <div>
                    <a target="_blank" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors" href="{{"/"}}izvestaji/polozeniStampa/{{$student->id}}">
                        <span class="fa fa-print mr-2"></span> Уверење о положеним испитима
                    </a>
                </div>
            </div>
        </x-card>

    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>

@endsection
