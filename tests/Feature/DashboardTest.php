<?php

namespace Tests\Feature;

use App\Models\AktivniIspitniRokovi;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\KrsnaSlava;
use App\Models\Obavestenje;
use App\Models\Opstina;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\Region;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusProfesora;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\User;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $programA;

    protected StudijskiProgram $programB;

    protected GodinaStudija $godinaStudija;

    protected SkolskaGodUpisa $godinaUpisa2023;

    protected SkolskaGodUpisa $godinaUpisa2024;

    protected StatusStudiranja $statusNijeUpisan;

    protected Kandidat $studentA;

    protected Kandidat $studentB;

    protected Profesor $profesor;

    protected PredmetProgram $predmetProgramA;

    protected PredmetProgram $predmetProgramB;

    protected AktivniIspitniRokovi $ispitniRok;

    protected int $currentYear;

    protected int $expectedAktivnaObavestenja;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        Cache::flush();

        $this->currentYear = (int) date('Y');

        $this->user = User::create([
            'name' => 'Dashboard Admin',
            'email' => 'dashboard_admin_'.uniqid().'@test.com',
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

        $this->godinaStudija = GodinaStudija::forceCreate([
            'id' => 1,
            'naziv' => 'Prva',
            'nazivRimski' => 'I',
            'nazivSlovimaUPadezu' => 'Prve',
            'redosledPrikazivanja' => 1,
            'indikatorAktivan' => 1,
        ]);

        $this->tipStudija = TipStudija::factory()->create([
            'id' => 1,
            'naziv' => 'Osnovne akademske studije',
            'skrNaziv' => 'OAS',
            'indikatorAktivan' => 1,
        ]);

        $this->programA = StudijskiProgram::factory()->create([
            'naziv' => 'Fizioterapija',
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->programB = StudijskiProgram::factory()->create([
            'naziv' => 'Radiologija',
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->godinaUpisa2023 = SkolskaGodUpisa::factory()->create([
            'id' => 1,
            'naziv' => '2023/2024',
            'aktivan' => 0,
        ]);

        $this->godinaUpisa2024 = SkolskaGodUpisa::factory()->create([
            'id' => 2,
            'naziv' => '2024/2025',
            'aktivan' => 1,
        ]);

        $this->statusNijeUpisan = StatusStudiranja::query()->firstOrCreate(
            ['id' => 3],
            ['naziv' => 'kandidat', 'indikatorAktivan' => 1]
        );

        $statusProfesora = StatusProfesora::query()->first() ?? StatusProfesora::create([
            'id' => 1,
            'naziv' => 'Aktivan',
            'indikatorAktivan' => 1,
        ]);

        $this->profesor = Profesor::factory()->create([
            'status_id' => $statusProfesora->id,
            'indikatorAktivan' => 1,
        ]);

        $this->studentA = Kandidat::factory()->create([
            'imeKandidata' => 'Ana',
            'prezimeKandidata' => 'Anic',
            'email' => 'ana.dashboard@test.com',
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->programA->id,
            'skolskaGodinaUpisa_id' => $this->godinaUpisa2024->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusNijeUpisan->id,
            'brojIndeksa' => '2024/0001',
            'imePrezimeJednogRoditelja' => 'Petar',
            'indikatorAktivan' => 1,
        ]);

        $this->studentB = Kandidat::factory()->create([
            'imeKandidata' => 'Bojan',
            'prezimeKandidata' => 'Bojic',
            'email' => 'bojan.dashboard@test.com',
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->programB->id,
            'skolskaGodinaUpisa_id' => $this->godinaUpisa2023->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusNijeUpisan->id,
            'brojIndeksa' => '2023/0002',
            'imePrezimeJednogRoditelja' => 'Milan',
            'indikatorAktivan' => 1,
        ]);

        $tipPredmeta = TipPredmeta::query()->first() ?? TipPredmeta::create([
            'naziv' => 'Obavezni',
            'skrNaziv' => 'OBV',
            'indikatorAktivan' => 1,
        ]);

        $predmetA = Predmet::factory()->create([
            'naziv' => 'Anatomija',
        ]);

        $predmetB = Predmet::factory()->create([
            'naziv' => 'Fiziologija',
        ]);

        $this->predmetProgramA = PredmetProgram::create([
            'predmet_id' => $predmetA->id,
            'studijskiProgram_id' => $this->programA->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->godinaUpisa2024->id,
            'indikatorAktivan' => 1,
        ]);

        $this->predmetProgramB = PredmetProgram::create([
            'predmet_id' => $predmetB->id,
            'studijskiProgram_id' => $this->programB->id,
            'tipStudija_id' => $this->tipStudija->id,
            'semestar' => 1,
            'espb' => 6,
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipPredmeta_id' => $tipPredmeta->id,
            'statusPredmeta' => 1,
            'predavanja' => 2,
            'vezbe' => 2,
            'skolskaGodina_id' => $this->godinaUpisa2024->id,
            'indikatorAktivan' => 1,
        ]);

        $this->ispitniRok = AktivniIspitniRokovi::factory()->create([
            'naziv' => 'Januarski ispitni rok',
            'rok_id' => 1,
            'tipRoka_id' => 1,
            'indikatorAktivan' => 1,
        ]);

        Obavestenje::create([
            'naslov' => 'Aktivno obavestenje',
            'sadrzaj' => 'Tekst obavestenja',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now(),
            'datum_isteka' => now()->addDays(7),
            'profesor_id' => $this->profesor->id,
        ]);

        Obavestenje::create([
            'naslov' => 'Isteklo obavestenje',
            'sadrzaj' => 'Staro',
            'tip' => 'opste',
            'aktivan' => 1,
            'datum_objave' => now()->subDays(10),
            'datum_isteka' => now()->subDay(),
            'profesor_id' => $this->profesor->id,
        ]);

        $this->expectedAktivnaObavestenja = Obavestenje::aktivna()->count();

        $prijava1 = PrijavaIspita::create([
            'kandidat_id' => $this->studentA->id,
            'predmet_id' => $this->predmetProgramA->id,
            'profesor_id' => $this->profesor->id,
            'rok_id' => $this->ispitniRok->id,
            'brojPolaganja' => 1,
            'datum' => sprintf('%d-01-15', $this->currentYear),
            'datum2' => sprintf('%d-01-20', $this->currentYear),
            'vreme' => '10:00:00',
            'tipPrijave_id' => 1,
            'created_at' => sprintf('%d-01-10 10:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-01-10 10:00:00', $this->currentYear),
        ]);

        $prijava2 = PrijavaIspita::create([
            'kandidat_id' => $this->studentB->id,
            'predmet_id' => $this->predmetProgramB->id,
            'profesor_id' => $this->profesor->id,
            'rok_id' => $this->ispitniRok->id,
            'brojPolaganja' => 1,
            'datum' => sprintf('%d-02-15', $this->currentYear),
            'datum2' => sprintf('%d-02-20', $this->currentYear),
            'vreme' => '11:00:00',
            'tipPrijave_id' => 1,
            'created_at' => sprintf('%d-02-10 10:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-02-10 10:00:00', $this->currentYear),
        ]);

        $zapisnik1 = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $predmetA->id,
            'rok_id' => $this->ispitniRok->id,
            'prijavaIspita_id' => $prijava1->id,
            'datum' => sprintf('%d-01-15', $this->currentYear),
            'datum2' => sprintf('%d-01-20', $this->currentYear),
            'vreme' => '10:00:00',
            'ucionica' => 'A1',
            'profesor_id' => $this->profesor->id,
            'kandidat_id' => $this->studentA->id,
            'created_at' => sprintf('%d-01-15 10:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-01-15 10:00:00', $this->currentYear),
        ]);

        $zapisnik2 = ZapisnikOPolaganjuIspita::create([
            'predmet_id' => $predmetB->id,
            'rok_id' => $this->ispitniRok->id,
            'prijavaIspita_id' => $prijava2->id,
            'datum' => sprintf('%d-02-15', $this->currentYear),
            'datum2' => sprintf('%d-02-20', $this->currentYear),
            'vreme' => '11:00:00',
            'ucionica' => 'B2',
            'profesor_id' => $this->profesor->id,
            'kandidat_id' => $this->studentB->id,
            'created_at' => sprintf('%d-02-15 10:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-02-15 10:00:00', $this->currentYear),
        ]);

        PolozeniIspiti::create([
            'prijava_id' => $prijava1->id,
            'zapisnik_id' => $zapisnik1->id,
            'kandidat_id' => $this->studentA->id,
            'predmet_id' => $this->predmetProgramA->id,
            'ocenaPismeni' => 8,
            'ocenaUsmeni' => 8,
            'konacnaOcena' => 8,
            'brojBodova' => 85,
            'statusIspita' => 1,
            'odluka_id' => 1,
            'indikatorAktivan' => 1,
            'created_at' => sprintf('%d-01-20 12:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-01-20 12:00:00', $this->currentYear),
        ]);

        PolozeniIspiti::create([
            'prijava_id' => $prijava2->id,
            'zapisnik_id' => $zapisnik2->id,
            'kandidat_id' => $this->studentB->id,
            'predmet_id' => $this->predmetProgramB->id,
            'ocenaPismeni' => 5,
            'ocenaUsmeni' => 5,
            'konacnaOcena' => 5,
            'brojBodova' => 40,
            'statusIspita' => 0,
            'odluka_id' => 1,
            'indikatorAktivan' => 1,
            'created_at' => sprintf('%d-02-20 12:00:00', $this->currentYear),
            'updated_at' => sprintf('%d-02-20 12:00:00', $this->currentYear),
        ]);

        $this->actingAs($this->user);
    }

    public function test_dashboard_index_returns_aggregated_view_data(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        $response->assertViewHas('ukupnoStudenata', 2);
        $response->assertViewHas('polozeniIspiti', 2);
        $response->assertViewHas('prijavljeniIspiti', 2);
        $response->assertViewHas('aktivnaObavestenja', $this->expectedAktivnaObavestenja);
        $response->assertViewHas('prolaznost', 100.0);
        $response->assertViewHas('skolskaGodinaId', $this->godinaUpisa2024->id);
        $response->assertViewHas('studentiPoProgramu', function ($programi) {
            return $programi->pluck('broj', 'studijskiProgram_id')->sortKeys()->all() === [
                $this->programA->id => 1,
                $this->programB->id => 1,
            ];
        });
        $response->assertViewHas('studentiPoGodini', function ($godine) {
            return $godine->pluck('broj', 'skolskaGodinaUpisa_id')->sortKeys()->all() === [
                $this->godinaUpisa2023->id => 1,
                $this->godinaUpisa2024->id => 1,
            ];
        });
        $response->assertViewHas('najcesciNeuspesni', function ($predmeti) {
            return $predmeti->count() === 1
                && (int) $predmeti->first()->predmet_id === $this->predmetProgramB->id
                && (int) $predmeti->first()->broj === 1;
        });
        $response->assertViewHas('widgets', function (array $widgets) {
            return collect($widgets)->every(fn ($enabled) => $enabled === true);
        });
    }

    public function test_dashboard_widgets_can_be_saved_to_session(): void
    {
        $response = $this->post('/dashboard/widgets', [
            'studenti_ukupno' => 'on',
            'aktivna_obavestenja' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('dashboard_widgets', [
            'studenti_ukupno' => true,
            'polozeni_ispiti' => false,
            'prijavljeni_ispiti' => false,
            'aktivna_obavestenja' => true,
            'studenti_po_programu' => false,
            'studenti_po_godini' => false,
            'prolaznost' => false,
            'neuspesni_predmeti' => false,
        ]);
    }

    public function test_dashboard_studenti_filters_by_program_and_year(): void
    {
        $response = $this->get('/dashboard/studenti?program_id='.$this->programA->id.'&godina_id='.$this->godinaUpisa2024->id);

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.studenti');
        $response->assertViewHas('programId', (string) $this->programA->id);
        $response->assertViewHas('godinaId', (string) $this->godinaUpisa2024->id);
        $response->assertViewHas('studenti', function ($studenti) {
            return $studenti->count() === 1
                && $studenti->first()->is($this->studentA);
        });
    }

    public function test_dashboard_ispiti_returns_monthly_and_subject_analytics(): void
    {
        $response = $this->get('/dashboard/ispiti?godina='.$this->currentYear);

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.ispiti');
        $response->assertViewHas('godina', (string) $this->currentYear);
        $response->assertViewHas('polozeniPoMesecima', function ($meseci) {
            return (int) $meseci->firstWhere('mesec', 1)->broj === 1
                && (int) $meseci->firstWhere('mesec', 2)->broj === 1;
        });
        $response->assertViewHas('prijavePoMesecima', function ($meseci) {
            return (int) $meseci->firstWhere('mesec', 1)->broj === 1
                && (int) $meseci->firstWhere('mesec', 2)->broj === 1;
        });
        $response->assertViewHas('uspehPoPredmetu', function ($predmeti) {
            $anatomija = $predmeti->firstWhere('predmet_id', $this->predmetProgramA->id);
            $fiziologija = $predmeti->firstWhere('predmet_id', $this->predmetProgramB->id);

            return $predmeti->count() === 2
                && (int) $anatomija->ukupno === 1
                && (int) $anatomija->polozeni === 1
                && (float) $anatomija->prosek === 8.0
                && (int) $fiziologija->ukupno === 1
                && (int) $fiziologija->polozeni === 0
                && (float) $fiziologija->prosek === 5.0;
        });
    }
}
