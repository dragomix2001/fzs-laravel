<title>Sportsko angažovanje</title>
@extends('layouts.layout')
@section('page_heading','Sportsko angažovanje')
@section('section')

    <div class="w-full lg:w-9/12">
        <a href="/kandidat/{{ $kandidat->id }}/edit" class="text-primary-600 hover:text-primary-800 underline">Nazad</a>

        <div class="mt-4">
            <x-table>
                <thead>
                <th>Naziv kluba</th>
                <th>Period</th>
                <th>Broj godina</th>
                <th>Sport</th>
                <th>Ime i prezime</th>
                <th>Akcije</th>
                </thead>

                @foreach($kandidat->angazovanja as $sportskoAngazovanje)
                    <tr>
                        <td>{{$sportskoAngazovanje->nazivKluba}}</td>
                        <td>{{$sportskoAngazovanje->odDoGodina}}</td>
                        <td>{{$sportskoAngazovanje->ukupnoGodina}}</td>
                        <td>{{$sportskoAngazovanje->sport->naziv}}</td>
                        <td>{{$sportskoAngazovanje->kandidat->imeKandidata}}
                            &nbsp; {{$sportskoAngazovanje->kandidat->prezimeKandidata}}</td>
                        <td>
                            <div class="inline-flex gap-2">
                                <form class="inline-block" action="/sportskoAngazovanje/{{$sportskoAngazovanje->id}}/edit">
                                    <x-button variant="primary">Promeni</x-button>
                                </form>
                                <form class="inline-block" onsubmit="return confirm('Да ли сте сигурни да желите да обришете податке?');" action="/sportskoAngazovanje/{{$sportskoAngazovanje->id}}/delete">
                                    <x-button variant="danger">Izbriši</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>

        <div class="mt-6">
            <x-card variant="success">
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Sportsko angažovanje</h3>
                </x-slot:header>
                <form role="form" method="post" action="{{ url('/sportskoAngazovanje/unos') }}">
                    {{csrf_field()}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-form-input label="Naziv kluba:" name="nazivKluba" type="text" />
                        <x-form-input label="Od do godina:" name="odDoGodina" type="text" />
                        <x-form-input label="Ukupno godina:" name="ukupnoGodina" type="text" />
                        <x-form-select label="Sport:" name="sport_id"
                                       :options="$sport->pluck('naziv','id')->toArray()" />
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
