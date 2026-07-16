<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\KandidatPrilozenaDokumenta;
use App\Models\KrsnaSlava;
use App\Models\Opstina;
use App\Models\PrilozenaDokumenta;
use App\Models\Region;
use App\Models\SkolskaGodUpisa;
use App\Models\Sport;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UpisGodine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class KandidatControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $osnovneStudije;

    protected TipStudija $masterStudije;

    protected StudijskiProgram $osnovniProgram;

    protected StudijskiProgram $masterProgram;

    protected SkolskaGodUpisa $skolskaGodina;

    protected GodinaStudija $godinaStudija;

    protected StatusStudiranja $kandidatStatus;

    protected StatusStudiranja $upisanStatus;

    protected Kandidat $kandidat;

    protected Kandidat $masterKandidat;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        Gate::before(function ($user) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            if ($user && isset($user->role) && $user->role === 'admin') {
                return true;
            }
        });

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'kandidat_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $region = Region::query()->first() ?? Region::forceCreate([
            'naziv' => 'Srbija',
        ]);

        Opstina::query()->first() ?? Opstina::forceCreate([
            'naziv' => 'Beograd',
            'region_id' => $region->id,
        ]);

        KrsnaSlava::query()->first() ?? KrsnaSlava::forceCreate([
            'naziv' => 'Sveti Nikola',
            'datumSlave' => '19.12.',
            'indikatorAktivan' => 1,
        ]);

        PrilozenaDokumenta::query()->first() ?? PrilozenaDokumenta::forceCreate([
            'naziv' => 'Svedocanstvo',
            'skolskaGodina_id' => 1,
            'redniBrojDokumenta' => 1,
        ]);

        $this->godinaStudija = GodinaStudija::forceCreate([
            'id' => 1,
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->osnovneStudije = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->masterStudije = TipStudija::factory()->create([
            'id' => 2,
            'naziv' => 'Master akademske studije',
            'skrNaziv' => 'MAS',
            'indikatorAktivan' => 1,
        ]);

        $this->osnovniProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'indikatorAktivan' => 1,
        ]);

        $this->masterProgram = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->masterStudije->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->kandidatStatus = StatusStudiranja::factory()->create([
            'id' => 3,
            'naziv' => 'kandidat',
            'indikatorAktivan' => 1,
        ]);

        $this->upisanStatus = StatusStudiranja::factory()->create([
            'id' => 1,
            'naziv' => 'upisan',
            'indikatorAktivan' => 1,
        ]);

        $this->kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->kandidatStatus->id,
            'indikatorAktivan' => 1,
        ]);

        $this->attachApprovedRequiredDocument($this->kandidat);

        $this->masterKandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->masterStudije->id,
            'studijskiProgram_id' => $this->masterProgram->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->kandidatStatus->id,
            'indikatorAktivan' => 1,
        ]);

        Sport::query()->first() ?? Sport::forceCreate([
            'naziv' => 'Kosarka',
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($this->user);
    }

    private function attachApprovedRequiredDocument(Kandidat $kandidat): void
    {
        $dokument = PrilozenaDokumenta::query()
            ->where('skolskaGodina_id', '1')
            ->orderBy('redniBrojDokumenta')
            ->firstOrFail();

        KandidatPrilozenaDokumenta::create([
            'kandidat_id' => $kandidat->id,
            'prilozenaDokumenta_id' => $dokument->id,
            'indikatorAktivan' => 1,
            'review_status' => KandidatPrilozenaDokumenta::STATUS_APPROVED,
            'reviewer_id' => $this->user->id,
        ]);
    }

    public function test_index_returns_filtered_osnovne_candidates_view(): void
    {
        $response = $this->get('/kandidat?studijskiProgramId='.$this->osnovniProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.indeks');
        $response->assertViewHas('kandidati', function ($kandidati) {
            return $kandidati->contains('id', $this->kandidat->id);
        });
        $response->assertViewHas('studijskiProgrami');
    }

    public function test_create_returns_first_step_view(): void
    {
        $response = $this->get('/kandidat/create');

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.create_part_1');
        $response->assertViewHasAll([
            'mestoRodjenja',
            'krsnaSlava',
            'studijskiProgram',
            'tipStudija',
            'godinaStudija',
            'skolskeGodineUpisa',
        ]);
    }

    public function test_show_returns_details_view(): void
    {
        $response = $this->get('/kandidat/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.details');
        $response->assertViewHas('kandidat');
    }

    public function test_edit_returns_update_view(): void
    {
        $response = $this->get('/kandidat/'.$this->kandidat->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.update');
        $response->assertViewHas('kandidat');
    }

    public function test_sport_returns_sports_view(): void
    {
        $response = $this->get('/kandidat/'.$this->kandidat->id.'/sportskoangazovanje');

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.sportsko_angazovanje');
        $response->assertViewHasAll(['sport', 'kandidat', 'sportskoAngazovanje', 'id']);
    }

    public function test_sport_store_creates_record_and_redirects(): void
    {
        $sport = Sport::query()->firstOrFail();

        $response = $this->post('/kandidat/'.$this->kandidat->id.'/sportskoangazovanje', [
            'sport' => $sport->id,
            'klub' => 'KK Test',
            'uzrast' => '2015-2020',
            'godine' => 5,
        ]);

        $response->assertRedirect('/kandidat/'.$this->kandidat->id.'/sportskoangazovanje');
        $this->assertDatabaseHas('sportsko_angazovanje', [
            'kandidat_id' => $this->kandidat->id,
            'sport_id' => $sport->id,
            'nazivKluba' => 'KK Test',
        ]);
    }

    public function test_index_master_returns_master_candidates_view(): void
    {
        $response = $this->get('/master?studijskiProgramId='.$this->masterProgram->id);

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.index_master');
        $response->assertViewHas('kandidati', function ($kandidati) {
            return $kandidati->contains('id', $this->masterKandidat->id);
        });
    }

    public function test_create_master_returns_master_create_view(): void
    {
        $response = $this->get('/master/create');

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.create_master');
        $response->assertViewHasAll(['studijskiProgram', 'dokumentaMaster', 'tipStudija']);
    }

    public function test_edit_master_returns_update_master_view(): void
    {
        $response = $this->get('/master/'.$this->masterKandidat->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.update_master');
        $response->assertViewHas('kandidat');
    }

    public function test_upis_kandidata_redirects_to_kandidat_index_on_success(): void
    {
        UpisGodine::create([
            'kandidat_id' => $this->kandidat->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $response = $this->get('/kandidat/'.$this->kandidat->id.'/upis');

        $response->assertRedirect('/kandidat/');
        $response->assertSessionHas('flash-success', 'upis');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->kandidat->id,
            'statusUpisa_id' => 1,
        ]);
    }

    public function test_masovna_uplata_redirects_and_marks_selected_candidates_paid(): void
    {
        $response = $this->post('/kandidat/masovnaUplata', [
            'odabir' => [$this->kandidat->id],
        ]);

        $response->assertRedirect('/kandidat/');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->kandidat->id,
            'uplata' => 1,
        ]);
    }

    public function test_masovni_upis_redirects_to_index_on_success(): void
    {
        UpisGodine::create([
            'kandidat_id' => $this->kandidat->id,
            'godina' => 1,
            'pokusaj' => 1,
            'tipStudija_id' => $this->osnovneStudije->id,
            'studijskiProgram_id' => $this->osnovniProgram->id,
            'statusGodine_id' => 3,
            'skolskaGodina_id' => null,
            'datumUpisa' => null,
        ]);

        $response = $this->post('/kandidat/masovniUpis', [
            'odabir' => [$this->kandidat->id],
        ]);

        $response->assertRedirect('/kandidat/');
        $response->assertSessionMissing('flash-error');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->kandidat->id,
            'statusUpisa_id' => 1,
        ]);
    }

    public function test_masovna_uplata_master_redirects_and_marks_master_candidates_paid(): void
    {
        $response = $this->post('/master/masovnaUplata', [
            'odabir' => [$this->masterKandidat->id],
        ]);

        $response->assertRedirect('/master/');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->masterKandidat->id,
            'uplata' => 1,
        ]);
    }

    public function test_masovni_upis_master_redirects_and_updates_status(): void
    {
        $response = $this->post('/master/masovniUpis', [
            'odabir' => [$this->masterKandidat->id],
        ]);

        $response->assertRedirect('/master/');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->masterKandidat->id,
            'statusUpisa_id' => 1,
        ]);
    }

    public function test_registracija_kandidata_redirects_to_index(): void
    {
        $response = $this->get('/regk/'.$this->kandidat->id);

        $response->assertRedirect('/kandidat/');
    }

    public function test_store_page_1_creates_kandidat_and_returns_page_2_view(): void
    {
        $opstina = Opstina::query()->first();
        $krsnaSlava = KrsnaSlava::query()->first();

        $response = $this->post('/kandidat', [
            'page' => 1,
            'ImeKandidata' => 'Марко',
            'PrezimeKandidata' => 'Петровић',
            'JMBG' => '0101999123456',
            'DatumRodjenja' => '1999-01-01',
            'mestoRodjenja' => $opstina->id,
            'KrsnaSlava' => $krsnaSlava->id,
            'StudijskiProgram' => $this->osnovniProgram->id,
            'GodinaStudija' => $this->godinaStudija->id,
            'SkolskeGodineUpisa' => $this->skolskaGodina->id,
            'BrojBodovaTest' => 0,
            'BrojBodovaSkola' => 0,
            'ukupniBrojBodova' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('kandidat.create_part_2');
        $this->assertDatabaseHas('kandidat', [
            'imeKandidata' => 'Марко',
            'prezimeKandidata' => 'Петровић',
            'jmbg' => '0101999123456',
        ]);
    }

    public function test_store_page_2_completes_kandidat_creation_and_redirects(): void
    {
        $response = $this->post('/kandidat', [
            'page' => 2,
            'insertedId' => $this->kandidat->id,
            'OpstiUspehSrednjaSkola' => 1,
            'SrednjaOcenaSrednjaSkola' => 4.5,
            'prviRazred' => 1, 'SrednjaOcena1' => 4.5,
            'drugiRazred' => 1, 'SrednjaOcena2' => 4.5,
            'treciRazred' => 1, 'SrednjaOcena3' => 4.5,
            'cetvrtiRazred' => 1, 'SrednjaOcena4' => 4.5,
        ]);

        $response->assertRedirect('/kandidat');
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->kandidat->id,
            'srednjaOcenaSrednjaSkola' => 4.5,
        ]);
    }

    public function test_update_updates_kandidat_and_redirects_to_index(): void
    {
        $response = $this->put('/kandidat/'.$this->kandidat->id, [
            'ImeKandidata' => 'Никола Updated',
            'PrezimeKandidata' => 'Јовановић Updated',
            'JMBG' => $this->kandidat->jmbg,
            'DatumRodjenja' => $this->kandidat->datumRodjenja,
            'mestoRodjenja' => $this->kandidat->mesto_id,
            'KrsnaSlava' => $this->kandidat->krsnaSlava_id,
            'TipStudija' => $this->kandidat->tipStudija_id,
            'StudijskiProgram' => $this->kandidat->studijskiProgram_id,
            'GodinaStudija' => $this->kandidat->godinaStudija_id,
            'SkolskeGodineUpisa' => $this->kandidat->skolskaGodinaUpisa_id,
            'statusUpisa_id' => $this->kandidat->statusUpisa_id,
            'prviRazred' => 1, 'SrednjaOcena1' => 4.5,
            'drugiRazred' => 1, 'SrednjaOcena2' => 4.5,
            'treciRazred' => 1, 'SrednjaOcena3' => 4.5,
            'cetvrtiRazred' => 1, 'SrednjaOcena4' => 4.5,
            'OpstiUspehSrednjaSkola' => 1,
            'SrednjaOcenaSrednjaSkola' => 4.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->kandidat->id,
            'imeKandidata' => 'Никола Updated',
            'prezimeKandidata' => 'Јовановић Updated',
        ]);
    }

    public function test_update_with_submitstay_redirects_back_to_edit(): void
    {
        $response = $this->put('/kandidat/'.$this->kandidat->id, [
            'submitstay' => '1',
            'ImeKandidata' => 'Стефан',
            'PrezimeKandidata' => 'Николић',
            'JMBG' => $this->kandidat->jmbg,
            'DatumRodjenja' => $this->kandidat->datumRodjenja,
            'mestoRodjenja' => $this->kandidat->mesto_id,
            'KrsnaSlava' => $this->kandidat->krsnaSlava_id,
            'TipStudija' => $this->kandidat->tipStudija_id,
            'StudijskiProgram' => $this->kandidat->studijskiProgram_id,
            'GodinaStudija' => $this->kandidat->godinaStudija_id,
            'SkolskeGodineUpisa' => $this->kandidat->skolskaGodinaUpisa_id,
            'statusUpisa_id' => $this->kandidat->statusUpisa_id,
            'prviRazred' => 1, 'SrednjaOcena1' => 4.5,
            'drugiRazred' => 1, 'SrednjaOcena2' => 4.5,
            'treciRazred' => 1, 'SrednjaOcena3' => 4.5,
            'cetvrtiRazred' => 1, 'SrednjaOcena4' => 4.5,
            'OpstiUspehSrednjaSkola' => 1,
            'SrednjaOcenaSrednjaSkola' => 4.5,
        ]);

        $response->assertRedirect('/kandidat/'.$this->kandidat->id.'/edit');
    }

    public function test_destroy_deletes_kandidat_and_flashes_success(): void
    {
        $kandidatId = $this->kandidat->id;

        $response = $this->from('/kandidat')
            ->delete('/kandidat/'.$kandidatId);

        $response->assertRedirect('/kandidat');
        $response->assertSessionHas('flash-success', 'delete');
        $this->assertDatabaseMissing('kandidat', [
            'id' => $kandidatId,
        ]);
    }

    public function test_store_master_creates_master_kandidat_and_redirects(): void
    {
        $opstina = Opstina::query()->first();
        $krsnaSlava = KrsnaSlava::query()->first();

        $response = $this->post('/storeMaster/', [
            'ImeKandidata' => 'Анна',
            'PrezimeKandidata' => 'Поповић',
            'JMBG' => '0202995654321',
            'DatumRodjenja' => '1995-02-02',
            'mestoRodjenja' => $opstina->id,
            'KrsnaSlava' => $krsnaSlava->id,
            'TipStudija' => $this->masterStudije->id,
            'StudijskiProgram' => $this->masterProgram->id,
            'GodinaStudija' => $this->godinaStudija->id,
            'SkolskeGodineUpisa' => $this->skolskaGodina->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kandidat', [
            'imeKandidata' => 'Анна',
            'prezimeKandidata' => 'Поповић',
            'jmbg' => '0202995654321',
            'tipStudija_id' => $this->masterStudije->id,
        ]);
    }

    public function test_update_master_updates_master_kandidat_and_redirects(): void
    {
        $response = $this->post('/master/'.$this->masterKandidat->id.'/edit', [
            'ImeKandidata' => 'Master Updated',
            'PrezimeKandidata' => 'Name Updated',
            'JMBG' => $this->masterKandidat->jmbg,
            'DatumRodjenja' => $this->masterKandidat->datumRodjenja,
            'mestoRodjenja' => $this->masterKandidat->mesto_id,
            'KrsnaSlava' => $this->masterKandidat->krsnaSlava_id,
            'TipStudija' => $this->masterKandidat->tipStudija_id,
            'StudijskiProgram' => $this->masterKandidat->studijskiProgram_id,
            'GodinaStudija' => $this->masterKandidat->godinaStudija_id,
            'SkolskeGodineUpisa' => $this->masterKandidat->skolskaGodinaUpisa_id,
            'statusUpisa_id' => $this->masterKandidat->statusUpisa_id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kandidat', [
            'id' => $this->masterKandidat->id,
            'imeKandidata' => 'Master Updated',
        ]);
    }

    public function test_destroy_master_deletes_master_kandidat_and_flashes_success(): void
    {
        $masterKandidatId = $this->masterKandidat->id;

        $response = $this->from('/master')
            ->get('/master/'.$masterKandidatId.'/delete');

        $response->assertRedirect('/master');
        $response->assertSessionHas('flash-success', 'delete');
        $this->assertDatabaseMissing('kandidat', [
            'id' => $masterKandidatId,
        ]);
    }

    public function test_test_post_returns_request_data(): void
    {
        $response = $this->post('/testPost', [
            'test_key' => 'test_value',
            'another_key' => 'another_value',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'test_key' => 'test_value',
            'another_key' => 'another_value',
        ]);
    }
}
