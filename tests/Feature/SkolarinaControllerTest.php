<?php

namespace Tests\Feature;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\KrsnaSlava;
use App\Models\Opstina;
use App\Models\Region;
use App\Models\Skolarina;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusStudiranja;
use App\Models\StudijskiProgram;
use App\Models\TipStudija;
use App\Models\UplataSkolarine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SkolarinaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected TipStudija $tipStudija;

    protected StudijskiProgram $program;

    protected GodinaStudija $godinaStudija;

    protected SkolskaGodUpisa $skolskaGodina;

    protected StatusStudiranja $statusStudiranja;

    protected Kandidat $kandidat;

    protected function setUp(): void
    {
        static::$databasePrepared = true;

        parent::setUp();

        Model::unguard();

        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'skolarina_admin_'.uniqid().'@test.com',
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

        $this->program = StudijskiProgram::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'indikatorAktivan' => 1,
        ]);

        $this->skolskaGodina = SkolskaGodUpisa::factory()->create([
            'naziv' => '2024/2025',
        ]);

        $this->statusStudiranja = StatusStudiranja::factory()->create([
            'id' => 1,
            'naziv' => 'upisan',
            'indikatorAktivan' => 1,
        ]);

        $this->kandidat = Kandidat::factory()->create([
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
            'skolskaGodinaUpisa_id' => $this->skolskaGodina->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'statusUpisa_id' => $this->statusStudiranja->id,
            'brojIndeksa' => '2024/0001',
            'imePrezimeJednogRoditelja' => 'Petar',
            'indikatorAktivan' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_index_returns_view_without_current_tuition(): void
    {
        $response = $this->get('/skolarina/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.index');
        $response->assertViewHas('kandidat', function (Kandidat $kandidat) {
            return $kandidat->is($this->kandidat);
        });
        $response->assertViewHas('trenutnaSkolarina', null);
        $response->assertViewHas('uplacenIznos', 0);
        $response->assertViewHas('preostaliIznos', 0);
    }

    public function test_index_returns_current_tuition_with_payment_totals(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 40000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-01',
        ]);

        UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 20000,
            'naziv' => 'Druga rata',
            'datum' => '2024-11-01',
        ]);

        $response = $this->get('/skolarina/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.index');
        $response->assertViewHas('trenutnaSkolarina', function (Skolarina $current) use ($skolarina) {
            return $current->is($skolarina);
        });
        $response->assertViewHas('uplacenIznos', 60000);
        $response->assertViewHas('preostaliIznos', 60000);
        $response->assertViewHas('trenutneUplate', function ($uplate) {
            return $uplate->count() === 2;
        });
    }

    public function test_create_returns_tuition_form_with_lookup_data(): void
    {
        $response = $this->get('/skolarina/dodavanje/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.dodavanje');
        $response->assertViewHasAll([
            'kandidat',
            'tipStudija',
            'godinaStudija',
        ]);
    }

    public function test_store_creates_new_tuition_and_redirects_to_archive(): void
    {
        $response = $this->post('/skolarina/store', [
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 90000,
            'komentar' => 'Redovna skolarina',
            'tipStudija_id' => $this->tipStudija->id,
            'godinaStudija_id' => $this->godinaStudija->id,
        ]);

        $response->assertRedirect('/skolarina/arhiva/'.$this->kandidat->id);
        $this->assertDatabaseHas('skolarina', [
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 90000,
            'komentar' => 'Redovna skolarina',
            'tipStudija_id' => $this->tipStudija->id,
            'godinaStudija_id' => $this->godinaStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);
    }

    public function test_edit_returns_existing_tuition_form(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 100000,
            'komentar' => 'Obnova godine',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->get('/skolarina/izmena/'.$skolarina->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.izmena');
        $response->assertViewHas('skolarina', function (Skolarina $current) use ($skolarina) {
            return $current->is($skolarina);
        });
    }

    public function test_store_updates_existing_tuition_and_redirects_to_archive(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 100000,
            'komentar' => 'Obnova godine',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->post('/skolarina/store', [
            'id' => $skolarina->id,
            'iznos' => 110000,
            'komentar' => 'Azurirana obnova',
            'tipStudija_id' => $this->tipStudija->id,
            'godinaStudija_id' => $this->godinaStudija->id,
        ]);

        $response->assertRedirect('/skolarina/arhiva/'.$this->kandidat->id);
        $this->assertDatabaseHas('skolarina', [
            'id' => $skolarina->id,
            'iznos' => 110000,
            'komentar' => 'Azurirana obnova',
        ]);
    }

    public function test_create_uplata_returns_payment_form(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->get('/skolarina/uplata/'.$skolarina->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.createUplata');
        $response->assertViewHas('skolarina', function (Skolarina $current) use ($skolarina) {
            return $current->is($skolarina);
        });
    }

    public function test_store_uplata_creates_payment_and_redirects_to_current_tuition(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->post('/uplata/store', [
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 30000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-15',
        ]);

        $response->assertRedirect('/skolarina/'.$this->kandidat->id);
        $this->assertDatabaseHas('uplata_skolarine', [
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 30000,
            'naziv' => 'Prva rata',
        ]);
    }

    public function test_edit_uplata_returns_existing_payment_form(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $uplata = UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 30000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-15',
        ]);

        $response = $this->get('/skolarina/uplata/edit/'.$uplata->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.editUplata');
        $response->assertViewHas('uplata', function (UplataSkolarine $payment) use ($uplata) {
            return $payment->is($uplata);
        });
    }

    public function test_store_uplata_updates_existing_payment(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $uplata = UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 30000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-15',
        ]);

        $response = $this->post('/uplata/store', [
            'id' => $uplata->id,
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 35000,
            'naziv' => 'Ispravljena rata',
            'datum' => '2024-10-20',
        ]);

        $response->assertRedirect('/skolarina/'.$this->kandidat->id);
        $this->assertDatabaseHas('uplata_skolarine', [
            'id' => $uplata->id,
            'iznos' => 35000,
            'naziv' => 'Ispravljena rata',
        ]);
    }

    public function test_delete_uplata_removes_payment_and_redirects(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $uplata = UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 30000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-15',
        ]);

        $response = $this->get('/skolarina/uplata/delete/'.$uplata->id);

        $response->assertRedirect('/skolarina/'.$this->kandidat->id);
        $this->assertDatabaseMissing('uplata_skolarine', [
            'id' => $uplata->id,
        ]);
    }

    public function test_arhiva_returns_all_tuition_entries(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->get('/skolarina/arhiva/'.$this->kandidat->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.arhiva');
        $response->assertViewHas('sveSkolarine', function ($skolarine) use ($skolarina) {
            return $skolarine->contains('id', $skolarina->id);
        });
    }

    public function test_view_returns_selected_tuition_summary(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        UplataSkolarine::create([
            'skolarina_id' => $skolarina->id,
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 50000,
            'naziv' => 'Prva rata',
            'datum' => '2024-10-15',
        ]);

        $response = $this->get('/skolarina/view/'.$skolarina->id);

        $response->assertStatus(200);
        $response->assertViewIs('skolarina.index');
        $response->assertViewHas('uplacenIznos', 50000);
        $response->assertViewHas('preostaliIznos', 70000);
    }

    public function test_delete_removes_tuition_and_redirects_to_archive(): void
    {
        $skolarina = Skolarina::create([
            'kandidat_id' => $this->kandidat->id,
            'iznos' => 120000,
            'komentar' => 'Redovna skolarina',
            'godinaStudija_id' => $this->godinaStudija->id,
            'tipStudija_id' => $this->tipStudija->id,
            'studijskiProgram_id' => $this->program->id,
        ]);

        $response = $this->get('/skolarina/delete/'.$skolarina->id);

        $response->assertRedirect('/skolarina/arhiva/'.$this->kandidat->id);
        $this->assertDatabaseMissing('skolarina', [
            'id' => $skolarina->id,
        ]);
    }
}
