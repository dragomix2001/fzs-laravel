<title>Srednje škole i fakulteti</title>
@extends('layouts.layout')
@section('page_heading','Srednje škole i fakulteti')
@section('section')

    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <th>Naziv srednje škole/fakulteta</th>
            <th>Indikator</th>
            <th>Akcije</th>
            </thead>

            @foreach($srednjeSkoleFakulteti as $srednjeSkoleFakulteti)
                <tr>
                    <td>{{$srednjeSkoleFakulteti->naziv}}</td>
                    <td>
                        @if($srednjeSkoleFakulteti->indSkoleFakulteta == 1)
                            Škola
                        @else
                            Fakultet
                        @endif
                    </td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="srednjeSkoleFakulteti/{{$srednjeSkoleFakulteti->id}}/edit">
                                <x-button variant="primary" size="sm">Promeni</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="srednjeSkoleFakulteti/{{$srednjeSkoleFakulteti->id}}/delete">
                                <x-button variant="danger" size="sm">Izbriši</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-6">
            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Srednje škole i fakulteti</h3>
                </x-slot:header>
                <form role="form" method="post" action="{{ url('/srednjeSkoleFakulteti/unos') }}">
                    {{csrf_field()}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input label="Naziv:" name="naziv" type="text" />
                        <x-form-select label="Škola/Fakultet:" name="indSkoleFakulteta"
                                       :options="['1'=>'Škola','2'=>'Fakultet']" />
                    </div>
                    <div class="mt-6">
                        <x-button variant="primary">Dodaj</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <script type="text/javascript" src="{{ URL::asset('/js/tabela.js') }}"></script>

@endsection
