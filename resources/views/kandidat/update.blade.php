@extends('layouts.layout')
@section('page_heading', $kandidat->upisan == 0 ? 'Измена података постојећег кандидата' : 'Измена података активног студента')
@section('section')
    <form role="form" method="post" action="{{ url('/kandidat/' . $kandidat->id) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" value="put"/>

        <div class="space-y-6">
            @if (Session::get('errors'))
                <x-alert type="danger" title="Грешка!">
                    <ul class="list-disc list-inside">
                        @foreach (Session::get('errors')->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- LEFT COLUMN --}}
                <div class="space-y-6">
                    <x-card>
                        <x-slot:header>Студијски програм</x-slot:header>
                        <div class="space-y-4">
                            <x-form-select name="TipStudija" label="Тип студија"
                                :options="$tipStudija->pluck('naziv', 'id')->toArray()" :selected="$kandidat->tipStudija_id" />
                            <x-form-select name="StudijskiProgram" label="Студијски програм"
                                :options="$studijskiProgram->pluck('naziv', 'id')->toArray()" :selected="$kandidat->studijskiProgram_id" />
                            <x-form-select name="SkolskeGodineUpisa" label="Школска година"
                                :options="$skolskeGodineUpisa->pluck('naziv', 'id')->toArray()" :selected="$kandidat->skolskaGodinaUpisa_id" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-select name="statusUpisa_id" label="Статус"
                                    :options="$statusKandidata->pluck('naziv', 'id')->toArray()" :selected="$kandidat->statusUpisa_id" />
                                <x-form-input name="datumStatusa" label="Датум статуса" class="dateMask"
                                    value="{{ !empty($kandidat->datumStatusa) ? $kandidat->datumStatusa->format('d.m.Y.') : '' }}" />
                            </div>
                        </div>
                    </x-card>

                    <x-card>
                        <x-slot:header>Основни подаци</x-slot:header>
                        <div class="space-y-4">
                            <x-form-input name="brojIndeksa" label="Број индекса" value="{{ $kandidat->brojIndeksa }}" />

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <img src="{{"/"}}uploads/images/{{$kandidat->slika}}"
                                         class="mx-auto rounded-lg shadow-sm ring-1 ring-black/5 max-h-[300px]">
                                </div>
                                <div class="sm:col-span-2 space-y-4">
                                    <x-form-input name="ImeKandidata" label="Име кандидата" value="{{ $kandidat->imeKandidata }}" />
                                    <x-form-input name="PrezimeKandidata" label="Презиме кандидата" value="{{ $kandidat->prezimeKandidata }}" />
                                    <div>
                                        <label class="block text-sm font-medium text-secondary-700 mb-1">Нова слика</label>
                                        <input type="file" accept="image/*" name="imageUpload" id="imageUpload"
                                               class="block w-full text-sm text-secondary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-input name="JMBG" label="ЈМБГ" value="{{ $kandidat->jmbg }}" />
                                <x-form-input name="DatumRodjenja" label="Датум рођења" class="dateMask"
                                    value="{{ date('d.m.Y.', strtotime($kandidat->datumRodjenja)) }}" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            </div>

                            <x-form-select name="KrsnaSlava" label="Крсна слава"
                                :options="$krsnaSlava->pluck('naziv', 'id')->toArray()"
                                :selected="$kandidat->krsnaSlava_id" placeholder="" />

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-input name="KontaktTelefon" label="Контакт телефон" value="{{ $kandidat->kontaktTelefon }}" />
                            </div>

                            <x-form-input name="AdresaStanovanja" label="Адреса становања" value="{{ $kandidat->adresaStanovanja }}" />
                            <x-form-input name="Email" label="Email" type="email" value="{{ $kandidat->email }}" />

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-input name="ImePrezimeJednogRoditelja" label="Име родитеља" value="{{ $kandidat->imePrezimeJednogRoditelja }}" />
                                <x-form-input name="KontaktTelefonRoditelja" label="Контакт телефон родитеља" value="{{ $kandidat->kontaktTelefonRoditelja }}" />
                            </div>

                            <hr class="border-secondary-200">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-input name="NazivSkoleFakulteta" label="Назив школе или факултета" value="{{ $kandidat->srednjeSkoleFakulteti }}" />
                                <x-form-input name="SmerZavrseneSkoleFakulteta" label="Смер" value="{{ $kandidat->smerZavrseneSkoleFakulteta }}" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="mestoZavrseneSkoleFakulteta" class="block text-sm font-medium text-secondary-700 mb-1">Место завршене школе или факултета</label>
                                    <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                           id="mestoZavrseneSkoleFakulteta" name="mestoZavrseneSkoleFakulteta" list="mestaList"
                                           value="{{ $kandidat->mestoZavrseneSkoleFakulteta }}">
                                </div>
                                <x-form-input name="drzavaZavrseneSkole" label="Држава завршене школе" value="{{ $kandidat->drzavaZavrseneSkole }}" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <x-form-input name="godinaZavrsetkaSkole" label="Година завршетка" value="{{ $kandidat->godinaZavrsetkaSkole }}" />
                                <x-form-select name="GodinaStudija" label="Година студија"
                                    :options="$godinaStudija->pluck('naziv', 'id')->toArray()" :selected="$kandidat->godinaStudija_id" />
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-6">
                    <x-card>
                        <x-slot:header>Само за прву годину</x-slot:header>
                        <div class="space-y-4">
                            @foreach([['key'=>'prvi','num'=>'1'], ['key'=>'drugi','num'=>'2'], ['key'=>'treci','num'=>'3'], ['key'=>'cetvrti','num'=>'4']] as $razred)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-select name="{{ $razred['key'] }}Razred" label="{{ $razred['num'] }}. разред"
                                    :options="$opstiUspehSrednjaSkola->pluck('naziv', 'id')->toArray()"
                                    :selected="${$razred['key'] . 'Razred'}->opstiUspeh_id ?? ''" />
                                <x-form-input name="SrednjaOcena{{ $razred['num'] }}" label="Средња оцена"
                                    value="{{ number_format((float)${$razred['key'] . 'Razred'}->srednja_ocena ?? 0, 2, '.', '') }}" />
                            </div>
                            @endforeach

                            <hr class="border-secondary-200">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-form-select name="OpstiUspehSrednjaSkola" label="Општи успех средња школа"
                                    :options="$opstiUspehSrednjaSkola->pluck('naziv', 'id')->toArray()"
                                    :selected="$kandidat->opstiUspehSrednjaSkola_id ?? ''" />
                                <x-form-input name="SrednjaOcenaSrednjaSkola" label="Средња оцена средња школа"
                                    value="{{ number_format((float)$kandidat->srednjaOcenaSrednjaSkola, 2, '.', '') }}" />
                            </div>
                        </div>
                    </x-card>

                    <x-card>
                        <x-slot:header>Спортско ангажовање</x-slot:header>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-secondary-200">
                                <thead class="bg-secondary-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Спорт</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase w-24"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-secondary-200">
                                    @foreach($sportskoAngazovanjeKandidata as $angazovanje)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-secondary-900">{{ $sport->find($angazovanje->sport_id)->naziv ?? '' }}</td>
                                        <td class="px-4 py-3">
                                            <a href="{{"/"}}kandidat/{{ $kandidat->id }}/sportskoangazovanje" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors">
                                                Додај
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card>

                    <x-card>
                        <x-slot:header>Висина и тежина</x-slot:header>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <x-form-input name="VisinaKandidata" label="Висина кандидата (cm)" value="{{ $kandidat->visina }}" />
                            <x-form-input name="TelesnaTezinaKandidata" label="Телесна тежина (kg)" value="{{ $kandidat->telesnaTezina }}" />
                        </div>
                    </x-card>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <x-card>
                            <x-slot:header>ДОКУМЕНТА - I година студија</x-slot:header>
                            <div class="space-y-3">
                                @foreach($dokumentiPrvaGodina as $i=>$dokument)
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="dokumentiPrva[{{ $i }}]" value="{{$dokument->id}}"
                                           {{ (in_array($dokument->id, $prilozenaDokumenta) ? "checked":"") }}
                                           class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                                    <div class="flex-1">
                                        <label class="text-sm text-secondary-700">{{ $dokument->naziv }}</label>
                                        <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 mt-1" name="documentUploadsPrva[{{ $dokument->id }}]" accept=".pdf,.jpg,.jpeg,.png">
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
                            <x-slot:header>ДОКУМЕНТА - II, III и IV година</x-slot:header>
                            <div class="space-y-3">
                                @foreach($dokumentiOstaleGodine as $i=>$dokument)
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" name="dokumentiDruga[{{ $i }}]" value="{{$dokument->id}}"
                                           {{ (in_array($dokument->id, $prilozenaDokumenta) ? "checked":"") }}
                                           class="mt-1 rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                                    <div class="flex-1">
                                        <label class="text-sm text-secondary-700">{{ $dokument->naziv }}</label>
                                        <input type="file" class="block w-full text-sm text-secondary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 mt-1" name="documentUploadsDruga[{{ $dokument->id }}]" accept=".pdf,.jpg,.jpeg,.png">
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
                    </div>

                    <x-card>
                        <x-slot:header>Остало</x-slot:header>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <x-form-input name="BrojBodovaTest" label="Број бодова тест" value="{{ $kandidat->brojBodovaTest }}" />
                                <x-form-input name="BrojBodovaSkola" label="Број бодова школа" value="{{ $kandidat->brojBodovaSkola }}" />
                                <x-form-input name="ukupniBrojBodova" label="Укупни број бодова" value="{{ $kandidat->ukupniBrojBodova }}" />
                            </div>
                            <x-form-input name="UpisniRok" label="Уписни рок" value="{{ $kandidat->upisniRok }}" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 mb-1">Додај дипломски рад</label>
                                    <input type="file" accept="application/pdf" name="pdfUpload" id="pdfUpload"
                                           class="block w-full text-sm text-secondary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                                </div>
                                @if(!empty($kandidat->diplomski))
                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 mb-1">Дипломски рад</label>
                                    <a href="/uploads/pdf/{{$kandidat->diplomski}}" target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                                        </svg>
                                        Дипломски рад
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- SAVE SECTION --}}
            <x-card>
                <x-slot:header>Сачувај</x-slot:header>
                <div class="flex flex-wrap justify-center gap-3">
                    <x-button type="submit" variant="success" size="lg">Сачувај</x-button>
                    <x-button type="submit" name="submitstay" value="Сачувај и остани" variant="success" size="lg">Сачувај и остани</x-button>
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <a href="{{ route('kandidat.documents.review', $kandidat->id) }}" class="inline-flex items-center px-6 py-3 font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Преглед документације
                        </a>
                    @endif
                </div>
            </x-card>
        </div>
    </form>

    @push('scripts')
    <script src="{{"/"}}js/kandidat-create-part-1.js"></script>
    <script src="{{"/"}}js/kandidat-create-part-2.js"></script>
    <script src="{{"/"}}js/dateMask.js"></script>
    <script src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    @endpush
@endsection
