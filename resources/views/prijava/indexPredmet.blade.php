@extends('layouts.layout')
@section('page_heading','Пријава за полагање испита')
@section('section')
    <div class="w-full lg:w-10/12">
        @if (Session::get('flash-error'))
            <x-alert type="danger">
                @if(Session::get('flash-error') === 'create')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @endif
            </x-alert>
        @endif
        @if (Session::get('flash-success'))
            <x-alert type="success">
                Пријава је успешно забележена!
            </x-alert>
        @endif
        <x-card>
            <form role="form" method="post" action="{{ url('/prijava/') }}">
                {{ csrf_field() }}
                <input type="hidden" name="prijava_za_predmet" value="1">
                <input type="hidden" name="predmet_id" value="{{ $predmet->id }}">
                <input type="hidden" name="tipStudija_id" value="{{ $predmet->tipStudija_id }}">
                <input type="hidden" name="studijskiProgram_id" value="{{ $predmet->studijskiProgram_id }}">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <x-form-input name="brojIndeksa" label="Број Индекса" :value="$kandidat->brojIndeksa" disabled />
                    </div>
                    <div>
                        <x-form-select name="rok_id" label="Испитни рок" :options="$ispitniRok->pluck('naziv','id')->toArray()" />
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <x-button variant="success" size="lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Сачувај
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
