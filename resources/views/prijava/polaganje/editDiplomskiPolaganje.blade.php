@extends('layouts.layout')
@section('page_heading','Пријава полагања дипломског рада')
@section('section')
    <div class="w-full lg:w-10/12">
        @if (Session::get('errors'))
            <x-alert type="danger" :dismissible="true">
                <x-slot:title>Грешка!</x-slot:title>
                <ul>
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
        @if (Session::get('flash-error'))
            <x-alert type="danger" :dismissible="true">
                <x-slot:title>Грешка!</x-slot:title>
                @if(Session::get('flash-error') === 'create')
                    Дошло је до грешке при чувању података! Молимо вас покушајте поново.
                @endif
            </x-alert>
        @endif
        <div class="mb-4">
            <a href="/prijava/zaStudenta/{{ $kandidat->id }}">
                <x-button variant="primary">&lt;&lt; Назад на студента</x-button>
            </a>
        </div>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold">Пријава полагања дипломског рада</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{ url('/prijava/updateDiplomskiPolaganje') }}">
                {{ csrf_field() }}
                <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">
                <input type="hidden" name="polaganje_id" id="polaganje_id" value="{{ $diplomskiRadPolaganje->id }}">
                <input type="hidden" name="tipStudija_id" id="tipStudija_id" value="{{ $kandidat->tipStudija_id }}">
                <input type="hidden" name="studijskiProgram_id" id="studijskiProgram_id" value="{{ $kandidat->studijskiProgram_id }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-input name="brojIndeksa" label="Број Индекса" :value="$kandidat->brojIndeksa" disabled />
                    <div class="sm:col-span-2">
                        <x-form-input name="imeKandidata" label="Име"
                                      value="{{ $kandidat->imeKandidata . ' ' . $kandidat->imeRoditelja . ' ' . $kandidat->prezimeKandidata }}" disabled />
                    </div>
                    <x-form-input name="jmbg" label="ЈМБГ" :value="$kandidat->jmbg" disabled />
                    <div class="sm:col-span-2">
                        <x-form-input name="StudijskiProgram" label="Студијски програм"
                                      :value="$kandidat->program->naziv" disabled />
                    </div>
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="lg:col-span-2">
                        <x-form-select name="predmet_id" label="Дипломски рад из предмета"
                                       :options="$predmeti->mapWithKeys(fn($i) => [$i->id => $i->predmet?->naziv ?? '-'])->toArray()" />
                    </div>
                    <div class="lg:col-span-2">
                        <x-form-input name="nazivTeme" label="Назив теме:" :value="$diplomskiRadPolaganje->nazivTeme" />
                    </div>
                    <x-form-input name="formatDatum" label="Датум"
                                  value="{{ \Illuminate\Support\Carbon::parse($diplomskiRadPolaganje->datum)->format('d.m.Y.') }}"
                                  class="dateMask" />
                    <input type="hidden" name="datum" id="datum"
                           value="{{ \Illuminate\Support\Carbon::parse($diplomskiRadPolaganje->datum)->format('Y-m-d') }}">
                    <x-form-input name="vreme" label="Време"
                                  value="{{ substr($diplomskiRadPolaganje->vreme, 0, -3) }}"
                                  class="timeMask" />
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="lg:col-span-2">
                        <x-form-select name="profesor_id" label="Професор"
                                       :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()"
                                       :selected="$diplomskiRadPolaganje->profesor_id" />
                    </div>
                    <x-form-select name="profesor_id_predsednik" label="Председник комисије:"
                                   :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()"
                                   :selected="$diplomskiRadPolaganje->profesor_id_predsednik" />
                    <x-form-select name="profesor_id_clan" label="Члан комисије:"
                                   :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()"
                                   :selected="$diplomskiRadPolaganje->profesor_id_clan" />
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-form-input name="brojBodova" label="Број бодова" class="brojBodova"
                                  :value="$diplomskiRadPolaganje->brojBodova" />
                    <x-form-select name="ocena" label="Оцена" id="ocena"
                                   :options="['0'=>'','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10']"
                                   class="konacnaOcena" />
                    <x-form-select name="konacnaOcenaSlovima" label="Оцена словима"
                                   :options="['0'=>'','5'=>'пет','6'=>'шест','7'=>'седам','8'=>'осам','9'=>'девет','10'=>'десет']"
                                   class="konacnaOcenaSlovima" disabled />
                </div>

                <div class="mt-6 text-center">
                    <x-button variant="primary" size="lg">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Сачувај
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var formatDatum = document.getElementById('formatDatum');
            if (formatDatum) {
                formatDatum.addEventListener('input', function () {
                    var date = moment(formatDatum.value, "DD.MM.YYYY");
                    document.getElementById('datum').value = date.isValid() ? date.format('YYYY-MM-DD') : '';
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    return false;
                }
            });

            // Score auto-calculation
            var brojBodova = document.querySelector('.brojBodova');
            var konacnaOcena = document.querySelector('.konacnaOcena');
            var konacnaOcenaSlovima = document.querySelector('.konacnaOcenaSlovima');

            function izracunajOcenu() {
                var bodovi = parseInt(brojBodova.value) || 0;
                var ocena = 0;
                if (bodovi === 0) ocena = 0;
                else if (bodovi <= 50) ocena = 5;
                else if (bodovi >= 51 && bodovi <= 60) ocena = 6;
                else if (bodovi >= 61 && bodovi <= 70) ocena = 7;
                else if (bodovi >= 71 && bodovi <= 80) ocena = 8;
                else if (bodovi >= 81 && bodovi <= 90) ocena = 9;
                else if (bodovi >= 91 && bodovi <= 100) ocena = 10;
                else ocena = 0;
                konacnaOcena.value = ocena;
                konacnaOcenaSlovima.value = ocena;
            }

            if (brojBodova) {
                brojBodova.addEventListener('input', izracunajOcenu);
                // Trigger on load for edit
                izracunajOcenu();
            }
            if (konacnaOcena) {
                konacnaOcena.addEventListener('change', function () {
                    konacnaOcenaSlovima.value = konacnaOcena.value;
                });
            }
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
