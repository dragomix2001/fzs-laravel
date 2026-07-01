<title>Izmeni sportsko angažovanje</title>
@extends('layouts.layout')
@section('page_heading','Izmeni sportsko angažovanje')
@section('section')
    <div class="w-full lg:w-9/12">
        <x-card variant="success">
            <x-slot:header>
                <h3 class="text-lg font-semibold">Izmeni sportsko angažovanje</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{"/"}}sportskoAngazovanje/{{$angazovanje->id}}">
                {{csrf_field()}}
                {{method_field('PATCH')}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form-input label="Naziv kluba:" name="nazivKluba" type="text" :value="$angazovanje->nazivKluba" />
                    <x-form-input label="Od do godina:" name="odDoGodina" type="text" :value="$angazovanje->odDoGodina" />
                    <x-form-input label="Ukupno godina:" name="ukupnoGodina" type="text" :value="$angazovanje->ukupnoGodina" />
                    <x-form-select label="Sport:" name="sport_id"
                                   id="sport_id"
                                   :options="$sport->pluck('naziv','id')->toArray()"
                                   :selected="$angazovanje->sport_id" />
                </div>
                <div class="mt-6">
                    <x-button variant="primary">Izmeni</x-button>
                </div>
            </form>
        </x-card>
    </div>

@endsection
