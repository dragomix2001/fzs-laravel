@extends('layouts.layout')
@section('page_heading','Пријава теме дипломског рада')
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
                <h3 class="text-lg font-semibold">Пријава теме дипломског рада</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{ url('/prijava/storeDiplomskiTema') }}">
                {{ csrf_field() }}
                <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">
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
                                      :value="$kandidat->program?->naziv ?? ''" disabled />
                    </div>
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="lg:col-span-2">
                        <x-form-select name="predmet_id" label="Дипломски рад из предмета"
                                       :options="$predmeti->mapWithKeys(fn($i) => [$i->id => $i->predmet?->naziv ?? '-'])->toArray()"
                                       placeholder=" " />
                    </div>
                    <div class="lg:col-span-2">
                        <x-form-input name="nazivTeme" label="Назив теме:" />
                    </div>
                    <x-form-input name="formatDatum" label="Датум"
                                  value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"
                                  class="dateMask" />
                    <input type="hidden" name="datum" id="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                </div>

                <hr class="my-6 border-secondary-200">

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="indikatorOdobreno" value="1" id="indikatorOdobreno"
                               class="mt-1 rounded border-secondary-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        <label for="indikatorOdobreno" class="font-semibold text-secondary-700">Тема одобрена</label>
                    </div>
                    <div class="lg:w-8/12">
                        <x-form-select name="profesor_id" label="Тему одобрио:"
                                       :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()"
                                       placeholder=" " />
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
        });
    </script>
    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
@endsection
