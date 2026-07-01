@extends('layouts.layout')
@section('page_heading','Унос кандидата за мастер студије')
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

        <form role="form" method="post" action="{{ url('/kandidat/create_master') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <x-card>
                <x-slot:header>Основни подаци</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="ImeKandidata" label="Име кандидата" required />
                        <x-form-input name="PrezimeKandidata" label="Презиме кандидата" required />
                    </div>
                    <x-form-input name="JMBG" label="ЈМБГ" required />
                    <x-form-input name="DatumRodjenja" label="Датум рођења" type="date" />
                    <x-form-select name="MestoRodjenja" label="Место рођења" :options="$mestoRodjenja->pluck('NazivMesta', 'id')->toArray()" />
                    <x-form-input name="KontaktTelefon" label="Контакт телефон" />
                    <x-form-input name="Email" label="Email" type="email" />
                    <x-form-input name="AdresaStanovanja" label="Адреса становања" />
                    <x-form-input name="ImePrezimeJednogRoditelja" label="Име родитеља" />
                    <x-form-input name="KontaktTelefonRoditelja" label="Контакт телефон родитеља" />
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Школовање</x-slot:header>
                <div class="space-y-4">
                    <x-form-select name="NazivSkoleFakulteta" label="Назив школе или факултета"
                        :options="$nazivSkoleFakulteta->pluck('NazivSkoleFakulteta', 'id')->toArray()" />
                    <x-form-select name="MestoZavrseneSkoleFakulteta" label="Место завршене школе"
                        :options="$mestoZavrseneSkoleFakulteta->pluck('NazivMesta', 'id')->toArray()" />
                    <x-form-input name="SmerZavrseneSkoleFakulteta" label="Смер" />
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Спортско ангажовање</x-slot:header>
                <div class="space-y-4">
                    <x-form-select name="SportskoAngazovanje" label="Спортско ангажовање"
                        :options="$sportskoAngazovanje->pluck('NazivKluba', 'id')->toArray()" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="VisinaKandidata" label="Висина (cm)" />
                        <x-form-input name="TelesnaTezinaKandidata" label="Тежина (kg)" />
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Упис</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="BrojBodovaTest" label="Број бодова тест" />
                        <x-form-input name="BrojBodovaSkola" label="Број бодова школа" />
                    </div>
                    <x-form-input name="UpisniRok" label="Уписни рок" />
                    <x-form-select name="SkolskeGodineUpisa" label="Школска година"
                        :options="$skolskeGodineUpisa->pluck('NazivSkolskeGodineUpisa', 'id')->toArray()" />
                    <x-form-select name="StudijskiProgram" label="Студијски програм"
                        :options="$studijskiProgram->pluck('NazivStudijskogPrograma', 'id')->toArray()" />
                    <x-form-select name="TipStudija" label="Тип студија"
                        :options="$tipStudija->pluck('Naziv', 'id')->toArray()" />
                    <x-form-select name="GodinaStudija" label="Година студија"
                        :options="$godinaStudija->pluck('Naziv', 'id')->toArray()" />
                </div>
            </x-card>

            <div class="flex justify-end">
                <x-button type="submit" size="lg">Submit</x-button>
            </div>
        </form>
    </div>
@endsection
