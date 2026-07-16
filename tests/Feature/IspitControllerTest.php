<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusIspita;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class IspitControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $program;

    protected SkolskaGodUpisa $skolskaGodina;

    protected GodinaStudija $godinaStudija;

    protected StatusStudiranja $statusStudiranja;

    protected Kandidat $kandidat;

    protected Predmet $predmet;

    protected PredmetProgram $predmetProgram;

    protected Profesor $profesor;

    protected AktivniIspitniRokovi $rok;

    protected ZapisnikOPolaganjuIspita $zapisnik;

    protected TipPredmeta $tipPredmeta;

    protected TipPrijave $tipPrijave;

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
            'email' => 'ispit_admin_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::forceCreate([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $this->tipPrijave = TipPrijave::query()->first() ?? TipPrijave::forceCreate([
            'naziv' => 'Redovna',
            'indikatorAktivan' => 1,
        ]);

        $this->godinaStudija = GodinaStudija::create([
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->tipStudija = TipStudija::factory()->create([
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->statusStudiranja = StatusStudiranja::factory()->create([
            'naziv' => 'upisan',
            'indikatorAktivan' => 1,
        ]);

        $this->kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusStudiranja->id,
            'indikatorAktivan' => 1,
        ]);

        $this->predmet = Predmet::factory()->create();
        $this->profesor = Profesor::factory()->create();
        $this->rok = AktivniIspitniRokovi::factory()->create([
            'indikatorAktivan' => 1,
        ]);

        $this->predmetProgram = PredmetProgram::create([
            'predmet_id' => $this->predmet->id,
            'studijskiProgram_id' => $this->program->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $this->tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->skolskaGodina->id,
            'indikatorAktivan' => 1,
        ]);

        $prijava = PrijavaIspita::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        $this->zapisnik = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $this->predmet->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => now()->addDay()->toDateString(),
            'vreme' => '10:00',
            'ucionica' => 'A1',
            'prijavaIspita_id' => $prijava->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_zapisnik_returns_view(): void
    {
        $response = $this->get('/zapisnik');

        $response->assertStatus(200);
        $response->assertViewIs('ispit.indexZapisnik');
    }

    public function test_create_zapisnik_returns_view(): void
    {
        $response = $this->get('/zapisnik/create');

        $response->assertStatus(200);
        $response->assertViewIs('ispit.createZapisnik');
        $response->assertViewHasAll(['aktivniIspitniRok', 'predmeti', 'profesori']);
    }

    public function test_vrati_zapisnik_predmet_returns_subject_data(): void
    {
        $response = $this->get('/zapisnik/vratiZapisnikPredmet?rokId='.$this->rok->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->predmet->id]);
        $response->assertJsonFragment(['id' => $this->profesor->id]);
    }

    public function test_vrati_zapisnik_studenti_returns_registered_students(): void
    {
        $response = $this->get('/zapisnik/vratiZapisnikStudenti?predmet_id='.$this->predmet->id.'&rok_id='.$this->rok->id.'&profesor_id='.$this->profesor->id);

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->kandidat->id]);
    }

    public function test_pregled_zapisnik_returns_view(): void
    {
        $response = $this->get('/zapisnik/pregled/'.$this->zapisnik->id);

        $response->assertStatus(200);
        $response->assertViewIs('ispit.pregledZapisnik');
        $response->assertViewHas('zapisnik');
    }

    public function test_store_priznati_ispiti_redirects_back_to_student_exam_page(): void
    {
        $response = $this->post('/storePriznatiIspiti', [
            'kandidat_id' => $this->kandidat->id,
            'predmetId' => [$this->predmetProgram->id],
            'konacnaOcena' => [10],
        ]);

        $response->assertRedirect('/prijava/zaStudenta/'.$this->kandidat->id);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'konacnaOcena' => 10,
            'statusIspita' => 5,
        ]);
    }

    public function test_delete_priznat_ispit_redirects_back_to_student_exam_page(): void
    {
        $polozeniIspit = PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'prijava_id' => null,
            'zapisnik_id' => null,
            'konacnaOcena' => 9,
            'statusIspita' => 5,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->get('/deletePriznatIspit/'.$polozeniIspit->id);

        $response->assertRedirect('/prijava/zaStudenta/'.$this->kandidat->id);
        $this->assertDatabaseMissing('polozeni_ispiti', [
            'id' => $polozeniIspit->id,
        ]);
    }

    public function test_delete_privremeni_ispit_redirects_back(): void
    {
        $statusIspita = StatusIspita::query()->first() ?? StatusIspita::forceCreate([
            'naziv' => 'Privremeni',
            'indikatorAktivan' => 1,
        ]);

        $polozeniIspit = PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'prijava_id' => null,
            'zapisnik_id' => null,
            'statusIspita' => $statusIspita->id,
            'indikatorAktivan' => 0,
        ]);

        $response = $this->from('/prijava/zaStudenta/'.$this->kandidat->id)
            ->get('/deletePrivremeniIspit/'.$polozeniIspit->id);

        $response->assertRedirect('/prijava/zaStudenta/'.$this->kandidat->id);
        $this->assertDatabaseMissing('polozeni_ispiti', [
            'id' => $polozeniIspit->id,
        ]);
    }

    public function test_izmeni_podatke_updates_zapisnik_details(): void
    {
        $response = $this->from('/zapisnik/pregled/'.$this->zapisnik->id)
            ->post('/zapisnik/pregled/izmeniPodatke', [
                'zapisnikId' => $this->zapisnik->id,
                'vreme' => '12:15',
                'ucionica' => 'B2',
                'datum' => '2026-05-10',
                'datum2' => '2026-05-11',
            ]);

        $response->assertRedirect('/zapisnik/pregled/'.$this->zapisnik->id);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $this->zapisnik->id,
            'vreme' => '12:15',
            'ucionica' => 'B2',
        ]);
    }

    public function test_arhiva_zapisnik_returns_view(): void
    {
        $response = $this->get('/zapisnik/arhiva');

        $response->assertStatus(200);
        $response->assertViewIs('ispit.arhivaZapisnik');
        $response->assertViewHas('arhiviraniZapisnici');
    }

    public function test_arhiviraj_zapisnik_archives_record_and_redirects_back(): void
    {
        $response = $this->from('/zapisnik')
            ->get('/zapisnik/arhiviraj/'.$this->zapisnik->id);

        $response->assertRedirect('/zapisnik');
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $this->zapisnik->id,
            'arhiviran' => 1,
        ]);
    }

    public function test_store_zapisnik_creates_new_record_and_redirects(): void
    {
        $response = $this->post('/zapisnik/storeZapisnik', [
            'page' => 1,
            'predmet_id' => $this->predmet->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'datum' => '2026-08-10',
            'datum2' => '2026-08-11',
            'vreme' => '14:00',
            'ucionica' => 'C3',
            'odabir' => [$this->kandidat->id],
        ]);

        $response->assertRedirect('/zapisnik/');
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'predmet_id' => $this->predmet->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'ucionica' => 'C3',
        ]);
    }

    public function test_delete_zapisnik_removes_record_and_redirects_back(): void
    {
        $response = $this->from('/zapisnik')
            ->get('/zapisnik/delete/'.$this->zapisnik->id);

        $response->assertRedirect('/zapisnik');
        $this->assertDatabaseMissing('zapisnik_o_polaganju_ispita', [
            'id' => $this->zapisnik->id,
        ]);
    }

    public function test_polozeni_ispit_saves_exam_results_and_redirects(): void
    {
        $statusPolozio = StatusIspita::query()->firstOrCreate(
            ['naziv' => 'Položio'],
            ['indikatorAktivan' => 1]
        );

        $polozeniIspit = PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'zapisnik_id' => $this->zapisnik->id,
            'prijava_id' => null,
            'konacnaOcena' => 0,
            'statusIspita' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->post('/zapisnik/polozeniIspit', [
            'ispit_id' => [$polozeniIspit->id],
            'ocenaPismeni' => [0 => 15],
            'ocenaUsmeni' => [0 => 20],
            'brojBodova' => [0 => 35],
            'konacnaOcena' => [0 => 7],
            'statusIspita' => [0 => $statusPolozio->id],
        ]);

        $response->assertRedirect('/zapisnik/pregled/'.$this->zapisnik->id);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $polozeniIspit->id,
            'konacnaOcena' => 7,
        ]);
    }

    public function test_priznavanje_ispita_returns_view_with_data(): void
    {
        $response = $this->get('/priznavanjeIspita/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('ispit.priznatiIspiti');
        $response->assertViewHas('kandidat');
        $response->assertViewHas('predmetProgram');
    }

    public function test_delete_polozeni_ispit_removes_exam_result_and_redirects_back(): void
    {
        $polozeniIspit = PolozeniIspiti::create([
            'kandidat_id' => $this->kandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'prijava_id' => null,
            'zapisnik_id' => $this->zapisnik->id,
            'konacnaOcena' => 8,
            'statusIspita' => 1,
            'indikatorAktivan' => 1,
        ]);

        $response = $this->from('/zapisnik/pregled/'.$this->zapisnik->id)
            ->get('/ispit/delete/'.$polozeniIspit->id.'?brisiZapisnik=0');

        $response->assertRedirect('/zapisnik/pregled/'.$this->zapisnik->id);
        $this->assertDatabaseHas('polozeni_ispiti', [
            'id' => $polozeniIspit->id,
            'indikatorAktivan' => 0,
            'konacnaOcena' => 0,
        ]);
    }

    public function test_pregled_zapisnik_delete_removes_student_from_zapisnik(): void
    {
        $secondKandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusStudiranja->id,
            'indikatorAktivan' => 1,
        ]);

        // Ensure both students are linked to the zapisnik
        \App\Models\ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $this->zapisnik->id,
            'kandidat_id' => $this->kandidat->id,
            'prijavaIspita_id' => $this->zapisnik->prijavaIspita_id,
        ]);

        $prijava2 = PrijavaIspita::create([
            'kandidat_id' => $secondKandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        \App\Models\ZapisnikOPolaganju_Student::create([
            'zapisnik_id' => $this->zapisnik->id,
            'kandidat_id' => $secondKandidat->id,
            'prijavaIspita_id' => $prijava2->id,
        ]);

        $response = $this->from('/zapisnik/pregled/'.$this->zapisnik->id)
            ->get('/zapisnik/pregled/'.$this->zapisnik->id.'/'.$this->kandidat->id.'/delete');

        $response->assertRedirect('/zapisnik/pregled/'.$this->zapisnik->id);
    }

    public function test_dodaj_studenta_adds_student_to_zapisnik_and_redirects(): void
    {
        $secondKandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusStudiranja->id,
            'indikatorAktivan' => 1,
        ]);

        PrijavaIspita::create([
            'kandidat_id' => $secondKandidat->id,
            'predmet_id' => $this->predmetProgram->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'brojPolaganja' => 1,
            'datum' => now()->toDateString(),
            'tipPrijave_id' => $this->tipPrijave->id,
        ]);

        $response = $this->post('/zapisnik/pregled/dodajStudenta', [
            'zapisnikId' => $this->zapisnik->id,
            'odabir' => [$secondKandidat->id],
        ]);

        $response->assertRedirect('/zapisnik/pregled/'.$this->zapisnik->id);
    }

    public function test_arhiviraj_zapisnike_za_ispitni_rok_archives_all_for_rok(): void
    {
        $secondZapisnik = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $this->predmet->id,
            'rok_id' => $this->rok->id,
            'profesor_id' => $this->profesor->id,
            'datum' => now()->toDateString(),
            'datum2' => now()->addDay()->toDateString(),
            'vreme' => '11:00',
            'ucionica' => 'D4',
            'prijavaIspita_id' => null,
        ]);

        $response = $this->from('/zapisnik/arhiva')
            ->post('/zapisnik/arhivirajRok', [
                'rok_id' => $this->rok->id,
            ]);

        $response->assertRedirect('/zapisnik/arhiva');
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $this->zapisnik->id,
            'arhiviran' => 1,
        ]);
        $this->assertDatabaseHas('zapisnik_o_polaganju_ispita', [
            'id' => $secondZapisnik->id,
            'arhiviran' => 1,
        ]);
    }
}
