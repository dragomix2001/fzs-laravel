<?php

namespace App\Services;

use App\Models\AktivniIspitniRokovi;
use App\Models\DiplomskiPolaganje;
use App\Models\DiplomskiPrijavaOdbrane;
use App\Models\DiplomskiPrijavaTeme;
use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\StudijskiProgram;
use App\Models\TipPredmeta;
use App\Models\TipPrijave;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Database\Eloquent\Collection;

/**
 * Prijava Service — orchestrates all exam registration and thesis CRUD operations.
 *
 * Responsibilities:
 * - Exam registration listings and form data (predmet / student side)
 * - Bulk exam registration with optional Zapisnik creation
 * - PrijavaIspita create / delete (including cascading cleanup)
 * - AJAX helpers (vratiKandidataPrijava, vratiPredmetPrijava, vratiKandidataPoBroju, vratiIspitPoId)
 * - Diplomski rad CRUD (tema, odbrana, polaganje)
 * - Temporary retroactive exam entry (unosPrivremeni, dodajPolozeneIspite)
 *
 * @see PrijavaController
 */
class PrijavaService
{
    // -------------------------------------------------------------------------
    // region PRIJAVA ISPITA - PREDMET
    // -------------------------------------------------------------------------

    /**
     * Get data for the predmet listing page.
     *
     * @return array{tipStudija: Collection, studijskiProgrami: Collection, predmeti: Collection}
     */
    public function getSpisakPredmetaData(): array
    {
        $tipStudija = TipStudija::all();
        $predmeti = Predmet::all();
        $studijskiProgrami = StudijskiProgram::all();

        return compact('tipStudija', 'studijskiProgrami', 'predmeti');
    }

    /**
     * Get all PrijavaIspita records for a given Predmet (by predmet.id).
     *
     * @return array{predmet: Predmet, prijave: Collection}
     */
    public function getPrijaveZaPredmet(int $predmetId): array
    {
        $predmetProgram = PredmetProgram::where(['predmet_id' => $predmetId])->get();

        $prijave = new Collection;
        foreach ($predmetProgram as $predmet) {
            $prijave = $prijave->merge($predmet->prijaveIspita);
        }

        $predmet = Predmet::find($predmetId);

        return compact('predmet', 'prijave');
    }

    /**
     * Get all form data needed for creating a single exam registration (predmet side).
     */
    public function getCreatePrijavaIspitaPredmetData(int $predmetProgramId): array
    {
        $predmet = PredmetProgram::find($predmetProgramId);
        $kandidat = null;

        $brojeviIndeksa = Kandidat::where([
            'tipStudija_id' => $predmet->tipStudija_id,
            'studijskiProgram_id' => $predmet->studijskiProgram_id,
            'statusUpisa_id' => 1,
        ])->select('id', 'BrojIndeksa as naziv')->get();

        $studijskiProgram = StudijskiProgram::where(['id' => $predmet->studijskiProgram_id])->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();

        $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $predmet->id])->select('profesor_id')->first();
        if ($profesorPredmet === null) {
            $profesor = Profesor::all();
        } else {
            $profesor = Profesor::where(['id' => $profesorPredmet->profesor_id])->get();
        }

        $tipPrijave = TipPrijave::all();

        return compact(
            'kandidat', 'brojeviIndeksa', 'predmet', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave'
        );
    }

    /**
     * Get all form data needed for bulk exam registration (createManyPredmet view).
     */
    public function getCreatePrijavaIspitaPredmetManyData(int $predmetId): array
    {
        $predmet = Predmet::find($predmetId);

        $studijskiProgrami = PredmetProgram::where(['predmet_id' => $predmetId])
            ->pluck('studijskiProgram_id')
            ->all();

        if (! empty($studijskiProgrami)) {
            $kandidati = Kandidat::where(['statusUpisa_id' => 1])
                ->whereIn('studijskiProgram_id', $studijskiProgrami)
                ->orderBy('brojIndeksa')
                ->get();
        } else {
            $kandidati = Kandidat::where(['statusUpisa_id' => 1])
                ->orderBy('brojIndeksa')
                ->get();
        }

        $kandidatiJson = $kandidati->map(fn ($k) => [
            'id' => $k->id,
            'label' => $k->brojIndeksa.' - '.$k->imeKandidata.' '.$k->prezimeKandidata,
            'value' => $k->id,
        ]);

        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();
        $profesor = Profesor::all();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $tipPrijave = TipPrijave::all();
        $studijskiProgram = StudijskiProgram::whereIn('id', $studijskiProgrami)->get();

        return compact(
            'kandidati', 'kandidatiJson', 'predmet', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave'
        );
    }

    /**
     * Store many exam registrations at once, optionally creating a Zapisnik.
     *
     * The $data array must contain:
     *   - odabir        array<int>  — kandidat IDs
     *   - predmet_id    int
     *   - rok_id        int
     *   - profesor_id   int
     *   - datum         string
     *   - datum2        string|null
     *   - tipPrijave_id int
     *   - withZapisnik  bool        — true when Submit2 was pressed
     *
     * @param  array<string, mixed>  $data
     * @return array{errorArray: array, duplicateArray: array}
     */
    public function storePrijavaIspitaPredmetMany(array $data): array
    {
        $errorArray = [];
        $duplicateArray = [];

        $zapisnik = null;

        if ($data['withZapisnik']) {
            $zapisnik = new ZapisnikOPolaganjuIspita;
            $zapisnik->predmet_id = $data['predmet_id'];
            $zapisnik->datum = $data['datum'];
            $zapisnik->datum2 = $data['datum2'] ?? null;
            $zapisnik->rok_id = $data['rok_id'];
            $zapisnik->profesor_id = $data['profesor_id'];
            $zapisnik->save();
        }

        // Pre-fetch all kandidati by id for O(1) lookups
        $kandidatiMap = Kandidat::whereIn('id', $data['odabir'])->get()->keyBy('id');

        $uniqueStudijskiProgramIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->values();

        $predmetProgramMap1 = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $uniqueStudijskiProgramIds)
            ->get()
            ->keyBy(fn ($pp) => $pp->tipStudija_id.'_'.$pp->studijskiProgram_id);

        $predmetProgramMap2 = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $uniqueStudijskiProgramIds)
            ->get()
            ->keyBy(fn ($pp) => $pp->studijskiProgram_id);

        $smerovi = [];

        foreach ($data['odabir'] as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);

            $predmetProgramZaPrijavu = $predmetProgramMap1->get(
                $kandidat->tipStudija_id.'_'.$kandidat->studijskiProgram_id
            );

            if ($predmetProgramZaPrijavu === null) {
                continue;
            }

            $exists = PrijavaIspita::where([
                'kandidat_id' => $kandidatId,
                'rok_id' => $data['rok_id'],
                'predmet_id' => $predmetProgramZaPrijavu->id,
            ])->exists();

            if ($exists) {
                $duplicateArray[] = $kandidat;

                continue;
            }

            $prijava = new PrijavaIspita;
            $prijava->kandidat_id = $kandidatId;
            $prijava->predmet_id = $predmetProgramZaPrijavu->id;
            $prijava->rok_id = $data['rok_id'];
            $prijava->profesor_id = $data['profesor_id'];
            $prijava->brojPolaganja = 1;
            $prijava->datum = $data['datum'];
            $prijava->tipPrijave_id = $data['tipPrijave_id'];

            if ($zapisnik !== null) {
                $zapisnik->predmet_id = $data['predmet_id'];
                $zapisnik->save();
            }

            $saved = $prijava->save();

            if ($zapisnik !== null) {
                $zapisStudent = new ZapisnikOPolaganju_Student;
                $zapisStudent->zapisnik_id = $zapisnik->id;
                $zapisStudent->prijavaIspita_id = $prijava->id;
                $zapisStudent->kandidat_id = $kandidatId;
                $zapisStudent->save();

                $smerovi[] = $kandidat->studijskiProgram_id;

                /** @var PredmetProgram $predmetProgramForPolozen */
                $predmetProgramForPolozen = $predmetProgramMap2->get($kandidat->studijskiProgram_id);

                $polozenIspit = new PolozeniIspiti;
                $polozenIspit->indikatorAktivan = false;
                $polozenIspit->kandidat_id = $kandidatId;
                $polozenIspit->predmet_id = $predmetProgramForPolozen->id;
                $polozenIspit->zapisnik_id = $zapisnik->id;
                $polozenIspit->prijava_id = $prijava->id;
                $polozenIspit->save();
            }

            if (! $saved) {
                $errorArray[] = $kandidatiMap->get($kandidatId);
            }
        }

        if ($zapisnik !== null) {
            $smerovi = array_unique($smerovi);
            foreach ($smerovi as $smerId) {
                $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
                $zapisSmer->zapisnik_id = $zapisnik->id;
                $zapisSmer->StudijskiProgram_id = $smerId;
                $zapisSmer->save();
            }
        }

        return compact('errorArray', 'duplicateArray');
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // region PRIJAVA ISPITA - STUDENT
    // -------------------------------------------------------------------------

    /**
     * Get all data for the student status page (sve prijave).
     */
    public function getSvePrijaveZaStudenta(int $kandidatId): array
    {
        $kandidat = Kandidat::find($kandidatId);
        $prijave = $kandidat->prijaveIspita()->get();

        $diplomskiRadTema = DiplomskiPrijavaTeme::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadOdbrana = DiplomskiPrijavaOdbrane::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $diplomskiRadPolaganje = DiplomskiPolaganje::where([
            'kandidat_id' => $kandidatId,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->first();

        $ispiti = PolozeniIspiti::where([
            'kandidat_id' => $kandidatId,
            'indikatorAktivan' => 1,
        ])->get();

        return compact(
            'kandidat', 'prijave',
            'diplomskiRadTema', 'diplomskiRadOdbrana', 'diplomskiRadPolaganje',
            'ispiti'
        );
    }

    /**
     * Get all form data needed for creating an exam registration (student side).
     */
    public function getCreatePrijavaIspitaStudentData(int $kandidatId): array
    {
        $kandidat = Kandidat::find($kandidatId);

        $brojeviIndeksa = Kandidat::where([
            'statusUpisa_id' => 1,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
            'godinaStudija_id' => $kandidat->godinaStudija_id,
        ])->select('id', 'BrojIndeksa as naziv')->get();

        $predmeti = PredmetProgram::where([
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ])->orderBy('semestar')->get();

        $studijskiProgram = StudijskiProgram::where(['id' => $kandidat->studijskiProgram_id])->get();
        $godinaStudija = GodinaStudija::all();
        $tipPredmeta = TipPredmeta::all();
        $tipStudija = TipStudija::all();
        $ispitniRok = AktivniIspitniRokovi::where(['indikatorAktivan' => 1])->get();
        $profesor = Profesor::all();

        if ($predmeti->isEmpty()) {
            $profesori = Profesor::all();
        } else {
            $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $predmeti->first()->id])
                ->select('profesor_id')
                ->get();
            $ids = array_map(fn (ProfesorPredmet $o) => $o->profesor_id, $profesorPredmet->all());
            $profesori = Profesor::whereIn('id', $ids)->get();
        }

        $tipPrijave = TipPrijave::all();

        return compact(
            'kandidat', 'brojeviIndeksa', 'predmeti', 'studijskiProgram',
            'godinaStudija', 'tipPredmeta', 'tipStudija', 'ispitniRok',
            'profesor', 'tipPrijave', 'profesori'
        );
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // region PRIJAVA ISPITA - SAVE/DELETE + AJAX
    // -------------------------------------------------------------------------

    /**
     * Create and persist a new PrijavaIspita.
     */
    public function storePrijavaIspita(array $data): PrijavaIspita
    {
        $prijava = new PrijavaIspita($data);
        $prijava->save();

        return $prijava;
    }

    /**
     * Delete a PrijavaIspita and cascade to ZapisnikOPolaganju_Student,
     * PolozeniIspiti, and — if the last student in the zapisnik — the
     * ZapisnikOPolaganjuIspita itself.
     *
     * @return array{kandidat_id: int, predmet_id: int}
     */
    public function deletePrijavaIspita(int $prijavaId): array
    {
        $prijava = PrijavaIspita::find($prijavaId);
        $kandidatId = $prijava->kandidat_id;
        $predmetId = PredmetProgram::find($prijava->predmet_id)->predmet_id;

        $zapisnikStudent = ZapisnikOPolaganju_Student::where(['prijavaIspita_id' => $prijavaId])->first();
        $polozeniIspit = PolozeniIspiti::where(['prijava_id' => $prijavaId])->first();

        $zapisnikId = 0;
        if ($zapisnikStudent !== null) {
            $zapisnikId = $zapisnikStudent->zapisnik_id;
            $zapisnikStudent->delete();
        }

        if ($polozeniIspit !== null) {
            $polozeniIspit->delete();
        }

        PrijavaIspita::destroy($prijavaId);

        $zapisnikProvera = ZapisnikOPolaganju_Student::where(['zapisnik_id' => $zapisnikId])->get();
        if ($zapisnikId !== 0 && $zapisnikProvera->count() === 0) {
            ZapisnikOPolaganjuIspita::destroy($zapisnikId);
        }

        return ['kandidat_id' => $kandidatId, 'predmet_id' => $predmetId];
    }

    /**
     * AJAX: return kandidat + their predmeti as HTML options.
     *
     * @return array{student: Kandidat, predmeti: string}
     */
    public function vratiKandidataPrijava(int $kandidatId): array
    {
        $kandidat = Kandidat::find($kandidatId);
        $predmetProgram = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->get();

        $stringPredmeti = '';
        foreach ($predmetProgram as $item) {
            $stringPredmeti .= "<option value='{$item->id}'>{$item->predmet->naziv}</option>";
        }

        return ['student' => $kandidat, 'predmeti' => $stringPredmeti];
    }

    /**
     * AJAX: return tipPredmeta, godinaStudija, tipStudija and profesori HTML options for a PredmetProgram.
     *
     * @return array{tipPredmeta: int, godinaStudija: int, tipStudija: int, profesori: string}
     */
    public function vratiPredmetPrijava(int $predmetProgramId): array
    {
        $predmetProgram = PredmetProgram::find($predmetProgramId);
        $profesorPredmet = ProfesorPredmet::where(['predmet_id' => $predmetProgramId])
            ->select('profesor_id')
            ->get();

        if ($profesorPredmet->isEmpty()) {
            $profesori = Profesor::all();
        } else {
            $ids = array_map(fn (ProfesorPredmet $o) => $o->profesor_id, $profesorPredmet->all());
            $profesori = Profesor::whereIn('id', $ids)->get();
        }

        $stringProfesori = '';
        foreach ($profesori as $item) {
            $stringProfesori .= "<option value='{$item->id}'>".$item->zvanje.' '.$item->ime.' '.$item->prezime.'</option>';
        }

        return [
            'tipPredmeta' => $predmetProgram->tipPredmeta_id,
            'godinaStudija' => $predmetProgram->godinaStudija_id,
            'tipStudija' => $predmetProgram->tipStudija_id,
            'profesori' => $stringProfesori,
        ];
    }

    /**
     * AJAX: return a single kandidat row HTML for the bulk-registration table.
     */
    public function vratiKandidataPoBroju(int $kandidatId): string
    {
        $kandidat = Kandidat::find($kandidatId);

        return '<tr>'.
            "<td><input type='checkbox' name='odabir[]' value='$kandidat->id' checked></td>".
            "<td>{$kandidat->brojIndeksa}</td>".
            '<td>'.$kandidat->imeKandidata.' '.$kandidat->prezimeKandidata.'</td>'.
            "<td>{$kandidat->godinaStudija->nazivRimski}</td></tr>";
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    // region PRIVREMENI DEO
    // -------------------------------------------------------------------------

    /**
     * Get data for the retroactive exam entry page.
     *
     * @return array{kandidat: Kandidat, ispiti: Collection, polozeniIspiti: Collection}
     */
    public function getUnosPrivremeniData(int $kandidatId): array
    {
        $kandidat = Kandidat::findOrFail($kandidatId);
        $ispiti = PredmetProgram::where([
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->get();
        $polozeniIspiti = PolozeniIspiti::where(['kandidat_id' => $kandidat->id])->get();

        return compact('kandidat', 'ispiti', 'polozeniIspiti');
    }

    /**
     * AJAX: return a single predmetProgram row HTML for the retroactive entry table.
     */
    public function vratiIspitPoId(int $predmetProgramId): string
    {
        $predmet = PredmetProgram::find($predmetProgramId);

        return '<tr>'.
            "<td><input type='checkbox' name='odabir[$predmet->id]' value='$predmet->id' checked></td>".
            "<td>{$predmet->predmet->naziv}</td>".
            "<td><select class='konacnaOcena' data-index='$predmet->id' name='konacnaOcena[$predmet->id]'>".
            "<option value='0'></option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option></select></td></tr>";
    }

    /**
     * Persist multiple retroactively-acknowledged passed exams for a kandidat.
     *
     * @param  array<int, int>  $ispiti  index => predmet_id
     * @param  array<int, int>  $konacnaOcena  index => grade value
     */
    public function dodajPolozeneIspite(int $kandidatId, array $ispiti, array $konacnaOcena): void
    {
        foreach ($ispiti as $index => $ispit) {
            $novIspit = new PolozeniIspiti;
            $novIspit->prijava_id = null;
            $novIspit->zapisnik_id = null;
            $novIspit->kandidat_id = $kandidatId;
            $novIspit->predmet_id = $ispit;
            $novIspit->ocenaPismeni = 0;
            $novIspit->ocenaUsmeni = 0;
            $novIspit->konacnaOcena = $konacnaOcena[$index];
            $novIspit->brojBodova = 0;
            $novIspit->statusIspita = 1;
            $novIspit->odluka_id = 0;
            $novIspit->indikatorAktivan = true;
            $novIspit->save();
        }
    }

    // -------------------------------------------------------------------------
    // endregion
    // -------------------------------------------------------------------------
}
