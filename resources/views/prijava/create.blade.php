@extends('layouts.layout')
@section('page_heading','Пријава за полагање испита')
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
            @if(!empty($kandidat))
                <a href="/prijava/zaStudenta/{{ $kandidat->id }}">
                    <x-button variant="primary">Назад на студента</x-button>
                </a>
            @else
                <a href="/prijava/zaPredmet/{{ $predmet->predmet_id }}">
                    <x-button variant="primary">Назад на предмет</x-button>
                </a>
            @endif
        </div>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold">Пријава за полагање испита</h3>
            </x-slot:header>
            <form role="form" method="post" action="{{ url('/prijava/') }}">
                {{ csrf_field() }}

                @if(!empty($kandidat))
                    {{-- KANDIDAT FLOW --}}
                    <input type="hidden" name="kandidat_id" id="kandidat_id" value="{{ $kandidat->id }}">
                    <input type="hidden" name="tipStudija_id" id="tipStudija_id" value="{{ $kandidat->tipStudija_id }}">
                    <input type="hidden" name="studijskiProgram_id" id="studijskiProgram_id" value="{{ $kandidat->studijskiProgram_id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex items-end gap-4">
                            <div class="w-1/2">
                                <x-form-select name="brojIndeksa" id="brojIndeksa" label="Број Индекса"
                                               :options="$brojeviIndeksa->pluck('naziv','id')->toArray()"
                                               :selected="$kandidat->studijskiProgram_id" />
                            </div>
                            <div>
                                <x-button variant="success" id="asdasd">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </x-button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                        <x-form-input name="jmbg" id="jmbg" label="ЈМБГ" :value="$kandidat->jmbg" disabled />
                        <div class="lg:col-span-2">
                            <x-form-select name="StudijskiProgram" id="StudijskiProgram" label="Студијски програм"
                                           :options="$studijskiProgram->pluck('naziv','id')->toArray()"
                                           :selected="$kandidat->studijskiProgram_id" disabled />
                        </div>
                        <x-form-input name="imeKandidata" id="imeKandidata" label="Име" :value="$kandidat->imeKandidata" disabled />
                        <x-form-input name="prezimeKandidata" id="prezimeKandidata" label="Презиме" :value="$kandidat->prezimeKandidata" disabled />
                    </div>

                    <hr class="my-6 border-secondary-200">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="lg:col-span-2">
                            <x-form-select name="predmet_id" id="predmet_id" label="Пријављујем се за полагање испита из предмета"
                                           :options="$predmeti->mapWithKeys(fn($i) => [$i->id => $i->predmet?->naziv ?? '-'])->toArray()" />
                        </div>
                        <x-form-select name="tipPredmeta_id" id="tipPredmeta_id" label="Тип предмета:"
                                       :options="$tipPredmeta->pluck('naziv','id')->toArray()" disabled />
                        <x-form-select name="godinaStudija_id" id="godinaStudija_id" label="Година студија"
                                       :options="$godinaStudija->pluck('naziv','id')->toArray()"
                                       :selected="$kandidat->godinaStudija_id" disabled />
                        <x-form-select name="tipStudija_id" id="tipStudija_id" label="Тип студија:"
                                       :options="$tipStudija->pluck('naziv','id')->toArray()"
                                       :selected="$kandidat->tipStudija_id" disabled />
                    </div>

                    <hr class="my-6 border-secondary-200">

                    <div class="lg:w-8/12">
                        <x-form-select name="profesor_id" id="profesor_id" label="Професор"
                                       :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()" />
                    </div>

                @else
                    {{-- PREDMET FLOW --}}
                    <input type="hidden" name="prijava_za_predmet" value="1">
                    <input type="hidden" name="kandidat_id" id="kandidat_id" value="">
                    <input type="hidden" name="tipStudija_id" id="tipStudija_id" value="{{ $predmet->tipStudija_id }}">
                    <input type="hidden" name="studijskiProgram_id" id="studijskiProgram_id" value="{{ $predmet->studijskiProgram_id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex items-end gap-4">
                            <div class="w-1/2">
                                <x-form-select name="brojIndeksa" id="brojIndeksa" label="Број Индекса"
                                               :options="$brojeviIndeksa->pluck('naziv','id')->toArray()" />
                            </div>
                            <div>
                                <x-button variant="success" id="asdasd">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </x-button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                        <x-form-input name="jmbg" id="jmbg" label="ЈМБГ" value="" disabled />
                        <div class="lg:col-span-2">
                            <x-form-select name="StudijskiProgram" id="StudijskiProgram" label="Студијски програм"
                                           :options="$studijskiProgram->pluck('naziv','id')->toArray()"
                                           :selected="($predmet->program?->id ?? null)" disabled />
                        </div>
                        <x-form-input name="imeKandidata" id="imeKandidata" label="Име" value="" disabled />
                        <x-form-input name="prezimeKandidata" id="prezimeKandidata" label="Презиме" value="" disabled />
                    </div>

                    <hr class="my-6 border-secondary-200">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="lg:col-span-2">
                            <x-form-input name="predmet_display" label="Пријављујем се за полагање испита из предмета"
                                          value="{{ 'Семестар ' . $predmet->semestar . ': ' . ($predmet->predmet?->naziv ?? '-') }}" disabled />
                            <input type="hidden" name="predmet_id" id="predmet_id" value="{{ $predmet->id }}">
                        </div>
                        <x-form-select name="tipPredmeta_id" id="tipPredmeta_id" label="Тип предмета:"
                                       :options="$tipPredmeta->pluck('naziv','id')->toArray()"
                                       :selected="$predmet->tipPredmeta_id" disabled />
                        <x-form-select name="godinaStudija_id" id="godinaStudija_id" label="Година студија"
                                       :options="$godinaStudija->pluck('naziv','id')->toArray()"
                                       :selected="$predmet->godinaStudija_id" disabled />
                        <x-form-select name="tipStudija_id" id="tipStudija_id" label="Тип студија:"
                                       :options="$tipStudija->pluck('naziv','id')->toArray()"
                                       :selected="$predmet->tipStudija_id" disabled />
                    </div>

                    <hr class="my-6 border-secondary-200">

                    <div class="lg:w-4/5">
                        <x-form-select name="profesor_id" id="profesor_id" label="Професор"
                                       :options="$profesor->mapWithKeys(fn($t) => [$t->id => $t->zvanje . ' ' . $t->ime . ' ' . $t->prezime])->toArray()" />
                    </div>
                @endif

                <hr class="my-6 border-secondary-200">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-form-select name="rok_id" label="Испитни рок"
                                   :options="$ispitniRok->pluck('naziv','id')->toArray()" />
                    <x-form-input name="brojPolaganja" label="Испит полажем (редни број полагања)" value="1" />
                    <x-form-input name="formatDatum" label="Датум"
                                  value="{{ Carbon\Carbon::now()->format('d.m.Y.') }}"
                                  class="dateMask" />
                </div>

                <input type="hidden" name="datum" id="datum" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">

                <div class="mt-6 text-center">
                    <x-button variant="success" size="lg" type="submit" name="Submit">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Сачувај
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    <script type="text/javascript" src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    <script type="text/javascript" src="{{"/"}}js/dateMask.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Date input sync
            var formatDatum = document.getElementById('formatDatum');
            if (formatDatum) {
                formatDatum.addEventListener('input', function () {
                    var date = moment(formatDatum.value, "DD.MM.YYYY");
                    document.getElementById('datum').value = date.isValid() ? date.format('YYYY-MM-DD') : '';
                });
            }

            // Prevent Enter key form submission
            document.addEventListener('keydown', function (event) {
                if (event.keyCode === 13 && event.target.tagName !== 'TEXTAREA') {
                    event.preventDefault();
                    return false;
                }
            });
        });

        // jQuery for AJAX operations (preserved - complex DataTables/AJAX)
        $(document).ready(function () {
            var pathname = window.location.pathname;

            // Lookup student by index number
            $('#asdasd').click(function () {
                $.ajax({
                    url: '{{"/"}}prijava/vratiKandidataPrijava',
                    type: 'post',
                    data: {
                        id: $('#brojIndeksa').val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $('#kandidat_id').val(result['student'].id);
                        $('#jmbg').val(result['student'].jmbg);
                        $('#imeKandidata').val(result['student'].imeKandidata);
                        $('#prezimeKandidata').val(result['student'].prezimeKandidata);
                        $('#studijskiProgram_id').val(result['student'].studijskiProgram_id);

                        if (pathname.indexOf('/prijava/predmet/') == -1) {
                            $('#predmet_id').html(result['predmeti']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            });

            // Subject change handler - load professor/tip info
            $('#predmet_id').change(function () {
                $.ajax({
                    url: '{{"/"}}prijava/vratiPredmetPrijava',
                    type: 'post',
                    data: {
                        id: $('#predmet_id').val(),
                        kandidat: $('#kandidat_id').val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function (result) {
                        $('#tipPredmeta_id').val(result['tipPredmeta']);
                        $('#godinaStudija_id').val(result['godinaStudija']);
                        $('#tipStudija_id').val(result['tipStudija']);
                        $('#profesor_id').html(result['profesori']);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });
            });
        });
    </script>
@endsection
