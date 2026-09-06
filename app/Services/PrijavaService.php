<?php

namespace App\Services;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\Predmet;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\Profesor;
use App\Models\ProfesorPredmet;
use App\Models\TipPredmeta;
use App\Models\TipStudija;
use App\Models\ZapisnikOPolaganju_Student;
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
    public function __construct(
        private ?PrijavaPredmetService $predmetService = null,
        private ?BulkPrijavaService $bulkService = null,
        private ?PrijavaStudentService $studentService = null,
    ) {}

    private function predmetDataService(): PrijavaPredmetService
    {
        return $this->predmetService ??= new PrijavaPredmetService;
    }

    private function bulkDataService(): BulkPrijavaService
    {
        return $this->bulkService ??= new BulkPrijavaService;
    }

    private function studentDataService(): PrijavaStudentService
    {
        return $this->studentService ??= new PrijavaStudentService;
    }

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
        return $this->predmetDataService()->getSpisakPredmetaData();
    }

    /**
     * Get all PrijavaIspita records for a given Predmet (by predmet.id).
     *
     * @return array{predmet: Predmet, prijave: Collection}
     */
    public function getPrijaveZaPredmet(int $predmetId): array
    {
        return $this->predmetDataService()->getPrijaveZaPredmet($predmetId);
    }

    /**
     * Get all form data needed for creating a single exam registration (predmet side).
     */
    public function getCreatePrijavaIspitaPredmetData(int $predmetProgramId): array
    {
        return $this->predmetDataService()->getCreatePrijavaIspitaPredmetData($predmetProgramId);
    }

    /**
     * Get all form data needed for bulk exam registration (createManyPredmet view).
     */
    public function getCreatePrijavaIspitaPredmetManyData(int $predmetId): array
    {
        return $this->predmetDataService()->getCreatePrijavaIspitaPredmetManyData($predmetId);
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
        return $this->bulkDataService()->store($data);
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
        return $this->studentDataService()->getSvePrijaveZaStudenta($kandidatId);
    }

    /**
     * Get all form data needed for creating an exam registration (student side).
     */
    public function getCreatePrijavaIspitaStudentData(int $kandidatId): array
    {
        return $this->studentDataService()->getCreatePrijavaIspitaStudentData($kandidatId);
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
        $kandidat = Kandidat::with('godinaStudija')->findOrFail($kandidatId);

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
