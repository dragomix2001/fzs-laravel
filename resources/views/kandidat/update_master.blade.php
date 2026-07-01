@extends('layouts.layout')
@section('page_heading', $kandidat->upisan == 0 ? 'Измена података кандидата за мастер студије' : 'Измена података активног студента мастер студија')
@section('section')
    <div class="max-w-5xl mx-auto space-y-6">
        @if (Session::get('errors'))
            <x-alert type="danger" title="Грешка!">
                <ul class="list-disc list-inside">
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form role="form" method="post" action="{{"/"}}master/{{ $kandidat->id }}/edit" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-card>
                <x-slot:header>Документа</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="TipStudija" label="Тип студија"
                            :options="$tipStudija->pluck('naziv', 'id')->toArray()" :selected="$kandidat->tipStudija_id" />
                        <x-form-select name="StudijskiProgram" label="Студијски програм"
                            :options="$studijskiProgram->pluck('naziv', 'id')->toArray()" :selected="$kandidat->studijskiProgram_id" />
                    </div>
                    <x-form-select name="SkolskeGodineUpisa" label="Школска година"
                        :options="$skolskeGodineUpisa->pluck('naziv', 'id')->toArray()" :selected="$kandidat->skolskaGodinaUpisa_id" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="statusUpisa_id" label="Статус"
                            :options="$statusKandidata->pluck('naziv', 'id')->toArray()" :selected="$kandidat->statusUpisa_id" />
                        <x-form-input name="datumStatusa" label="Датум статуса" class="dateMask"
                            value="{{ !empty($kandidat->datumStatusa) ? $kandidat->datumStatusa->format('d.m.Y.') : '' }}" />
                    </div>

                    <p class="text-sm font-medium text-secondary-700">Уз пријаву прилажем:</p>
                    @foreach($dokumentaMaster as $i=>$dokument)
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="dokumentaMaster[{{ $i }}]" value="{{$dokument->id}}"
                               {{ (in_array($dokument->id, $prilozenaDokumenta) ? "checked":"") }}
                               class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                        <div class="flex-1">
                            <label class="text-sm text-secondary-700">{{ $dokument->naziv }}</label>
                            <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 mt-1" name="dokumentaMasterUpload[{{ $dokument->id }}]" accept=".pdf,.jpg,.jpeg,.png">
                            @if(!empty($prilozenaDokumentaFajlovi[$dokument->id]))
                                <a class="inline-flex items-center text-sm text-primary-600 hover:text-primary-800 mt-1" target="_blank" href="{{ '/' . ltrim('uploads/' . $prilozenaDokumentaFajlovi[$dokument->id], '/') }}">
                                    {{ $prilozenaDokumentaNazivi[$dokument->id] ?? 'Погледај документ' }}
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Основни подаци</x-slot:header>
                <div class="space-y-4">
                    <x-form-input name="brojIndeksa" label="Број индекса" value="{{ $kandidat->brojIndeksa }}" />

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="text-center">
                            <img src="{{"/"}}uploads/images/{{$kandidat->slika}}"
                                 class="mx-auto rounded-lg shadow-sm ring-1 ring-black/5 max-h-[300px]">
                        </div>
                        <div class="lg:col-span-2 space-y-4">
                            <x-form-input name="ImeKandidata" label="Име кандидата" value="{{ $kandidat->imeKandidata }}" />
                            <x-form-input name="PrezimeKandidata" label="Презиме кандидата" value="{{ $kandidat->prezimeKandidata }}" />
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 mb-1">Нова слика</label>
                                <input type="file" name="imageUpload" id="imageUpload"
                                       class="block w-full text-sm text-secondary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary-200">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="JMBG" label="ЈМБГ" value="{{ $kandidat->jmbg }}" />
                        <div>
                            <label for="mestoRodjenja" class="block text-sm font-medium text-secondary-700 mb-1">Место рођења</label>
                            <input type="text" name="mestoRodjenja" id="mestoRodjenja" list="mestaList"
                                   value="{{ $kandidat->mestoRodjenja }}"
                                   class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <datalist id="mestaList">
                                @foreach($mestoRodjenja as $item)
                                    <option value="{{$item->naziv}}">
                                @endforeach
                            </datalist>
                        </div>
                        <x-form-input name="drzavaRodjenja" label="Држава рођења" value="{{ $kandidat->drzavaRodjenja }}" />
                        <x-form-input name="KontaktTelefon" label="Контакт телефон" value="{{ $kandidat->kontaktTelefon }}" />
                        <x-form-input name="Email" label="Email" type="email" value="{{ $kandidat->email }}" />
                    </div>

                    <x-form-input name="AdresaStanovanja" label="Адреса становања" value="{{ $kandidat->adresaStanovanja }}" />

                    <hr class="border-secondary-200">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="NazivSkoleFakulteta" label="Назив школе или факултета" value="{{ $kandidat->srednjeSkoleFakulteti }}" />
                        <x-form-input name="SmerZavrseneSkoleFakulteta" label="Смер завршене школе или факултета" value="{{ $kandidat->smerZavrseneSkoleFakulteta }}" />
                        <div>
                            <label for="mestoZavrseneSkoleFakulteta" class="block text-sm font-medium text-secondary-700 mb-1">Место завршене школе или факултета</label>
                            <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                   id="mestoZavrseneSkoleFakulteta" name="mestoZavrseneSkoleFakulteta" list="mestaList"
                                   value="{{ $kandidat->mestoZavrseneSkoleFakulteta }}">
                        </div>
                        <x-form-input name="drzavaZavrseneSkole" label="Држава завршене школе или факултета" value="{{ $kandidat->drzavaZavrseneSkole }}" />
                        <x-form-input name="godinaZavrsetkaSkole" label="Година завршетка школе или факултета" value="{{ $kandidat->godinaZavrsetkaSkole }}" />
                    </div>

                    <hr class="border-secondary-200">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="ProsecnaOcena" label="Просечна оцена" value="{{ $kandidat->prosecnaOcena }}" />
                        <x-form-input name="UpisniRok" label="Уписни рок" value="{{ $kandidat->upisniRok }}" />
                    </div>

                    <div class="flex justify-center gap-3 pt-4">
                        <x-button type="submit" name="Submit" size="lg">Сачувај</x-button>
                        <x-button type="submit" name="submitstay" value="Сачувај и остани" variant="secondary" size="lg">Сачувај и остани</x-button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                return false;
            }
        });
    </script>
    <script src="{{"/"}}js/dateMask.js"></script>
    <script src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    @endpush
@endsection
