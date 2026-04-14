<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\KandidatData;
use App\DTOs\KandidatPage1Data;
use App\DTOs\KandidatPage2Data;
use App\DTOs\KandidatUpdateData;
use App\DTOs\MasterKandidatData;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\SkolskaGodUpisa;
use App\Models\Sport;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\DocumentManagementService;
use App\Services\FileStorageService;
use App\Services\GradeManagementService;
use App\Services\KandidatService;
use App\Services\SportsManagementService;
use App\Services\UpisService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KandidatServiceExtendedTest extends TestCase
{
    use DatabaseTransactions;

    private KandidatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        Model::unguard();
        $this->service = app(KandidatService::class);
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    private function createBasePrerequisites(int $tipStudijaId = 1): array
    {
        $tipStudija = TipStudija::find($tipStudijaId);
        if (! $tipStudija) {
            DB::table('tip_studija')->insert([
                'id' => $tipStudijaId,
                'naziv' => $tipStudijaId === 1 ? 'Osnovne studije' : 'Master studije',
                'skrNaziv' => $tipStudijaId === 1 ? 'OS' : 'MS',
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $tipStudija = TipStudija::find($tipStudijaId);
        }
        $statusStudiranja = $this->ensureStatusStudiranjaExists();
        $studijskiProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);
        $skolskaGodinaUpisa = SkolskaGodUpisa::factory()->create();
        $godinaStudija = GodinaStudija::factory()->create();

        return compact('tipStudija', 'statusStudiranja', 'studijskiProgram', 'skolskaGodinaUpisa', 'godinaStudija');
    }

    private function ensureStatusStudiranjaExists(): StatusStudiranja
    {
        $status = StatusStudiranja::find(3);
        if (! $status) {
            DB::table('status_studiranja')->insert([
                'id' => 3,
                'naziv' => 'Kandidat',
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $status = StatusStudiranja::find(3);
        }

        return $status;
    }

    private function makeKandidatPage1Data(array $overrides = []): KandidatPage1Data
    {
        $data = $this->createBasePrerequisites();

        return new KandidatPage1Data(
            ime: $overrides['ime'] ?? 'Marko',
            prezime: $overrides['prezime'] ?? 'Markovic',
            JMBG: $overrides['JMBG'] ?? '1234567890123',
            uplata: $overrides['uplata'] ?? false,
            datumRodjenja: $overrides['datumRodjenja'] ?? null,
            mestoRodjenja: $overrides['mestoRodjenja'] ?? 'Beograd',
            krsnaSlavaId: $overrides['krsnaSlavaId'] ?? 1,
            kontaktTelefon: $overrides['kontaktTelefon'] ?? '0641234567',
            adresaStanovanja: $overrides['adresaStanovanja'] ?? 'Test adresa 1',
            email: $overrides['email'] ?? 'test@test.com',
            imePrezimeJednogRoditelja: $overrides['imePrezimeJednogRoditelja'] ?? 'Otac Markovic',
            kontaktTelefonRoditelja: $overrides['kontaktTelefonRoditelja'] ?? '0641111111',
            srednjeSkoleFakulteti: $overrides['srednjeSkoleFakulteti'] ?? 'Gimnazija',
            mestoZavrseneSkoleFakulteta: $overrides['mestoZavrseneSkoleFakulteta'] ?? 'Beograd',
            smerZavrseneSkoleFakulteta: $overrides['smerZavrseneSkoleFakulteta'] ?? 'Opsti smer',
            studijskiProgramId: $overrides['studijskiProgramId'] ?? $data['studijskiProgram']->id,
            skolskaGodinaUpisaId: $overrides['skolskaGodinaUpisaId'] ?? $data['skolskaGodinaUpisa']->id,
            drzavaZavrseneSkole: $overrides['drzavaZavrseneSkole'] ?? 'Srbija',
            godinaZavrsetkaSkole: $overrides['godinaZavrsetkaSkole'] ?? '2020',
            drzavaRodjenja: $overrides['drzavaRodjenja'] ?? 'Srbija',
            godinaStudijaId: $overrides['godinaStudijaId'] ?? $data['godinaStudija']->id,
            imageUpload: $overrides['imageUpload'] ?? null,
        );
    }

    private function makeMasterKandidatData(array $overrides = []): MasterKandidatData
    {
        $data = $this->createBasePrerequisites(2);

        return new MasterKandidatData(
            ime: $overrides['ime'] ?? 'Ana',
            prezime: $overrides['prezime'] ?? 'Anic',
            JMBG: $overrides['JMBG'] ?? '9876543210987',
            uplata: $overrides['uplata'] ?? false,
            statusUpisaId: $overrides['statusUpisaId'] ?? null,
            datumStatusa: $overrides['datumStatusa'] ?? null,
            imageUpload: $overrides['imageUpload'] ?? null,
            mestoRodjenja: $overrides['mestoRodjenja'] ?? 'Novi Sad',
            kontaktTelefon: $overrides['kontaktTelefon'] ?? '0641234567',
            adresaStanovanja: $overrides['adresaStanovanja'] ?? 'Test adresa master 1',
            email: $overrides['email'] ?? 'master@test.com',
            srednjeSkoleFakulteti: $overrides['srednjeSkoleFakulteti'] ?? 'Fakultet',
            mestoZavrseneSkoleFakulteta: $overrides['mestoZavrseneSkoleFakulteta'] ?? 'Beograd',
            smerZavrseneSkoleFakulteta: $overrides['smerZavrseneSkoleFakulteta'] ?? 'Sportski menadžment',
            tipStudijaId: $overrides['tipStudijaId'] ?? $data['tipStudija']->id,
            studijskiProgramId: $overrides['studijskiProgramId'] ?? $data['studijskiProgram']->id,
            skolskaGodinaUpisaId: $overrides['skolskaGodinaUpisaId'] ?? $data['skolskaGodinaUpisa']->id,
            prosecnaOcena: $overrides['prosecnaOcena'] ?? 8.5,
            upisniRok: $overrides['upisniRok'] ?? 'juni',
            godinaStudijaId: $overrides['godinaStudijaId'] ?? $data['godinaStudija']->id,
            brojIndeksa: $overrides['brojIndeksa'] ?? null,
            drzavaZavrseneSkole: $overrides['drzavaZavrseneSkole'] ?? 'Srbija',
            godinaZavrsetkaSkole: $overrides['godinaZavrsetkaSkole'] ?? '2022',
            drzavaRodjenja: $overrides['drzavaRodjenja'] ?? 'Srbija',
            dokumentaMaster: $overrides['dokumentaMaster'] ?? [],
        );
    }

    public function test_find_by_id_or_fail_returns_kandidat_when_found(): void
    {
        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();

        $result = $this->service->findByIdOrFail($kandidat->id);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals($kandidat->id, $result->id);
    }

    public function test_find_by_id_or_fail_throws_model_not_found_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->findByIdOrFail(999999);
    }

    public function test_get_active_studijski_program_id_returns_active_program_id(): void
    {
        $tipStudija = TipStudija::factory()->create();
        $program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $result = $this->service->getActiveStudijskiProgramId($tipStudija->id);

        $this->assertNotNull($result);
        $this->assertEquals($program->id, $result);
    }

    public function test_get_active_studijski_program_id_uses_cache(): void
    {
        $tipStudija = TipStudija::factory()->create();
        StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $firstResult = $this->service->getActiveStudijskiProgramId($tipStudija->id);
        $secondResult = $this->service->getActiveStudijskiProgramId($tipStudija->id);

        $this->assertEquals($firstResult, $secondResult);
        $this->assertTrue(Cache::has("active_studijski_program_{$tipStudija->id}"));
    }

    public function test_get_active_studijski_program_id_returns_null_when_none_active(): void
    {
        $tipStudija = TipStudija::factory()->create();
        StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 0,
        ]);

        Cache::forget("active_studijski_program_{$tipStudija->id}");

        $result = $this->service->getActiveStudijskiProgramId($tipStudija->id);

        $this->assertNull($result);
    }

    public function test_get_aktivni_studijski_programi_returns_only_active_programs(): void
    {
        $tipStudija = TipStudija::factory()->create();
        StudijskiProgram::factory()->count(2)->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 1,
        ]);
        StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 0,
        ]);

        $result = $this->service->getAktivniStudijskiProgrami($tipStudija->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn ($p) => $p->indikatorAktivan === 1));
        $this->assertTrue($result->every(fn ($p) => $p->tipStudija_id === $tipStudija->id));
    }

    public function test_get_aktivni_studijski_programi_returns_empty_when_none_active(): void
    {
        $tipStudija = TipStudija::factory()->create();
        StudijskiProgram::factory()->create([
            'tipStudija_id' => $tipStudija->id,
            'indikatorAktivan' => 0,
        ]);

        $result = $this->service->getAktivniStudijskiProgrami($tipStudija->id);

        $this->assertCount(0, $result);
    }

    public function test_get_page_two_form_data_returns_merged_dropdown_and_sport_data(): void
    {
        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();
        Sport::factory()->count(3)->create();

        $result = $this->service->getPageTwoFormData($kandidat->id);

        $this->assertArrayHasKey('insertedId', $result);
        $this->assertEquals($kandidat->id, $result['insertedId']);

        $this->assertArrayHasKey('sport', $result);

        $this->assertArrayHasKey('dokumentiPrvaGodina', $result);
        $this->assertArrayHasKey('dokumentiOstaleGodine', $result);

        $this->assertArrayHasKey('mestoRodjenja', $result);
        $this->assertArrayHasKey('tipStudija', $result);
        $this->assertArrayHasKey('studijskiProgram', $result);
    }

    public function test_get_page_two_form_data_sport_contains_all_sports(): void
    {
        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();
        Sport::factory()->count(2)->create();

        $allSports = Sport::all();
        $result = $this->service->getPageTwoFormData($kandidat->id);

        $this->assertCount($allSports->count(), $result['sport']);
    }

    public function test_get_sport_page_data_returns_required_keys(): void
    {
        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();
        Sport::factory()->count(2)->create();

        $result = $this->service->getSportPageData($kandidat->id);

        $this->assertArrayHasKey('sport', $result);
        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('sportskoAngazovanje', $result);
        $this->assertArrayHasKey('id', $result);
    }

    public function test_get_sport_page_data_returns_correct_kandidat(): void
    {
        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();

        $result = $this->service->getSportPageData($kandidat->id);

        $this->assertInstanceOf(Kandidat::class, $result['kandidat']);
        $this->assertEquals($kandidat->id, $result['kandidat']->id);
        $this->assertEquals($kandidat->id, $result['id']);
    }

    public function test_get_sport_page_data_throws_exception_for_nonexistent_kandidat(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getSportPageData(999999);
    }

    public function test_store_kandidat_page1_creates_kandidat_without_image(): void
    {
        $data = $this->makeKandidatPage1Data();

        $result = $this->service->storeKandidatPage1($data);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertNotNull($result->id);
        $this->assertEquals('Marko', $result->imeKandidata);
        $this->assertEquals('Markovic', $result->prezimeKandidata);
        $this->assertEquals('1234567890123', $result->jmbg);
        $this->assertEquals(1, $result->tipStudija_id);
        $this->assertEquals(3, $result->statusUpisa_id);
    }

    public function test_store_kandidat_page1_persists_all_page1_fields(): void
    {
        $data = $this->createBasePrerequisites();

        $page1Data = new KandidatPage1Data(
            ime: 'Petar',
            prezime: 'Petrovic',
            JMBG: '1111111111111',
            uplata: true,
            datumRodjenja: null,
            mestoRodjenja: 'Nis',
            krsnaSlavaId: 1,
            kontaktTelefon: '0601234567',
            adresaStanovanja: 'Ulica Mira 5',
            email: 'petar@example.com',
            imePrezimeJednogRoditelja: 'Roditelj Petrovic',
            kontaktTelefonRoditelja: null,
            srednjeSkoleFakulteti: 'Tehnicka skola',
            mestoZavrseneSkoleFakulteta: 'Nis',
            smerZavrseneSkoleFakulteta: 'Elektrotehnika',
            studijskiProgramId: $data['studijskiProgram']->id,
            skolskaGodinaUpisaId: $data['skolskaGodinaUpisa']->id,
            drzavaZavrseneSkole: 'Srbija',
            godinaZavrsetkaSkole: '2019',
            drzavaRodjenja: 'Srbija',
            godinaStudijaId: $data['godinaStudija']->id,
            imageUpload: null,
        );

        $result = $this->service->storeKandidatPage1($page1Data);

        $this->assertDatabaseHas('kandidat', [
            'id' => $result->id,
            'imeKandidata' => 'Petar',
            'prezimeKandidata' => 'Petrovic',
            'jmbg' => '1111111111111',
            'mestoRodjenja' => 'Nis',
            'email' => 'petar@example.com',
        ]);
    }

    public function test_store_kandidat_page1_with_image_calls_file_storage_service(): void
    {
        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldReceive('uploadImageForKandidat')
            ->once()
            ->withArgs(function ($kandidat, $file) {
                return $kandidat instanceof Kandidat;
            });

        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->service = app(KandidatService::class);

        $fakeImage = UploadedFile::fake()->image('photo.jpg');

        $data = $this->makeKandidatPage1Data(['imageUpload' => $fakeImage, 'JMBG' => '2222222222222']);

        $result = $this->service->storeKandidatPage1($data);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_store_kandidat_page1_without_image_does_not_call_file_storage_service(): void
    {
        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldNotReceive('uploadImageForKandidat');

        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->service = app(KandidatService::class);

        $data = $this->makeKandidatPage1Data(['imageUpload' => null, 'JMBG' => '3333333333333']);

        $result = $this->service->storeKandidatPage1($data);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_store_kandidat_page2_saves_scores_and_returns_kandidat(): void
    {
        $mockGradeService = $this->mock(GradeManagementService::class);
        $mockGradeService->shouldReceive('createGradesForKandidat')->once();

        $mockSportsService = $this->mock(SportsManagementService::class);
        $mockSportsService->shouldReceive('createSportForKandidat')->never();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(GradeManagementService::class, $mockGradeService);
        $this->app->instance(SportsManagementService::class, $mockSportsService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();

        $page2Data = new KandidatPage2Data(
            kandidatId: $kandidat->id,
            grades: [
                ['razred' => 1, 'uspeh' => 1, 'ocena' => 4.5],
                ['razred' => 2, 'uspeh' => 1, 'ocena' => 4.0],
                ['razred' => 3, 'uspeh' => 1, 'ocena' => 4.5],
                ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.8],
            ],
            opstiUspehSrednjaSkolaId: 1,
            srednjaOcenaSrednjaSkola: 4.45,
            sports: [],
            visina: 180.0,
            telesnaTezina: 75.5,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: 50.0,
            brojBodovaSkola: 30.0,
            ukupniBrojBodova: 80.0,
            upisniRok: 'juni',
        );

        $result = $this->service->storeKandidatPage2($page2Data);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals($kandidat->id, $result->id);
        $this->assertEquals(80.0, $result->ukupniBrojBodova);
        $this->assertEquals(50.0, $result->brojBodovaTest);
        $this->assertEquals(180.0, $result->visina);
        $this->assertEquals(75.5, $result->telesnaTezina);
    }

    public function test_store_kandidat_page2_with_sports_calls_sports_service(): void
    {
        $mockGradeService = $this->mock(GradeManagementService::class);
        $mockGradeService->shouldReceive('createGradesForKandidat')->once();

        $mockSportsService = $this->mock(SportsManagementService::class);
        $mockSportsService->shouldReceive('createSportForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(GradeManagementService::class, $mockGradeService);
        $this->app->instance(SportsManagementService::class, $mockSportsService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create();

        $page2Data = new KandidatPage2Data(
            kandidatId: $kandidat->id,
            grades: [
                ['razred' => 1, 'uspeh' => 1, 'ocena' => 4.5],
                ['razred' => 2, 'uspeh' => 1, 'ocena' => 4.0],
                ['razred' => 3, 'uspeh' => 1, 'ocena' => 4.5],
                ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.8],
            ],
            opstiUspehSrednjaSkolaId: 1,
            srednjaOcenaSrednjaSkola: 4.45,
            sports: [
                ['sport' => 1, 'klub' => 'FK Test', 'uzrast' => 'Senior', 'godine' => '5'],
            ],
            visina: 185.0,
            telesnaTezina: 80.0,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: 45.0,
            brojBodovaSkola: 25.0,
            ukupniBrojBodova: 70.0,
            upisniRok: 'septembar',
        );

        $result = $this->service->storeKandidatPage2($page2Data);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_store_kandidat_page2_throws_exception_for_nonexistent_kandidat(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $page2Data = new KandidatPage2Data(
            kandidatId: 999999,
            grades: [],
            opstiUspehSrednjaSkolaId: null,
            srednjaOcenaSrednjaSkola: null,
            sports: [],
            visina: null,
            telesnaTezina: null,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: null,
            brojBodovaSkola: null,
            ukupniBrojBodova: null,
            upisniRok: null,
        );

        $this->service->storeKandidatPage2($page2Data);
    }

    public function test_update_kandidat_updates_fields_without_files(): void
    {
        $mockGradeService = $this->mock(GradeManagementService::class);
        $mockGradeService->shouldReceive('updateGradesForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(GradeManagementService::class, $mockGradeService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $prereq = $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $prereq['tipStudija']->id,
            'studijskiProgram_id' => $prereq['studijskiProgram']->id,
        ]);

        $statusStudiranja = $this->ensureStatusStudiranjaExists();

        $updateData = new KandidatUpdateData(
            ime: 'Updated Ime',
            prezime: 'Updated Prezime',
            JMBG: '9999999999999',
            uplata: true,
            imageUpload: null,
            pdfUpload: null,
            datumRodjenja: null,
            mestoRodjenja: 'Updated Mesto',
            krsnaSlavaId: 1,
            kontaktTelefon: '0699999999',
            adresaStanovanja: 'Updated Adresa 1',
            email: 'updated@example.com',
            imePrezimeJednogRoditelja: 'Updated Roditelj',
            kontaktTelefonRoditelja: null,
            srednjeSkoleFakulteti: 'Updated Skola',
            mestoZavrseneSkoleFakulteta: 'Updated Grad',
            smerZavrseneSkoleFakulteta: 'Updated Smer',
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: $prereq['skolskaGodinaUpisa']->id,
            godinaStudijaId: $prereq['godinaStudija']->id,
            drzavaZavrseneSkole: 'Srbija',
            godinaZavrsetkaSkole: '2021',
            drzavaRodjenja: 'Srbija',
            statusUpisaId: $statusStudiranja->id,
            datumStatusa: null,
            grades: [
                ['razred' => 1, 'uspeh' => 1, 'ocena' => 4.5],
                ['razred' => 2, 'uspeh' => 1, 'ocena' => 4.0],
                ['razred' => 3, 'uspeh' => 1, 'ocena' => 3.5],
                ['razred' => 4, 'uspeh' => 1, 'ocena' => 4.0],
            ],
            opstiUspehSrednjaSkolaId: 1,
            srednjaOcenaSrednjaSkola: 4.0,
            visina: 175.0,
            telesnaTezina: 70.0,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: 40.0,
            brojBodovaSkola: 20.0,
            ukupniBrojBodova: 60.0,
            upisniRok: 'juni',
            indikatorAktivan: 1,
            brojIndeksa: '1234/2021',
        );

        $result = $this->service->updateKandidat($kandidat->id, $updateData);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals('Updated Ime', $result->imeKandidata);
        $this->assertEquals('Updated Prezime', $result->prezimeKandidata);
        $this->assertEquals('9999999999999', $result->jmbg);
        $this->assertEquals('1234/2021', $result->brojIndeksa);
    }

    public function test_update_kandidat_with_image_calls_replace_image(): void
    {
        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldReceive('replaceImageForKandidat')->once();
        $mockFileStorage->shouldNotReceive('replacePdfForKandidat');

        $mockGradeService = $this->mock(GradeManagementService::class);
        $mockGradeService->shouldReceive('updateGradesForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->app->instance(GradeManagementService::class, $mockGradeService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $prereq = $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $prereq['tipStudija']->id,
            'studijskiProgram_id' => $prereq['studijskiProgram']->id,
        ]);
        $statusStudiranja = $this->ensureStatusStudiranjaExists();

        $fakeImage = UploadedFile::fake()->image('new_photo.jpg');

        $updateData = new KandidatUpdateData(
            ime: 'Img Updated',
            prezime: 'Prezime',
            JMBG: '8888888888888',
            uplata: false,
            imageUpload: $fakeImage,
            pdfUpload: null,
            datumRodjenja: null,
            mestoRodjenja: null,
            krsnaSlavaId: 1,
            kontaktTelefon: null,
            adresaStanovanja: null,
            email: null,
            imePrezimeJednogRoditelja: null,
            kontaktTelefonRoditelja: null,
            srednjeSkoleFakulteti: null,
            mestoZavrseneSkoleFakulteta: null,
            smerZavrseneSkoleFakulteta: null,
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: $prereq['skolskaGodinaUpisa']->id,
            godinaStudijaId: $prereq['godinaStudija']->id,
            drzavaZavrseneSkole: null,
            godinaZavrsetkaSkole: null,
            drzavaRodjenja: null,
            statusUpisaId: $statusStudiranja->id,
            datumStatusa: null,
            grades: [],
            opstiUspehSrednjaSkolaId: null,
            srednjaOcenaSrednjaSkola: null,
            visina: null,
            telesnaTezina: null,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: null,
            brojBodovaSkola: null,
            ukupniBrojBodova: null,
            upisniRok: null,
            indikatorAktivan: null,
            brojIndeksa: null,
        );

        $result = $this->service->updateKandidat($kandidat->id, $updateData);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_update_kandidat_with_pdf_calls_replace_pdf(): void
    {
        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldNotReceive('replaceImageForKandidat');
        $mockFileStorage->shouldReceive('replacePdfForKandidat')->once();

        $mockGradeService = $this->mock(GradeManagementService::class);
        $mockGradeService->shouldReceive('updateGradesForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->app->instance(GradeManagementService::class, $mockGradeService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $prereq = $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $prereq['tipStudija']->id,
            'studijskiProgram_id' => $prereq['studijskiProgram']->id,
        ]);
        $statusStudiranja = $this->ensureStatusStudiranjaExists();

        $fakePdf = UploadedFile::fake()->create('diploma.pdf', 100, 'application/pdf');

        $updateData = new KandidatUpdateData(
            ime: 'Pdf Updated',
            prezime: 'Prezime',
            JMBG: '7777777777777',
            uplata: false,
            imageUpload: null,
            pdfUpload: $fakePdf,
            datumRodjenja: null,
            mestoRodjenja: null,
            krsnaSlavaId: 1,
            kontaktTelefon: null,
            adresaStanovanja: null,
            email: null,
            imePrezimeJednogRoditelja: null,
            kontaktTelefonRoditelja: null,
            srednjeSkoleFakulteti: null,
            mestoZavrseneSkoleFakulteta: null,
            smerZavrseneSkoleFakulteta: null,
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: $prereq['skolskaGodinaUpisa']->id,
            godinaStudijaId: $prereq['godinaStudija']->id,
            drzavaZavrseneSkole: null,
            godinaZavrsetkaSkole: null,
            drzavaRodjenja: null,
            statusUpisaId: $statusStudiranja->id,
            datumStatusa: null,
            grades: [],
            opstiUspehSrednjaSkolaId: null,
            srednjaOcenaSrednjaSkola: null,
            visina: null,
            telesnaTezina: null,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: null,
            brojBodovaSkola: null,
            ukupniBrojBodova: null,
            upisniRok: null,
            indikatorAktivan: null,
            brojIndeksa: null,
        );

        $result = $this->service->updateKandidat($kandidat->id, $updateData);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_update_kandidat_throws_exception_for_nonexistent_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $prereq = $this->createBasePrerequisites();

        $updateData = new KandidatUpdateData(
            ime: 'Ghost',
            prezime: 'User',
            JMBG: '0000000000000',
            uplata: false,
            imageUpload: null,
            pdfUpload: null,
            datumRodjenja: null,
            mestoRodjenja: null,
            krsnaSlavaId: 1,
            kontaktTelefon: null,
            adresaStanovanja: null,
            email: null,
            imePrezimeJednogRoditelja: null,
            kontaktTelefonRoditelja: null,
            srednjeSkoleFakulteti: null,
            mestoZavrseneSkoleFakulteta: null,
            smerZavrseneSkoleFakulteta: null,
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: null,
            godinaStudijaId: null,
            drzavaZavrseneSkole: null,
            godinaZavrsetkaSkole: null,
            drzavaRodjenja: null,
            statusUpisaId: null,
            datumStatusa: null,
            grades: [],
            opstiUspehSrednjaSkolaId: null,
            srednjaOcenaSrednjaSkola: null,
            visina: null,
            telesnaTezina: null,
            dokumentiPrva: [],
            dokumentiDruga: [],
            brojBodovaTest: null,
            brojBodovaSkola: null,
            ukupniBrojBodova: null,
            upisniRok: null,
            indikatorAktivan: null,
            brojIndeksa: null,
        );

        $this->service->updateKandidat(999999, $updateData);
    }

    public function test_store_master_kandidat_creates_kandidat_and_calls_upis_service(): void
    {
        $mockUpisService = $this->mock(UpisService::class);
        $mockUpisService->shouldReceive('registrujKandidata')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(UpisService::class, $mockUpisService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $data = $this->makeMasterKandidatData();

        $result = $this->service->storeMasterKandidat($data);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertNotNull($result->id);
        $this->assertEquals('Ana', $result->imeKandidata);
        $this->assertEquals('Anic', $result->prezimeKandidata);
        $this->assertEquals(2, $result->tipStudija_id);
        $this->assertEquals(3, $result->statusUpisa_id);
    }

    public function test_store_master_kandidat_with_image_calls_upload_image(): void
    {
        $mockUpisService = $this->mock(UpisService::class);
        $mockUpisService->shouldReceive('registrujKandidata')->once();

        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldReceive('uploadImageForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(UpisService::class, $mockUpisService);
        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $fakeImage = UploadedFile::fake()->image('master_photo.jpg');
        $data = $this->makeMasterKandidatData(['imageUpload' => $fakeImage, 'JMBG' => '5555555555555']);

        $result = $this->service->storeMasterKandidat($data);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_store_master_kandidat_sets_uplata_correctly(): void
    {
        $mockUpisService = $this->mock(UpisService::class);
        $mockUpisService->shouldReceive('registrujKandidata')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(UpisService::class, $mockUpisService);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $data = $this->makeMasterKandidatData(['uplata' => true, 'JMBG' => '6666666666666']);

        $result = $this->service->storeMasterKandidat($data);

        $this->assertTrue((bool) $result->uplata);
    }

    public function test_update_master_kandidat_updates_fields(): void
    {
        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $prereq = $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $prereq['tipStudija']->id,
            'studijskiProgram_id' => $prereq['studijskiProgram']->id,
        ]);
        $statusStudiranja = $this->ensureStatusStudiranjaExists();

        $updateData = new MasterKandidatData(
            ime: 'Updated Master',
            prezime: 'Updated Prezime',
            JMBG: '4444444444444',
            uplata: true,
            statusUpisaId: $statusStudiranja->id,
            datumStatusa: null,
            imageUpload: null,
            mestoRodjenja: 'Kragujevac',
            kontaktTelefon: '0690000000',
            adresaStanovanja: 'Updated Adresa Master',
            email: 'updatedmaster@example.com',
            srednjeSkoleFakulteti: 'Updated Fakultet',
            mestoZavrseneSkoleFakulteta: 'Kragujevac',
            smerZavrseneSkoleFakulteta: 'Updated Smer',
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: $prereq['skolskaGodinaUpisa']->id,
            prosecnaOcena: 9.0,
            upisniRok: 'oktobar',
            godinaStudijaId: $prereq['godinaStudija']->id,
            brojIndeksa: 'M001/2023',
            drzavaZavrseneSkole: 'Srbija',
            godinaZavrsetkaSkole: '2023',
            drzavaRodjenja: 'Srbija',
            dokumentaMaster: [],
        );

        $result = $this->service->updateMasterKandidat($kandidat->id, $updateData);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals('Updated Master', $result->imeKandidata);
        $this->assertEquals('Updated Prezime', $result->prezimeKandidata);
        $this->assertEquals('4444444444444', $result->jmbg);
        $this->assertEquals('M001/2023', $result->brojIndeksa);
    }

    public function test_update_master_kandidat_with_image_calls_replace_image(): void
    {
        $mockFileStorage = $this->mock(FileStorageService::class);
        $mockFileStorage->shouldReceive('replaceImageForKandidat')->once();

        $mockDocService = $this->mock(DocumentManagementService::class);
        $mockDocService->shouldReceive('deleteDocumentsForKandidat')->once();
        $mockDocService->shouldReceive('attachDocumentsForKandidat')->once();

        $this->app->instance(FileStorageService::class, $mockFileStorage);
        $this->app->instance(DocumentManagementService::class, $mockDocService);
        $this->service = app(KandidatService::class);

        $prereq = $this->createBasePrerequisites();
        $kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $prereq['tipStudija']->id,
            'studijskiProgram_id' => $prereq['studijskiProgram']->id,
        ]);
        $statusStudiranja = $this->ensureStatusStudiranjaExists();

        $fakeImage = UploadedFile::fake()->image('new_master.jpg');

        $updateData = new MasterKandidatData(
            ime: 'Img Master',
            prezime: 'Img Prezime',
            JMBG: '3344556677889',
            uplata: false,
            statusUpisaId: $statusStudiranja->id,
            datumStatusa: null,
            imageUpload: $fakeImage,
            mestoRodjenja: null,
            kontaktTelefon: null,
            adresaStanovanja: null,
            email: null,
            srednjeSkoleFakulteti: null,
            mestoZavrseneSkoleFakulteta: null,
            smerZavrseneSkoleFakulteta: null,
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: $prereq['skolskaGodinaUpisa']->id,
            prosecnaOcena: null,
            upisniRok: null,
            godinaStudijaId: $prereq['godinaStudija']->id,
            brojIndeksa: null,
            drzavaZavrseneSkole: null,
            godinaZavrsetkaSkole: null,
            drzavaRodjenja: null,
            dokumentaMaster: [],
        );

        $result = $this->service->updateMasterKandidat($kandidat->id, $updateData);

        $this->assertInstanceOf(Kandidat::class, $result);
    }

    public function test_update_master_kandidat_throws_exception_for_nonexistent_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $prereq = $this->createBasePrerequisites();

        $updateData = new MasterKandidatData(
            ime: 'Ghost',
            prezime: 'User',
            JMBG: '1122334455667',
            uplata: false,
            statusUpisaId: null,
            datumStatusa: null,
            imageUpload: null,
            mestoRodjenja: null,
            kontaktTelefon: null,
            adresaStanovanja: null,
            email: null,
            srednjeSkoleFakulteti: null,
            mestoZavrseneSkoleFakulteta: null,
            smerZavrseneSkoleFakulteta: null,
            tipStudijaId: $prereq['tipStudija']->id,
            studijskiProgramId: $prereq['studijskiProgram']->id,
            skolskaGodinaUpisaId: null,
            prosecnaOcena: null,
            upisniRok: null,
            godinaStudijaId: $prereq['godinaStudija']->id,
            brojIndeksa: null,
            drzavaZavrseneSkole: null,
            godinaZavrsetkaSkole: null,
            drzavaRodjenja: null,
            dokumentaMaster: [],
        );

        $this->service->updateMasterKandidat(999999, $updateData);
    }

    public function test_store_kandidat_creates_kandidat_from_dto(): void
    {
        $prereq = $this->createBasePrerequisites();

        $data = new KandidatData(
            ime: 'DTO Kandida',
            prezime: 'DTO Prezime',
            JMBG: '1020304050607',
            studijskiProgramId: $prereq['studijskiProgram']->id,
            tipStudijaId: $prereq['tipStudija']->id,
            brojIndeksa: 'DTO001/2024',
            godinaStudijaId: $prereq['godinaStudija']->id,
        );

        $result = $this->service->storeKandidat($data);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertNotNull($result->id);
        $this->assertEquals('DTO Kandida', $result->imeKandidata);
        $this->assertEquals('DTO Prezime', $result->prezimeKandidata);
        $this->assertEquals('1020304050607', $result->jmbg);
        $this->assertEquals('DTO001/2024', $result->brojIndeksa);
    }

    public function test_store_kandidat_persists_to_database(): void
    {
        $prereq = $this->createBasePrerequisites();

        $data = new KandidatData(
            ime: 'DB Kandida',
            prezime: 'DB Prezime',
            JMBG: '9988776655443',
            studijskiProgramId: $prereq['studijskiProgram']->id,
            tipStudijaId: $prereq['tipStudija']->id,
        );

        $result = $this->service->storeKandidat($data);

        $this->assertDatabaseHas('kandidat', [
            'id' => $result->id,
            'imeKandidata' => 'DB Kandida',
            'prezimeKandidata' => 'DB Prezime',
            'jmbg' => '9988776655443',
        ]);
    }

    public function test_store_kandidat_without_optional_fields(): void
    {
        $prereq = $this->createBasePrerequisites();

        $data = new KandidatData(
            ime: 'Minimal',
            prezime: 'Kandidat',
            JMBG: '1122334455668',
            studijskiProgramId: $prereq['studijskiProgram']->id,
            tipStudijaId: $prereq['tipStudija']->id,
        );

        $result = $this->service->storeKandidat($data);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertNull($result->brojIndeksa);
        $this->assertNull($result->godinaStudija_id);
    }
}
