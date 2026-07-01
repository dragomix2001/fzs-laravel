<title>Mesto</title>
@extends('layouts.layout')
@section('page_heading','Mesto')
@section('section')

    <div class="w-full lg:w-9/12">
        <x-table>
            <thead>
            <th>Naziv</th>
            <th>Naziv opštine</th>
            <th>Akcije</th>
            </thead>

            @foreach($mesto as $mesto)
                <tr>
                    <td>{{$mesto->naziv}}</td>
                    <td>
                        @if($mesto->opstina)
                            {{$mesto->opstina->naziv}}
                        @else
                            Prazno
                        @endif
                    </td>
                    <td>
                        <div class="inline-flex gap-2">
                            <form class="inline-block" action="mesto/{{$mesto->id}}/edit">
                                <x-button variant="primary">Promeni</x-button>
                            </form>
                            <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="mesto/{{$mesto->id}}/delete">
                                <x-button variant="danger">Izbriši</x-button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-6">
            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Mesto</h3>
                </x-slot:header>
                <form role="form" method="post" action="{{ url('/mesto/unos') }}">
                    {{csrf_field()}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input label="Naziv:" name="naziv" type="text" />
                        <x-form-select label="Opština:" name="opstina_id" :options="$opstina->pluck('naziv','id')->toArray()" />
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
