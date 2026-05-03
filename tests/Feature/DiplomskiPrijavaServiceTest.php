<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Services\DiplomskiPrijavaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DiplomskiPrijavaServiceTest extends TestCase
{
    use DatabaseTransactions;

    private DiplomskiPrijavaService $service;

    private Kandidat $student;

    private Profesor $profesor;

    private PredmetProgram $predmetProgram;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DiplomskiPrijavaService::class);

        TipStudija::factory()->create();
        StudijskiProgram::factory()->create();
        SkolskaGodUpisa::factory()->create();
        StatusGodine::factory()->create();

        $this->student = Kandidat::factory()->create();
        $this->profesor = Profesor::factory()->create();
        $this->predmetProgram = PredmetProgram::factory()->create([
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
        ]);
    }

    // =========================================================================
    // getDiplomskiTemaData
    // =========================================================================

    public function test_get_diplomski_tema_data_returns_required_keys(): void
    {
        $data = $this->service->getDiplomskiTemaData($this->student->id);

        $this->assertArrayHasKey('kandidat', $data);
        $this->assertArrayHasKey('profesor', $data);
        $this->assertArrayHasKey('predmeti', $data);
    }

    public function test_get_diplomski_tema_data_returns_correct_student(): void
    {
        $data = $this->service->getDiplomskiTemaData($this->student->id);

        $this->assertEquals($this->student->id, $data['kandidat']->id);
    }

    public function test_get_diplomski_tema_data_predmeti_scoped_to_student_program(): void
    {
        $otherPredmet = PredmetProgram::factory()->create([
            'tipStudija_id' => $this->student->tipStudija_id + 99,
        ]);

        $data = $this->service->getDiplomskiTemaData($this->student->id);

        $ids = $data['predmeti']->pluck('id');
        $this->assertTrue($ids->contains($this->predmetProgram->id));
        $this->assertFalse($ids->contains($otherPredmet->id));
    }

    // =========================================================================
    // storeDiplomskiTema / getEditDiplomskiTemaData / updateDiplomskiTema / deleteDiplomskiTema
    // =========================================================================

    public function test_store_diplomski_tema_persists_record(): void
    {
        $this->service->storeDiplomskiTema([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Test tema',
            'datum' => '2024-05-01',
            'profesor_id' => $this->profesor->id,
            'indikatorOdobreno' => false,
        ]);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'kandidat_id' => $this->student->id,
            'nazivTeme' => 'Test tema',
        ]);
    }

    public function test_store_diplomski_tema_returns_model_instance(): void
    {
        $tema = $this->service->storeDiplomskiTema([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Nova tema',
            'datum' => '2024-05-01',
            'profesor_id' => $this->profesor->id,
            'indikatorOdobreno' => false,
        ]);

        $this->assertInstanceOf(DiplomskiPrijavaTeme::class, $tema);
        $this->assertTrue($tema->exists);
    }

    public function test_get_edit_diplomski_tema_data_includes_existing_tema(): void
    {
        $tema = DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $data = $this->service->getEditDiplomskiTemaData($this->student->id);

        $this->assertNotNull($data['diplomskiRadTema']);
        $this->assertEquals($tema->id, $data['diplomskiRadTema']->id);
    }

    public function test_get_edit_diplomski_tema_data_returns_null_when_no_tema(): void
    {
        $data = $this->service->getEditDiplomskiTemaData($this->student->id);

        $this->assertNull($data['diplomskiRadTema']);
    }

    public function test_update_diplomski_tema_persists_changes(): void
    {
        $tema = DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'nazivTeme' => 'Stara tema',
        ]);

        $this->service->updateDiplomskiTema($tema->id, ['nazivTeme' => 'Nova tema'], false);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'id' => $tema->id,
            'nazivTeme' => 'Nova tema',
        ]);
    }

    public function test_update_diplomski_tema_sets_indikator_odobren(): void
    {
        $tema = DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'indikatorOdobreno' => 0,
        ]);

        $this->service->updateDiplomskiTema($tema->id, [], true);

        $this->assertDatabaseHas('diplomski_prijava_teme', [
            'id' => $tema->id,
            'indikatorOdobreno' => 1,
        ]);
    }

    public function test_delete_diplomski_tema_removes_record(): void
    {
        DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $this->service->deleteDiplomskiTema($this->student->id);

        $this->assertDatabaseMissing('diplomski_prijava_teme', [
            'kandidat_id' => $this->student->id,
        ]);
    }

    public function test_delete_diplomski_tema_returns_kandidat(): void
    {
        DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $result = $this->service->deleteDiplomskiTema($this->student->id);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals($this->student->id, $result->id);
    }

    // =========================================================================
    // getDiplomskiOdbranaData / storeDiplomskiOdbrana / updateDiplomskiOdbrana / deleteDiplomskiOdbrana
    // =========================================================================

    public function test_get_diplomski_odbrana_data_returns_required_keys(): void
    {
        $data = $this->service->getDiplomskiOdbranaData($this->student->id);

        $this->assertArrayHasKey('kandidat', $data);
        $this->assertArrayHasKey('profesor', $data);
        $this->assertArrayHasKey('predmeti', $data);
        $this->assertArrayHasKey('diplomskiRadTema', $data);
    }

    public function test_get_diplomski_odbrana_data_tema_is_null_when_missing(): void
    {
        $data = $this->service->getDiplomskiOdbranaData($this->student->id);

        $this->assertNull($data['diplomskiRadTema']);
    }

    public function test_get_diplomski_odbrana_data_tema_is_populated_when_exists(): void
    {
        $tema = DiplomskiPrijavaTeme::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $data = $this->service->getDiplomskiOdbranaData($this->student->id);

        $this->assertNotNull($data['diplomskiRadTema']);
        $this->assertEquals($tema->id, $data['diplomskiRadTema']->id);
    }

    public function test_store_diplomski_odbrana_persists_record(): void
    {
        $this->service->storeDiplomskiOdbrana([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Odbrana tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $this->assertDatabaseHas('diplomski_prijava_odbrane', [
            'kandidat_id' => $this->student->id,
        ]);
    }

    public function test_store_diplomski_odbrana_returns_model_instance(): void
    {
        $odbrana = $this->service->storeDiplomskiOdbrana([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Odbrana tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $this->assertInstanceOf(DiplomskiPrijavaOdbrane::class, $odbrana);
        $this->assertTrue($odbrana->exists);
    }

    public function test_delete_diplomski_odbrana_removes_record(): void
    {
        DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Del tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $this->service->deleteDiplomskiOdbrana($this->student->id);

        $this->assertDatabaseMissing('diplomski_prijava_odbrane', [
            'kandidat_id' => $this->student->id,
        ]);
    }

    // =========================================================================
    // getDiplomskiPolaganjeData / storeDiplomskiPolaganje / updateDiplomskiPolaganje / deleteDiplomskiPolaganje
    // =========================================================================

    public function test_get_diplomski_polaganje_data_returns_required_keys(): void
    {
        $data = $this->service->getDiplomskiPolaganjeData($this->student->id);

        $this->assertArrayHasKey('kandidat', $data);
        $this->assertArrayHasKey('profesor', $data);
        $this->assertArrayHasKey('predmeti', $data);
        $this->assertArrayHasKey('diplomskiRadTema', $data);
        $this->assertArrayHasKey('ispitniRok', $data);
    }

    public function test_store_diplomski_polaganje_persists_record(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();

        $this->service->storeDiplomskiPolaganje([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Polaganje tema',
            'datum' => '2024-06-01',
            'vreme' => '10:00',
            'profesor_id' => $this->profesor->id,
            'profesor_id_predsednik' => $this->profesor->id,
            'profesor_id_clan' => $this->profesor->id,
            'rok_id' => $rok->id,
            'brojBodova' => 80,
            'ocena' => 8,
        ]);

        $this->assertDatabaseHas('diplomski_polaganje', [
            'kandidat_id' => $this->student->id,
        ]);
    }

    public function test_store_diplomski_polaganje_returns_model_instance(): void
    {
        $rok = AktivniIspitniRokovi::factory()->create();

        $polaganje = $this->service->storeDiplomskiPolaganje([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Polaganje tema',
            'datum' => '2024-06-01',
            'vreme' => '10:00',
            'profesor_id' => $this->profesor->id,
            'profesor_id_predsednik' => $this->profesor->id,
            'profesor_id_clan' => $this->profesor->id,
            'rok_id' => $rok->id,
            'brojBodova' => 80,
            'ocena' => 8,
        ]);

        $this->assertInstanceOf(DiplomskiPolaganje::class, $polaganje);
        $this->assertTrue($polaganje->exists);
    }

    public function test_update_diplomski_polaganje_persists_changes(): void
    {
        $polaganje = DiplomskiPolaganje::factory()->create([
            'kandidat_id' => $this->student->id,
        ]);

        $this->service->updateDiplomskiPolaganje($polaganje->id, ['ocena' => 8]);

        $this->assertDatabaseHas('diplomski_polaganje', [
            'id' => $polaganje->id,
            'ocena' => 8,
        ]);
    }

    public function test_delete_diplomski_polaganje_removes_record(): void
    {
        DiplomskiPolaganje::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $this->service->deleteDiplomskiPolaganje($this->student->id);

        $this->assertDatabaseMissing('diplomski_polaganje', [
            'kandidat_id' => $this->student->id,
        ]);
    }

    public function test_delete_diplomski_polaganje_returns_kandidat(): void
    {
        DiplomskiPolaganje::factory()->create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
        ]);

        $result = $this->service->deleteDiplomskiPolaganje($this->student->id);

        $this->assertInstanceOf(Kandidat::class, $result);
        $this->assertEquals($this->student->id, $result->id);
    }

    // =========================================================================
    // getEditDiplomskiOdbranaData
    // =========================================================================

    public function test_get_edit_diplomski_odbrana_data_returns_required_keys(): void
    {
        $result = $this->service->getEditDiplomskiOdbranaData($this->student->id);

        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('profesor', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('diplomskiRadTema', $result);
        $this->assertArrayHasKey('diplomskiRadOdbrana', $result);
    }

    public function test_get_edit_diplomski_odbrana_data_odbrana_is_null_when_missing(): void
    {
        $result = $this->service->getEditDiplomskiOdbranaData($this->student->id);

        $this->assertNull($result['diplomskiRadOdbrana']);
    }

    public function test_get_edit_diplomski_odbrana_data_odbrana_populated_when_exists(): void
    {
        $odbrana = DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Test tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $result = $this->service->getEditDiplomskiOdbranaData($this->student->id);

        $this->assertNotNull($result['diplomskiRadOdbrana']);
        $this->assertEquals($odbrana->id, $result['diplomskiRadOdbrana']->id);
    }

    // =========================================================================
    // updateDiplomskiOdbrana
    // =========================================================================

    public function test_update_diplomski_odbrana_persists_changes(): void
    {
        $odbrana = DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Test tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $this->service->updateDiplomskiOdbrana($odbrana->id, ['nazivTeme' => 'Updated tema'], true);

        $this->assertDatabaseHas('diplomski_prijava_odbrane', [
            'id' => $odbrana->id,
            'nazivTeme' => 'Updated tema',
        ]);
    }

    public function test_update_diplomski_odbrana_sets_indikator_odobren(): void
    {
        $odbrana = DiplomskiPrijavaOdbrane::create([
            'kandidat_id' => $this->student->id,
            'tipStudija_id' => $this->student->tipStudija_id,
            'studijskiProgram_id' => $this->student->studijskiProgram_id,
            'predmet_id' => $this->predmetProgram->id,
            'nazivTeme' => 'Test tema',
            'datumPrijave' => '2024-05-01',
            'datumOdbrane' => '2024-06-01',
            'indikatorOdobreno' => false,
            'temu_odobrio_profesor_id' => $this->profesor->id,
            'odbranu_odobrio_profesor_id' => $this->profesor->id,
        ]);

        $result = $this->service->updateDiplomskiOdbrana($odbrana->id, [], true);

        $this->assertTrue((bool) $result->indikatorOdobreno);
    }

    // =========================================================================
    // getEditDiplomskiPolaganjeData
    // =========================================================================

    public function test_get_edit_diplomski_polaganje_data_returns_required_keys(): void
    {
        $result = $this->service->getEditDiplomskiPolaganjeData($this->student->id);

        $this->assertArrayHasKey('kandidat', $result);
        $this->assertArrayHasKey('profesor', $result);
        $this->assertArrayHasKey('predmeti', $result);
        $this->assertArrayHasKey('diplomskiRadTema', $result);
        $this->assertArrayHasKey('diplomskiRadPolaganje', $result);
        $this->assertArrayHasKey('ispitniRok', $result);
    }

    public function test_get_edit_diplomski_polaganje_data_polaganje_null_when_missing(): void
    {
        $result = $this->service->getEditDiplomskiPolaganjeData($this->student->id);

        $this->assertNull($result['diplomskiRadPolaganje']);
    }
}
