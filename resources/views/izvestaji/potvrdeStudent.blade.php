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
                    <x-button variant="primary" size="md" href="{{" target="_blank">id}}">
                        <span class="fa fa-print mr-2"></span> Штампа уверења
                    </x-button>
                </div>
                <div>
                    <x-button variant="primary" size="md" href="{{" target="_blank">id}}">
                        <span class="fa fa-print mr-2"></span> Комисија
                    </x-button>
                </div>

                <div>
                    <x-button variant="primary" size="md" href="{{" target="_blank">id}}">
                        <span class="fa fa-print mr-2"></span> Уверење о положеним испитима
                    </x-button>
                </div>
            </div>
        </x-card>

    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>

@endsection
