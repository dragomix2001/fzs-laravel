@extends('layouts.layout')
@section('page_heading','Унос кандидата - прва страна')
@section('section')
    <div class="max-w-4xl mx-auto space-y-6">
        @if (Session::get('errors'))
            <x-alert type="danger" title="Грешка!">
                <ul class="list-disc list-inside">
                    @foreach (Session::get('errors')->all() as $error)
                        <li>{!! $error !!}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form role="form" method="post" action="{{ url('/kandidat') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="page" value="1"/>

            <x-card>
                <x-slot:header>Студијски програм</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="StudijskiProgram" label="Студијски програм"
                            :options="$studijskiProgram->pluck('naziv', 'id')->toArray()" required />
                        <x-form-select name="SkolskeGodineUpisa" label="Школска година"
                            :options="$skolskeGodineUpisa->pluck('naziv', 'id')->toArray()" required />
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Основни подаци</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="ImeKandidata" label="Име кандидата" required value="{{ old('ImeKandidata') }}" />
                        <x-form-input name="PrezimeKandidata" label="Презиме кандидата" required value="{{ old('PrezimeKandidata') }}" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Слика</label>
                        <input type="file" accept="image/*" name="imageUpload" id="imageUpload"
                               class="block w-full text-sm text-secondary-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="JMBG" label="ЈМБГ" value="{{ old('JMBG') }}" />
                        <x-form-input name="DatumRodjenja" label="Датум рођења" class="dateMask" value="{{ old('DatumRodjenja') }}" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="mestoRodjenja" class="block text-sm font-medium text-secondary-700 mb-1">Место рођења</label>
                            <input type="text" name="mestoRodjenja" id="mestoRodjenja" list="mestaList"
                                   value="{{ old('mestoRodjenja') }}"
                                   class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <datalist id="mestaList">
                                @foreach($mestoRodjenja as $item)
                                    <option value="{{$item->naziv}}">
                                @endforeach
                            </datalist>
                        </div>
                        <x-form-input name="drzavaRodjenja" label="Држава рођења" value="{{ old('drzavaRodjenja') }}" />
                    </div>

                    <x-form-select name="KrsnaSlava" label="Крсна слава" :options="$krsnaSlava->pluck('naziv', 'id')->toArray()" placeholder="" class="auto-combobox" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="KontaktTelefon" label="Контакт телефон" value="{{ old('KontaktTelefon') }}" />
                    </div>

                    <x-form-input name="AdresaStanovanja" label="Адреса становања" value="{{ old('AdresaStanovanja') }}" class="max-w-lg" />
                    <x-form-input name="Email" label="Email" type="email" value="{{ old('Email') }}" class="max-w-md" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="ImePrezimeJednogRoditelja" label="Име једног родитеља" value="{{ old('ImePrezimeJednogRoditelja') }}" />
                        <x-form-input name="KontaktTelefonRoditelja" label="Контакт телефон родитеља" value="{{ old('KontaktTelefonRoditelja') }}" />
                    </div>

                    <hr class="border-secondary-200">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="NazivSkoleFakulteta" label="Назив школе или факултета" value="{{ old('NazivSkoleFakulteta') }}" />
                        <x-form-input name="SmerZavrseneSkoleFakulteta" label="Смер завршене школе или факултета" value="{{ old('SmerZavrseneSkoleFakulteta') }}" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="mestoZavrseneSkoleFakulteta" class="block text-sm font-medium text-secondary-700 mb-1">Место завршене школе или факултета</label>
                            <input type="text" class="block w-full rounded-lg border-secondary-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                   id="mestoZavrseneSkoleFakulteta" name="mestoZavrseneSkoleFakulteta" list="mestaList">
                        </div>
                        <x-form-input name="drzavaZavrseneSkole" label="Држава завршене школе или факултета" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="godinaZavrsetkaSkole" label="Година завршетка школе или факултета" />
                        <x-form-select name="GodinaStudija" label="Година студија (на коју се кандидат уписује)"
                            :options="$godinaStudija->pluck('naziv', 'id')->toArray()" />
                    </div>

                    <div class="flex justify-center pt-4">
                        <x-button type="submit" name="submit" value="submit" size="lg">Даље</x-button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>

    @push('scripts')
    <script src="{{"/"}}js/kandidat-create-part-1.js"></script>
    <script src="{{"/"}}js/dateMask.js"></script>
    <script src="{{"/"}}js/jquery-ui-autocomplete.js"></script>
    @endpush
@endsection
