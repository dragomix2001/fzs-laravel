<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\AktivniIspitniRokovi;
use App\Models\Aktivnost;
use App\Models\DiplomskiPolaganje;
use App\Models\Kandidat;
use App\Models\Obavestenje;
use App\Models\Ocenjivanje;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\PrilozenaDokumenta;
use App\Models\Prisanstvo;
use App\Models\ProfesorPredmet;
use App\Models\Raspored;
use App\Models\Skolarina;
use App\Models\SkolskaGodUpisa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class ModelRelationsCoverageTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function aktivnost_and_ocenjivanje_relations_are_defined(): void
    {
        $aktivnost = new Aktivnost;
        $ocenjivanje = new Ocenjivanje;

        $this->assertInstanceOf(BelongsTo::class, $aktivnost->predmet());
        $this->assertInstanceOf(HasMany::class, $aktivnost->ocenjivanja());

        $this->assertInstanceOf(BelongsTo::class, $ocenjivanje->student());
        $this->assertInstanceOf(BelongsTo::class, $ocenjivanje->aktivnost());
        $this->assertInstanceOf(BelongsTo::class, $ocenjivanje->profesor());
    }

    #[Test]
    public function exam_related_model_relations_are_defined(): void
    {
        $polozeni = new PolozeniIspiti;
        $predmetProgram = new PredmetProgram;
        $prijava = new PrijavaIspita;

        $this->assertInstanceOf(BelongsTo::class, $polozeni->kandidat());
        $this->assertInstanceOf(BelongsTo::class, $polozeni->predmet());
        $this->assertInstanceOf(BelongsTo::class, $polozeni->prijava());
        $this->assertInstanceOf(BelongsTo::class, $polozeni->zapisnik());

        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->predmet());
        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->program());
        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->godinaStudija());
        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->tipStudija());
        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->studijskiProgram());
        $this->assertInstanceOf(BelongsTo::class, $predmetProgram->tipPredmeta());
        $this->assertInstanceOf(HasMany::class, $predmetProgram->prijaveIspita());

        $this->assertInstanceOf(BelongsTo::class, $prijava->kandidat());
        $this->assertInstanceOf(BelongsTo::class, $prijava->predmet());
        $this->assertInstanceOf(BelongsTo::class, $prijava->rok());
        $this->assertInstanceOf(BelongsTo::class, $prijava->profesor());
        $this->assertNull(PrijavaIspita::nazivRokaPoId(999999));
    }

    #[Test]
    public function dokumenta_and_prisustvo_relations_are_defined(): void
    {
        $dokument = new PrilozenaDokumenta;
        $prisustvo = new Prisanstvo;

        $this->assertInstanceOf(HasMany::class, $dokument->kandidatDokumenta());
        $this->assertInstanceOf(BelongsToMany::class, $dokument->kandidati());
        $this->assertInstanceOf(BelongsTo::class, $dokument->godinaStudija());

        $this->assertInstanceOf(BelongsTo::class, $prisustvo->student());
        $this->assertInstanceOf(BelongsTo::class, $prisustvo->predmet());
        $this->assertInstanceOf(BelongsTo::class, $prisustvo->nastavnaNedelja());
        $this->assertInstanceOf(BelongsTo::class, $prisustvo->profesor());
    }

    #[Test]
    public function raspored_and_finance_related_models_cover_relations_and_scopes(): void
    {
        $profesorPredmet = new ProfesorPredmet;
        $raspored = new Raspored;
        $skolarina = new Skolarina;

        $this->assertInstanceOf(BelongsTo::class, $profesorPredmet->profesor());
        $this->assertInstanceOf(BelongsTo::class, $profesorPredmet->predmet());
        $this->assertInstanceOf(BelongsTo::class, $profesorPredmet->semestar());
        $this->assertInstanceOf(BelongsTo::class, $profesorPredmet->oblik_nastave());

        $this->assertInstanceOf(BelongsTo::class, $raspored->predmet());
        $this->assertInstanceOf(BelongsTo::class, $raspored->profesor());
        $this->assertInstanceOf(BelongsTo::class, $raspored->studijskiProgram());
        $this->assertInstanceOf(BelongsTo::class, $raspored->godinaStudija());
        $this->assertInstanceOf(BelongsTo::class, $raspored->semestar());
        $this->assertInstanceOf(BelongsTo::class, $raspored->skolskaGodina());
        $this->assertInstanceOf(BelongsTo::class, $raspored->oblikNastave());

        $builder = Raspored::query();
        $raspored->scopeZaDan($builder, 3);
        $raspored->scopeAktivan($builder);
        $this->assertNotEmpty($builder->getQuery()->wheres);

        $this->assertInstanceOf(BelongsTo::class, $skolarina->kandidat());
        $this->assertInstanceOf(HasMany::class, $skolarina->uplate());
        $this->assertInstanceOf(BelongsTo::class, $skolarina->tipStudija());
        $this->assertInstanceOf(BelongsTo::class, $skolarina->godinaStudija());
    }

    #[Test]
    public function skolska_godina_and_user_methods_are_covered(): void
    {
        $godina = (new SkolskaGodUpisa)->forceFill(['naziv' => '2025/2026']);
        $this->assertInstanceOf(HasMany::class, $godina->kandidati());
        $this->assertSame(2025, $godina->godina);

        $godinaNevalidna = (new SkolskaGodUpisa)->forceFill(['naziv' => 'nepoznato']);
        $this->assertNull($godinaNevalidna->godina);

        $user = new User(['role' => User::ROLE_ADMIN]);
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isProfessor());
        $this->assertFalse($user->isStudent());
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasAnyRole(['secretary', 'admin']));
        $this->assertInstanceOf(BelongsTo::class, $user->profesor());
        $this->assertInstanceOf(BelongsTo::class, $user->kandidat());
    }

    #[Test]
    public function kandidat_obavestenje_and_diplomski_polaganje_are_covered(): void
    {
        $kandidat = new Kandidat([
            'imeKandidata' => 'Pera',
            'prezimeKandidata' => 'Peric',
        ]);

        $this->assertInstanceOf(HasMany::class, $kandidat->angazovanja());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->tipStudija());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->program());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->studijskiProgram());
        $this->assertSame('Pera', $kandidat->ime);
        $this->assertSame('Peric', $kandidat->prezime);
        $this->assertInstanceOf(HasMany::class, $kandidat->upisaneGodine());
        $this->assertInstanceOf(HasMany::class, $kandidat->prijaveIspita());
        $this->assertInstanceOf(HasMany::class, $kandidat->kandidatDokumenta());
        $this->assertInstanceOf(BelongsToMany::class, $kandidat->prilozenaDokumenta());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->mestoRodjenja());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->godinaUpisa());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->godinaStudija());
        $this->assertInstanceOf(BelongsTo::class, $kandidat->statusUpisa());

        $obavestenje = new Obavestenje;
        $this->assertInstanceOf(BelongsTo::class, $obavestenje->profesor());
        $this->assertInstanceOf(BelongsToMany::class, $obavestenje->korisnici());

        $obBuilder = Obavestenje::query();
        $obavestenje->scopeZaTip($obBuilder, 'admini');
        $obavestenje->scopeAktivna($obBuilder);
        $this->assertNotEmpty($obBuilder->getQuery()->wheres);

        $diplomski = new DiplomskiPolaganje;
        $this->assertInstanceOf(BelongsTo::class, $diplomski->student());
        $this->assertInstanceOf(BelongsTo::class, $diplomski->predmet());
        $this->assertInstanceOf(BelongsTo::class, $diplomski->profesor());
        $this->assertInstanceOf(BelongsTo::class, $diplomski->ispitniRok());
        $this->assertInstanceOf(BelongsTo::class, $diplomski->clan());
        $this->assertInstanceOf(BelongsTo::class, $diplomski->predsednik());

        $newFactory = new ReflectionMethod(DiplomskiPolaganje::class, 'newFactory');
        $newFactory->setAccessible(true);
        $this->assertNotNull($newFactory->invoke(null));

        $this->assertSame('Редовни', AktivniIspitniRokovi::tipRoka(1));
        $this->assertSame('Ванредни', AktivniIspitniRokovi::tipRoka(2));
        $this->assertSame('', AktivniIspitniRokovi::tipRoka(99));

        $aktivniRok = new AktivniIspitniRokovi;
        $this->assertInstanceOf(BelongsTo::class, $aktivniRok->nadredjeniRok());

        $uniqueId = random_int(300000, 399999);
        if (! DB::table('ispitni_rok')->where('id', 1)->exists()) {
            DB::table('ispitni_rok')->insert([
                'id' => 1,
                'naziv' => 'Coverage parent rok',
                'indikatorAktivan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('aktivni_ispitni_rokovi')->insert([
            'id' => $uniqueId,
            'rok_id' => 1,
            'naziv' => 'Coverage Rok',
            'pocetak' => now()->toDateString(),
            'kraj' => now()->addDays(10)->toDateString(),
            'komentar' => 'Coverage komentar',
            'tipRoka_id' => 1,
            'indikatorAktivan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('Coverage Rok', PrijavaIspita::nazivRokaPoId($uniqueId));
    }
}
