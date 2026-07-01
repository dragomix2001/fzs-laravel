@extends('layouts.layout')
@section('page_heading','Unos kandidata')
@section('section')
    <div class="max-w-4xl mx-auto space-y-6">
        <form role="form" method="post" action="{{ url('kandidat/create') }}" class="space-y-6">
            @csrf

            <x-card>
                <x-slot:header>Основни подаци</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="ImeKandidata" label="Ime Kandidata" required />
                        <x-form-input name="PrezimeKandidata" label="Prezime Kandidata" required />
                    </div>
                    <x-form-input name="JMBG" label="JMBG" class="max-w-md" />
                    <x-form-input name="DatumRodjenja" label="Datum Rodjenja" type="date" class="max-w-md" />
                    <x-form-select name="MestoRodjenja" label="MestoRodjenja" :options="$mestoRodjenja->pluck('NazivMesta', 'id')->toArray()" class="max-w-md" />
                    <x-form-select name="KrsnaSlava" label="KrsnaSlava" :options="$krsnaSlava->pluck('NazivSlave', 'id')->toArray()" class="max-w-md" />
                    <x-form-input name="KontaktTelefon" label="Kontakt Telefon" class="max-w-sm" />
                    <x-form-input name="AdresaStanovanja" label="AdresaStanovanja" class="max-w-lg" />
                    <x-form-input name="Email" label="Email" type="email" class="max-w-md" />
                    <x-form-input name="ImePrezimeJednogRoditelja" label="ImePrezimeJednogRoditelja" class="max-w-lg" />
                    <x-form-input name="KontaktTelefonRoditelja" label="KontaktTelefonRoditelja" class="max-w-sm" />
                    <x-form-select name="NazivSkoleFakulteta" label="NazivSkoleFakulteta"
                        :options="$nazivSkoleFakulteta->pluck('NazivSkoleFakulteta', 'id')->toArray()" class="max-w-lg" />
                    <x-form-select name="MestoZavrseneSkoleFakulteta" label="MestoZavrseneSkoleFakulteta"
                        :options="$mestoZavrseneSkoleFakulteta->pluck('NazivMesta', 'id')->toArray()" class="max-w-md" />
                    <x-form-input name="SmerZavrseneSkoleFakulteta" label="SmerZavrseneSkoleFakulteta" class="max-w-lg" />
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Samo za prvu godinu</x-slot:header>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-select name="UspehSrednjaSkola" label="UspehSrednjaSkola"
                            :options="$uspehSrednjaSkola->pluck('Naziv', 'id')->toArray()" />
                        <x-form-select name="OpstiUspehSrednjaSkola" label="OpstiUspehSrednjaSkola"
                            :options="$opstiUspehSrednjaSkola->pluck('Naziv', 'id')->toArray()" />
                    </div>
                    <x-form-input name="SrednjaOcenaSrednjaSkola" label="SrednjaOcenaSrednjaSkola" />
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Sportsko angazovanje</x-slot:header>
                <div class="space-y-4">
                    <x-form-select name="SportskoAngazovanje" label="SportskoAngazovanje"
                        :options="$sportskoAngazovanje->pluck('NazivKluba', 'id')->toArray()" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="TelesnaTezinaKandidata" label="TelesnaTezinaKandidata" />
                        <x-form-input name="VisinaKandidata" label="VisinaKandidata" />
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>Upis</x-slot:header>
                <div class="space-y-4">
                    <x-form-select name="PrilozeniDokumentPrvaGodina" label="PrilozeniDokumentPrvaGodina"
                        :options="$prilozeniDokumentPrvaGodina->pluck('NazivDokumenta', 'id')->toArray()" />
                    <x-form-select name="StatusaUpisaKandidata" label="StatusaUpisaKandidata"
                        :options="$statusaUpisaKandidata->pluck('NazivStatusaStudiranja', 'id')->toArray()" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form-input name="BrojBodovaTest" label="BrojBodovaTest" />
                        <x-form-input name="BrojBodovaSkola" label="BrojBodovaSkola" />
                    </div>
                    <x-form-input name="UpisniRok" label="UpisniRok" />
                    <x-form-select name="SkolskeGodineUpisa" label="SkolskeGodineUpisa"
                        :options="$skolskeGodineUpisa->pluck('NazivSkolskeGodineUpisa', 'id')->toArray()" />
                    <x-form-input name="IndikatorAktivan" label="IndikatorAktivan" />
                    <x-form-select name="StudijskiProgram" label="StudijskiProgram"
                        :options="$studijskiProgram->pluck('NazivStudijskogPrograma', 'id')->toArray()" />
                    <x-form-select name="TipStudija" label="TipStudija"
                        :options="$tipStudija->pluck('Naziv', 'id')->toArray()" />
                    <x-form-select name="GodinaStudija" label="GodinaStudija"
                        :options="$godinaStudija->pluck('Naziv', 'id')->toArray()" />
                    <x-form-select name="MestoZavrseneSkoleFakulteta" label="MestoZavrseneSkoleFakulteta"
                        :options="$mestoZavrseneSkoleFakulteta->pluck('NazivMesta', 'id')->toArray()" />
                </div>
            </x-card>

            <div class="flex justify-end">
                <x-button type="submit">Submit</x-button>
            </div>
        </form>
    </div>
@endsection
