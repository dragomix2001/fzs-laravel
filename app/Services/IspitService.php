<?php

namespace App\Services;

use App\DTOs\ZapisnikData;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PredmetProgram;
use App\Models\PrijavaIspita;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Carbon\Carbon;

/**
 * Ispit Service - Main orchestrator for exam operations and records.
 *
 * Responsibilities:
 * - Exam records (Zapisnik) orchestration and mutation flows
 * - Exam registration management (PrijavaIspita)
 * - Grade recording (PolozeniIspiti)
 * - Student membership changes within existing zapisnici
 * - Exam recognition and zapisnik detail updates
 *
 * @see IspitController
 * @see IspitPdfService  For PDF generation (extracted)
 * @see IspitZapisnikService For listing, create-form, AJAX lookup, and archive concerns
 * @see ZapisnikData
 * @see ZapisnikOPolaganjuIspita
 */
class IspitService
{
    public function __construct(
        protected IspitZapisnikService $ispitZapisnikService,
        protected IspitResultService $ispitResultService
    ) {}

    // -------------------------------------------------------------------------
    // Index / listing
    // -------------------------------------------------------------------------

    /**
     * Get all exam records (Zapisnici) with optional filters.
     *
     * @param  array  $filters  Associative array (filter_predmet_id, filter_rok_id, filter_profesor_id)
     * @return array Contains 'zapisnici', 'predmeti', 'profesori', 'aktivniIspitniRok'
     */
    public function getZapisniciForIndex(array $filters): array
    {
        return $this->ispitZapisnikService->getZapisniciForIndex($filters);
    }

    // -------------------------------------------------------------------------
    // Create zapisnik — reference data
    // -------------------------------------------------------------------------

    /**
     * Get all reference data needed for creating a new exam record (Zapisnik).
     *
     * @return array Contains 'aktivniIspitniRok', 'predmeti', 'profesori'
     */
    public function getCreateZapisnikData(): array
    {
        return $this->ispitZapisnikService->getCreateZapisnikData();
    }

    // -------------------------------------------------------------------------
    // AJAX helpers
    // -------------------------------------------------------------------------

    /**
     * Get subjects and professors linked to a specific exam period (Rok).
     *
     * @param  int  $rokId  The exam period ID
     * @return array Contains 'predmeti', 'profesori'
     */
    public function getZapisnikPredmetData(int $rokId): array
    {
        return $this->ispitZapisnikService->getZapisnikPredmetData($rokId);
    }

    /**
     * Get students registered for a specific exam (subject, period, professor).
     *
     * @param  int  $predmetId  Subject ID
     * @param  int  $rokId  Exam period ID
     * @param  int  $profesorId  Professor ID
     * @return array List of candidate IDs and their data
     */
    public function getZapisnikStudenti(int $predmetId, int $rokId, int $profesorId): array
    {
        return $this->ispitZapisnikService->getZapisnikStudenti($predmetId, $rokId, $profesorId);
    }

    // -------------------------------------------------------------------------
    // Store zapisnik
    // -------------------------------------------------------------------------

    /**
     * Create a new exam record (Zapisnik) and associate students.
     *
     * Handles the creation of ZapisnikOPolaganjuIspita, links students,
     * creates placeholder grade records (PolozeniIspiti), and links study programs.
     *
     * @param  array  $data  Main Zapisnik data (predmet_id, rok_id, profesor_id, etc.)
     * @param  array  $odabir  List of student IDs to include in the record
     *
     * @throws \Exception If database transaction fails
     * @return ZapisnikOPolaganjuIspita The created record instance
     */
    public function createZapisnik(array $data, array $odabir): ZapisnikOPolaganjuIspita
    {
        $zapisnik = new ZapisnikOPolaganjuIspita($data);
        $zapisnik->save();

        $smerovi = [];

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');

        $studijskiProgramIds = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMap = PredmetProgram::where('predmet_id', $data['predmet_id'])
            ->whereIn('studijskiProgram_id', $studijskiProgramIds)
            ->get()
            ->keyBy('studijskiProgram_id');

        foreach ($odabir as $id) {
            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $zapisnik->prijavaIspita_id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            $kandidat = $kandidatiMap->get($id);
            $smerovi[] = $kandidat->studijskiProgram_id;

            $predmetProgramRecord = $predmetProgramMap->get($kandidat->studijskiProgram_id);

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = false;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $predmetProgramRecord->id;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $zapisnik->prijavaIspita_id;
            $polozenIspit->save();
        }

        $smerovi = array_unique($smerovi);
        foreach ($smerovi as $id) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $id;
            $zapisSmer->save();
        }

        return $zapisnik;
    }

    /**
     * Store a new exam record using a validated DTO.
     *
     * @param  ZapisnikData  $data  Validated DTO containing main record and student info
     * @return ZapisnikOPolaganjuIspita The created record instance
     */
    public function storeZapisnik(ZapisnikData $data): ZapisnikOPolaganjuIspita
    {
        return $this->createZapisnik($data->toArray(), $data->studentiIds);
    }

    // -------------------------------------------------------------------------
    // Pregled zapisnika
    // -------------------------------------------------------------------------

    public function getZapisnikPregled(int $zapisnikId): array
    {
        return $this->ispitResultService->getZapisnikPregled($zapisnikId);
    }

    // -------------------------------------------------------------------------
    // Save exam results
    // -------------------------------------------------------------------------

    /**
     * Save/update exam grades and scores for multiple students in a record.
     *
     * @param  array  $ispitIds  List of PolozeniIspiti IDs
     * @param  array  $ocenePismeni  List of written exam grades
     * @param  array  $oceneUsmeni  List of oral exam grades
     * @param  array  $konacneOcene  List of final grades
     * @param  array  $brojBodova  List of total points
     * @param  array  $statusIspita  List of exam statuses (passed, failed, etc.)
     * @return int The ID of the associated Zapisnik
     */
    public function savePolozeniIspiti(array $ispitIds, array $ocenePismeni, array $oceneUsmeni, array $konacneOcene, array $brojBodova, array $statusIspita): int
    {
        return $this->ispitResultService->savePolozeniIspiti(
            $ispitIds,
            $ocenePismeni,
            $oceneUsmeni,
            $konacneOcene,
            $brojBodova,
            $statusIspita
        );
    }

    // -------------------------------------------------------------------------
    // Add student to zapisnik
    // -------------------------------------------------------------------------

    /**
     * Add more students to an existing exam record (Zapisnik).
     *
     * @param  int  $zapisnikId  The record ID to update
     * @param  array  $odabir  List of student IDs to add
     */
    public function addStudentToZapisnik(int $zapisnikId, array $odabir): void
    {
        $zapisnik = ZapisnikOPolaganjuIspita::find($zapisnikId);

        $prijavljeniStudenti = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('kandidat_id')->all();

        $prijavljeniSmerovi = ZapisnikOPolaganju_StudijskiProgram::where([
            'zapisnik_id' => $zapisnikId,
        ])->pluck('studijskiProgram_id')->all();

        $smerovi = [];

        $kandidatiMap = Kandidat::whereIn('id', $odabir)->get()->keyBy('id');

        $studijskiProgramIdsForDodaj = $kandidatiMap->pluck('studijskiProgram_id')->unique()->all();
        $predmetProgramMapDodaj = PredmetProgram::where('predmet_id', $zapisnik->predmet_id)
            ->whereIn('studijskiProgram_id', $studijskiProgramIdsForDodaj)
            ->get()
            ->groupBy('studijskiProgram_id');

        foreach ($odabir as $id) {
            if (in_array($id, $prijavljeniStudenti)) {
                // ako student vec postoji u zapisniku, preskacemo ga
                continue;
            }
            $kandidat = $kandidatiMap->get($id);
            $predmetProgram = $predmetProgramMapDodaj->get($kandidat->studijskiProgram_id)?->first();

            if ($predmetProgram === null) {
                continue;
            }

            $novaPrijava = new PrijavaIspita;
            $novaPrijava->kandidat_id = $id;
            $novaPrijava->predmet_id = $predmetProgram->id;
            $novaPrijava->profesor_id = $zapisnik->profesor_id;
            $novaPrijava->rok_id = $zapisnik->rok_id;
            $novaPrijava->brojPolaganja = 1;
            $novaPrijava->datum = Carbon::now();
            $novaPrijava->datum2 = Carbon::now();
            $novaPrijava->vreme = $zapisnik->vreme;
            $novaPrijava->tipPrijave_id = 0;
            $novaPrijava->save();

            $zapisStudent = new ZapisnikOPolaganju_Student;
            $zapisStudent->zapisnik_id = $zapisnik->id;
            $zapisStudent->prijavaIspita_id = $novaPrijava->id;
            $zapisStudent->kandidat_id = $id;
            $zapisStudent->save();

            if (! in_array($kandidat->studijskiProgram_id, $prijavljeniSmerovi)) {
                $smerovi[] = $kandidat->studijskiProgram_id;
            }

            $polozenIspit = new PolozeniIspiti;
            $polozenIspit->indikatorAktivan = false;
            $polozenIspit->kandidat_id = $id;
            $polozenIspit->predmet_id = $predmetProgram->id;
            $polozenIspit->zapisnik_id = $zapisnik->id;
            $polozenIspit->prijava_id = $novaPrijava->id;
            $polozenIspit->save();
        }

        $smerovi = array_unique($smerovi);
        foreach ($smerovi as $id) {
            $zapisSmer = new ZapisnikOPolaganju_StudijskiProgram;
            $zapisSmer->zapisnik_id = $zapisnik->id;
            $zapisSmer->StudijskiProgram_id = $id;
            $zapisSmer->save();
        }
    }

    // -------------------------------------------------------------------------
    // Delete student from zapisnik
    // -------------------------------------------------------------------------

    /**
     * Remove a student from an exam record and clean up associated records.
     *
     * @param  int  $zapisnikId  The record ID
     * @param  int  $kandidatId  The student ID to remove
     * @return bool True if the record was also deleted (because it became empty)
     */
    public function removeStudentFromZapisnik(int $zapisnikId, int $kandidatId): bool
    {
        ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        PolozeniIspiti::where([
            'zapisnik_id' => $zapisnikId,
            'kandidat_id' => $kandidatId,
        ])->delete();

        $zapisnikProvera = ZapisnikOPolaganju_Student::where([
            'zapisnik_id' => $zapisnikId,
        ])->get();

        if ($zapisnikProvera->count() == 0) {
            ZapisnikOPolaganjuIspita::destroy($zapisnikId);

            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Delete zapisnik
    // -------------------------------------------------------------------------

    /**
     * Delete an exam record and all its associated student and study program links.
     *
     * @param  int  $id  The record ID to delete
     */
    public function deleteZapisnikWithChildren(int $id): void
    {
        ZapisnikOPolaganju_Student::where(['zapisnik_id' => $id])->delete();
        ZapisnikOPolaganju_StudijskiProgram::where(['zapisnik_id' => $id])->delete();
        ZapisnikOPolaganjuIspita::destroy($id);
    }

    /**
     * Alias for deleteZapisnikWithChildren.
     *
     * @param  int  $id  The record ID to delete
     */
    public function deleteZapisnik(int $id): void
    {
        $this->deleteZapisnikWithChildren($id);
    }

    // -------------------------------------------------------------------------
    // Delete polozeni ispit
    // -------------------------------------------------------------------------

    public function deletePolozeniIspit(int $id, int $brisiZapisnik): void
    {
        $ispit = PolozeniIspiti::find($id);

        if ($brisiZapisnik == 1) {
            ZapisnikOPolaganju_Student::where([
                'zapisnik_id' => $ispit->zapisnik_id,
                'kandidat_id' => $ispit->kandidat_id,
            ])->delete();

            PolozeniIspiti::destroy($id);

            $zapisnikProvera = ZapisnikOPolaganju_Student::where([
                'zapisnik_id' => $ispit->zapisnik_id,
            ])->get();

            if ($zapisnikProvera->count() == 0) {
                ZapisnikOPolaganjuIspita::destroy($ispit->zapisnik_id);
            }
        } else {
            $ispit->indikatorAktivan = false;
            $ispit->ocenaPismeni = 0;
            $ispit->ocenaUsmeni = 0;
            $ispit->konacnaOcena = 0;
            $ispit->brojBodova = 0;
            $ispit->statusIspita = 0;
            $ispit->save();
        }
    }

    public function deletePrivremeniIspit(int $id): void
    {
        $polozenIspit = PolozeniIspiti::find($id);
        $polozenIspit->delete();
    }

    // -------------------------------------------------------------------------
    // Priznavanje ispita
    // -------------------------------------------------------------------------

    public function getPriznavanjeData(int $id): array
    {
        $kandidat = Kandidat::find($id);

        $predmetProgram = PredmetProgram::where([
            'tipStudija_id' => $kandidat->tipStudija_id,
            'studijskiProgram_id' => $kandidat->studijskiProgram_id,
        ])->orderBy('semestar')->get();

        return compact('kandidat', 'predmetProgram');
    }

    public function storePriznatiIspiti(int $kandidatId, ?array $predmetIds, array $konacneOcene): void
    {
        if ($predmetIds != null) {
            foreach ($predmetIds as $index => $ispit) {
                $polozenIspit = new PolozeniIspiti;
                $polozenIspit->kandidat_id = $kandidatId;
                $polozenIspit->predmet_id = $ispit;
                $polozenIspit->zapisnik_id = null;
                $polozenIspit->prijava_id = null;
                $polozenIspit->konacnaOcena = $konacneOcene[$index];
                $polozenIspit->statusIspita = 5;
                $polozenIspit->indikatorAktivan = true;
                $polozenIspit->save();
            }
        }
    }

    public function deletePriznatIspit(int $id): int
    {
        $polozenIspit = PolozeniIspiti::find($id);
        $kandidatId = $polozenIspit->kandidat_id;
        $polozenIspit->delete();

        return $kandidatId;
    }

    // -------------------------------------------------------------------------
    // Update zapisnik details
    // -------------------------------------------------------------------------

    public function updateZapisnikDetails(int $zapisnikId, array $data): void
    {
        $this->ispitResultService->updateZapisnikDetails($zapisnikId, $data);
    }

    // -------------------------------------------------------------------------
    // Arhiva
    // -------------------------------------------------------------------------

    public function getArhiviraniZapisnici(): array
    {
        return $this->ispitZapisnikService->getArhiviraniZapisnici();
    }

    public function arhivirajZapisnik(int $id): void
    {
        $this->ispitZapisnikService->arhivirajZapisnik($id);
    }

    public function arhivirajZapisnikeZaRok(int $rokId): void
    {
        $this->ispitZapisnikService->arhivirajZapisnikeZaRok($rokId);
    }
}
