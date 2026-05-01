<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\DTOs\DiplomaAddData;
use App\DTOs\DiplomskiAddData;
use App\DTOs\KandidatData;
use App\DTOs\KandidatPage1Data;
use App\DTOs\KandidatPage2Data;
use App\DTOs\KandidatUpdateData;
use App\DTOs\MasterKandidatData;
use App\DTOs\NastavniPlanData;
use App\DTOs\PrijavaIspitaData;
use App\DTOs\ZapisnikData;
use App\DTOs\ZapisnikStampaData;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DtoCoverageTest extends TestCase
{
    #[Test]
    public function diploma_add_data_from_request_maps_fields(): void
    {
        $request = Request::create('/', 'POST', [
            'kandidat_id' => '5',
            'brojDiplome' => '12/2026',
            'datumOdbrane' => '2026-05-01',
            'nazivStudijskogPrograma' => 'Sportski trener',
            'brojPocetnogLista' => '77',
            'brojZapisnika' => '88',
            'datum' => '2026-04-30',
            'pristupniRad' => 'DA',
            'tema' => 'Tema',
            'mentor' => 'Mentor',
            'ocena' => '10',
        ]);

        $dto = DiplomaAddData::fromRequest($request);

        $this->assertSame(5, $dto->kandidatId);
        $this->assertSame('12/2026', $dto->brojDiplome);
        $this->assertSame('10', $dto->ocena);
    }

    #[Test]
    public function diplomski_add_data_from_request_maps_alternative_fields(): void
    {
        $request = Request::create('/', 'POST', [
            'id' => '10',
            'predmet' => '21',
            'tema' => 'Naziv teme',
            'mentor' => '33',
            'ocenaBroj' => '9,5',
            'datumPrijave' => '2026-01-01',
            'datumOdbrane' => '2026-02-02',
        ]);

        $dto = DiplomskiAddData::fromRequest($request);

        $this->assertSame(10, $dto->kandidatId);
        $this->assertSame(21, $dto->predmetId);
        $this->assertSame('Naziv teme', $dto->naziv);
        $this->assertSame(33, $dto->mentorId);
        $this->assertSame(9.5, $dto->ocenaBroj);
    }

    #[Test]
    public function kandidat_data_from_request_and_to_array_work(): void
    {
        $request = Request::create('/', 'POST', [
            'ImeKandidata' => 'Pera',
            'PrezimeKandidata' => 'Peric',
            'JMBG' => '1234567890123',
            'StudijskiProgram' => '2',
            'TipStudija' => '1',
            'BrojIndeksa' => 'RA-1/2026',
            'GodinaStudija' => '3',
        ]);

        $dto = KandidatData::fromRequest($request);
        $data = $dto->toArray();

        $this->assertSame('Pera', $dto->ime);
        $this->assertSame('RA-1/2026', $dto->brojIndeksa);
        $this->assertSame(2, $data['studijskiProgram_id']);
        $this->assertSame(3, $data['godinaStudija_id']);
    }

    #[Test]
    public function kandidat_page_one_data_from_request_normalizes_values(): void
    {
        $image = UploadedFile::fake()->image('photo.jpg');

        $request = Request::create('/', 'POST', [
            'ImeKandidata' => 'Mika',
            'PrezimeKandidata' => 'Mikic',
            'JMBG' => '999',
            'uplata' => '1',
            'DatumRodjenja' => '01.05.2000.',
            'mestoRodjenja' => 'Beograd',
            'KrsnaSlava' => '2',
            'KontaktTelefon' => '123',
            'AdresaStanovanja' => 'Ulica 1',
            'Email' => 'mika@test.local',
            'ImePrezimeJednogRoditelja' => 'Petar Mikic',
            'KontaktTelefonRoditelja' => '555',
            'NazivSkoleFakulteta' => 'Gimnazija',
            'mestoZavrseneSkoleFakulteta' => 'Novi Sad',
            'SmerZavrseneSkoleFakulteta' => 'Opsti',
            'StudijskiProgram' => '4',
            'SkolskeGodineUpisa' => '8',
            'drzavaZavrseneSkole' => 'SRB',
            'godinaZavrsetkaSkole' => '2019',
            'drzavaRodjenja' => 'SRB',
            'GodinaStudija' => '1',
        ], [], ['imageUpload' => $image]);

        $dto = KandidatPage1Data::fromRequest($request);

        $this->assertTrue($dto->uplata);
        $this->assertSame('Mika', $dto->ime);
        $this->assertSame(4, $dto->studijskiProgramId);
        $this->assertSame($image->getClientOriginalName(), $dto->imageUpload?->getClientOriginalName());
        $this->assertSame('2000', $dto->datumRodjenja?->format('Y'));
    }

    #[Test]
    public function kandidat_page_two_data_from_request_builds_grades_and_sports(): void
    {
        $pdfA = UploadedFile::fake()->create('a.pdf', 10, 'application/pdf');
        $pdfB = UploadedFile::fake()->create('b.pdf', 10, 'application/pdf');

        $request = Request::create('/', 'POST', [
            'insertedId' => '42',
            'prviRazred' => '5',
            'drugiRazred' => '4',
            'treciRazred' => '5',
            'cetvrtiRazred' => '4',
            'SrednjaOcena1' => '4,50',
            'SrednjaOcena2' => '4,00',
            'SrednjaOcena3' => '4,25',
            'SrednjaOcena4' => '5,00',
            'OpstiUspehSrednjaSkola' => '3',
            'SrednjaOcenaSrednjaSkola' => '4,44',
            'sport1' => '7',
            'klub1' => 'FK Test',
            'uzrast1' => 'seniori',
            'godine1' => '5',
            'sport2' => '0',
            'sport3' => '',
            'VisinaKandidata' => '181,5',
            'TelesnaTezinaKandidata' => '79,2',
            'dokumentiPrva' => [1, '', 3],
            'dokumentiDruga' => [2, null],
            'BrojBodovaTest' => '20,5',
            'BrojBodovaSkola' => '33,5',
            'ukupniBrojBodova' => '54,0',
            'UpisniRok' => 'jun',
        ], [], [
            'documentUploadsPrva' => ['12' => $pdfA],
            'documentUploadsDruga' => ['15' => $pdfB],
        ]);

        $dto = KandidatPage2Data::fromRequest($request);

        $this->assertSame(42, $dto->kandidatId);
        $this->assertCount(4, $dto->grades);
        $this->assertCount(1, $dto->sports);
        $this->assertSame(7, $dto->sports[0]['sport']);
        $this->assertSame(181.5, $dto->visina);
        $this->assertArrayHasKey(12, $dto->documentUploadsPrva);
        $this->assertArrayHasKey(15, $dto->documentUploadsDruga);
    }

    #[Test]
    public function kandidat_update_data_from_request_maps_and_normalizes_all_fields(): void
    {
        $image = UploadedFile::fake()->image('avatar.jpg');
        $pdf = UploadedFile::fake()->create('file.pdf', 12, 'application/pdf');

        $request = Request::create('/', 'POST', [
            'ImeKandidata' => 'A',
            'PrezimeKandidata' => 'B',
            'JMBG' => '1',
            'uplata' => '0',
            'DatumRodjenja' => '10.02.2001.',
            'mestoRodjenja' => 'Nis',
            'KrsnaSlava' => '1',
            'KontaktTelefon' => '123',
            'AdresaStanovanja' => 'Adr',
            'Email' => 'a@b.com',
            'ImePrezimeJednogRoditelja' => 'Roditelj',
            'KontaktTelefonRoditelja' => '888',
            'NazivSkoleFakulteta' => 'Skola',
            'mestoZavrseneSkoleFakulteta' => 'Nis',
            'SmerZavrseneSkoleFakulteta' => 'Opsti',
            'TipStudija' => '2',
            'StudijskiProgram' => '3',
            'SkolskeGodineUpisa' => '4',
            'GodinaStudija' => '1',
            'drzavaZavrseneSkole' => 'SRB',
            'godinaZavrsetkaSkole' => '2020',
            'drzavaRodjenja' => 'SRB',
            'statusUpisa_id' => '1',
            'datumStatusa' => '03.03.2025.',
            'prviRazred' => '5',
            'drugiRazred' => '5',
            'treciRazred' => '5',
            'cetvrtiRazred' => '5',
            'SrednjaOcena1' => '5,00',
            'SrednjaOcena2' => '5,00',
            'SrednjaOcena3' => '5,00',
            'SrednjaOcena4' => '5,00',
            'OpstiUspehSrednjaSkola' => '5',
            'SrednjaOcenaSrednjaSkola' => '5,00',
            'VisinaKandidata' => '180',
            'TelesnaTezinaKandidata' => '80',
            'dokumentiPrva' => [1, 2],
            'dokumentiDruga' => [3],
            'BrojBodovaTest' => '20',
            'BrojBodovaSkola' => '30',
            'ukupniBrojBodova' => '50',
            'UpisniRok' => 'septembar',
            'IndikatorAktivan' => '1',
            'BrojIndeksa' => 'RA-22/2025',
        ], [], [
            'imageUpload' => $image,
            'pdfUpload' => $pdf,
            'documentUploadsPrva' => ['1' => UploadedFile::fake()->create('p1.pdf')],
            'documentUploadsDruga' => ['2' => UploadedFile::fake()->create('p2.pdf')],
        ]);

        $dto = KandidatUpdateData::fromRequest($request);

        $this->assertFalse($dto->uplata);
        $this->assertSame(2, $dto->tipStudijaId);
        $this->assertSame(4, $dto->skolskaGodinaUpisaId);
        $this->assertSame('RA-22/2025', $dto->brojIndeksa);
        $this->assertCount(4, $dto->grades);
        $this->assertArrayHasKey(1, $dto->documentUploadsPrva);
        $this->assertArrayHasKey(2, $dto->documentUploadsDruga);
    }

    #[Test]
    public function master_kandidat_data_from_request_maps_master_fields(): void
    {
        $image = UploadedFile::fake()->image('master.jpg');
        $request = Request::create('/', 'POST', [
            'ImeKandidata' => 'Master',
            'PrezimeKandidata' => 'Kandidat',
            'JMBG' => '123',
            'uplata' => '1',
            'statusUpisa_id' => '1',
            'datumStatusa' => '01.01.2026.',
            'mestoRodjenja' => 'BG',
            'KontaktTelefon' => '111',
            'AdresaStanovanja' => 'Adr',
            'Email' => 'master@test.local',
            'NazivSkoleFakulteta' => 'Fakultet',
            'mestoZavrseneSkoleFakulteta' => 'BG',
            'SmerZavrseneSkoleFakulteta' => 'Sport',
            'TipStudija' => '2',
            'StudijskiProgram' => '9',
            'SkolskeGodineUpisa' => '5',
            'ProsecnaOcena' => '9,30',
            'UpisniRok' => 'oktobar',
            'GodinaStudija' => '2',
            'brojIndeksa' => 'MS-1',
            'drzavaZavrseneSkole' => 'SRB',
            'godinaZavrsetkaSkole' => '2024',
            'drzavaRodjenja' => 'SRB',
            'dokumentaMaster' => [1, null, 3],
        ], [], [
            'imageUpload' => $image,
            'dokumentaMasterUpload' => ['5' => UploadedFile::fake()->create('master.pdf')],
        ]);

        $dto = MasterKandidatData::fromRequest($request);

        $this->assertTrue($dto->uplata);
        $this->assertSame(2, $dto->godinaStudijaId);
        $this->assertSame('MS-1', $dto->brojIndeksa);
        $this->assertCount(2, $dto->dokumentaMaster);
        $this->assertArrayHasKey(5, $dto->dokumentaMasterUpload);
    }

    #[Test]
    public function plan_prijava_and_zapisnik_dtos_map_and_serialize(): void
    {
        $planRequest = Request::create('/', 'POST', ['predmet' => '1', 'program' => '2', 'godina' => '3']);
        $plan = NastavniPlanData::fromRequest($planRequest);
        $this->assertSame(1, $plan->predmetId);

        $prijavaRequest = Request::create('/', 'POST', [
            'kandidat_id' => '9',
            'predmet_id' => '8',
            'profesor_id' => '7',
            'rok_id' => '6',
            'brojPolaganja' => '2',
            'datum' => '2026-05-01',
            'tipPrijave_id' => '3',
        ]);
        $prijava = PrijavaIspitaData::fromRequest($prijavaRequest);
        $this->assertSame(9, $prijava->toArray()['kandidat_id']);

        $zapisnikRequest = Request::create('/', 'POST', [
            'predmet_id' => '11',
            'profesor_id' => '12',
            'rok_id' => '13',
            'datum' => '2026-05-01',
            'datum2' => '2026-05-02',
            'vreme' => '10:00',
            'ucionica' => 'A1',
            'prijavaIspita_id' => '14',
            'odabir' => [1, 2, 3],
        ]);
        $zapisnik = ZapisnikData::fromRequest($zapisnikRequest);
        $this->assertSame(13, $zapisnik->toArray()['rok_id']);

        $stampaRequest = Request::create('/', 'POST', [
            'id' => '99',
            'predmet' => 'Anatomija',
            'rok' => 'Jun',
            'profesor' => 'Dr. X',
        ]);
        $stampa = ZapisnikStampaData::fromRequest($stampaRequest);
        $this->assertSame(99, $stampa->zapisnikId);
    }
}
