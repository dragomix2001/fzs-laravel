<?php

namespace App\Services;

use App\DTOs\KandidatData;
use App\Jobs\MassEnrollmentJob;
use App\Kandidat;
use App\KandidatPrilozenaDokumenta;
use App\PrijavaIspita;
use App\Sport;
use App\SportskoAngazovanje;
use App\StudijskiProgram;
use App\UpisGodine;
use App\UspehSrednjaSkola;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Kandidat Service - Main orchestrator for student candidate operations.
 *
 * WARNING: This is a God Service (935 lines, 35 methods) - known technical debt.
 * See docs/ADR/001-god-services.md for refactoring strategy.
 *
 * Main Responsibilities:
 * - Kandidat CRUD operations (create, update, delete)
 * - Image and PDF file handling (upload, update, delete)
 * - High school grades management (UspehSrednjaSkola)
 * - Sports engagement management (SportskoAngazovanje)
 * - Documents management (KandidatPrilozenaDokumenta)
 * - Dropdown data retrieval for forms
 * - Cache management for active studijski programs
 * - Mass enrollment dispatch (Queue jobs)
 *
 * @see KandidatController
 * @see StoreKandidatRequest
 * @see UpdateKandidatRequest
 * @see Kandidat
 */
class KandidatService
{
    public function __construct(
        protected UpisService $upisService,
        protected FileStorageService $fileStorageService,
        protected GradeManagementService $gradeManagementService,
        protected DropdownDataService $dropdownDataService,
        protected SportsManagementService $sportsManagementService,
        protected DocumentManagementService $documentManagementService
    ) {}

    /**
     * Get all candidates with optional filtering.
     *
     * @param  array  $filters  Associative array of filters (tipStudija_id, statusUpisa_id, studijskiProgram_id)
     * @return Collection Collection of candidates
     */
    public function getAll(array $filters = [])
    {
        $query = Kandidat::query();

        if (! empty($filters['tipStudija_id'])) {
            $query->where('tipStudija_id', $filters['tipStudija_id']);
        }

        if (! empty($filters['statusUpisa_id'])) {
            $query->where('statusUpisa_id', $filters['statusUpisa_id']);
        }

        if (! empty($filters['studijskiProgram_id'])) {
            $query->where('studijskiProgram_id', $filters['studijskiProgram_id']);
        }

        return $query->get();
    }

    /**
     * Get candidate by ID.
     *
     * @param  int  $id  Primary key of the candidate
     * @return Kandidat|null The candidate instance or null if not found
     */
    public function findById(int $id): ?Kandidat
    {
        return Kandidat::find($id);
    }

    /**
     * Get active study program ID for osnovne studije.
     *
     * Uses cache for 1 hour to reduce database queries.
     *
     * @return int|null The active program ID or null if none active
     */
    public function getActiveStudijskiProgramOsnovne(): ?int
    {
        return Cache::remember('active_studijski_program_osnovne', 3600, function () {
            return StudijskiProgram::where(['tipStudija_id' => 1, 'indikatorAktivan' => 1])->value('id');
        });
    }

    /**
     * Get all study programs for a specific study type.
     *
     * @param  int  $tipStudijaId  ID of the study type (e.g., 1 for Osnovne, 2 for Master)
     * @return Collection Collection of study programs
     */
    public function getStudijskiProgrami(int $tipStudijaId): mixed
    {
        return $this->dropdownDataService->getStudijskiProgrami($tipStudijaId);
    }

    /**
     * Get all dropdown and metadata needed for candidate creation forms.
     *
     * Retrieves locations, religions, schools, grades, sports, and available study programs.
     *
     * @return array Associative array of collections for form select inputs
     */
    public function getDropdownData(): array
    {
        return $this->dropdownDataService->getDropdownData();
    }

    /**
     * Get dropdown data specifically for the master student creation form.
     *
     * Focuses on active master programs and specific document requirements.
     *
     * @return array Associative array of collections for master student select inputs
     */
    public function getDropdownDataMaster(): array
    {
        return $this->dropdownDataService->getDropdownDataMaster();
    }

    /**
     * Store kandidat page 1 (basic information).
     *
     * Creates a new Kandidat record with basic personal information, study program selection,
     * and optional image upload. This is the first step of the 2-step application process.
     *
     * NOTE: This method accepts both KandidatData DTO and raw Request object.
     * Legacy technical debt - see docs/ADR/002-request-coupling.md
     *
     * @param  KandidatData  $data  Validated DTO containing personal and program info
     * @param  Request  $request  Raw HTTP request (legacy - should be migrated to DTO)
     *
     * @throws \Exception If image upload fails
     * @return Kandidat Created kandidat instance
     */
    public function storeKandidatPage1(KandidatData $data, Request $request): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        } else {
            $kandidat->uplata = 0;
        }

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        if (date_create_from_format('d.m.Y.', $request->DatumRodjenja)) {
            $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->krsnaSlava_id = $request->KrsnaSlava;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;
        $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;
        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 1;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->save();

        if ($request->hasFile('imageUpload')) {
            $this->fileStorageService->uploadImageForKandidat($kandidat, $request->file('imageUpload'));
        }

        return $kandidat;
    }

    /**
     * Store kandidat page 2 (grades, sports, documents, and scores).
     *
     * Completes the candidate profile by saving high school grades, sports engagement,
     * submitted documents, and calculating the total score based on academic success.
     *
     * NOTE: This method accepts raw Request object and relies on $request->insertedId.
     * Legacy technical debt - see docs/ADR/002-request-coupling.md
     *
     * @param  Request  $request  Raw HTTP request containing all secondary data
     *
     * @throws ModelNotFoundException If the candidate from page 1 is not found
     * @return Kandidat Updated kandidat instance
     */
    public function storeKandidatPage2(Request $request): Kandidat
    {
        $kandidat = Kandidat::find($request->insertedId);

        // Store high school grades using GradeManagementService
        $this->gradeManagementService->createGradesForKandidat($request->insertedId, [
            ['razred' => 1, 'uspeh' => $request->prviRazred, 'ocena' => $request->SrednjaOcena1],
            ['razred' => 2, 'uspeh' => $request->drugiRazred, 'ocena' => $request->SrednjaOcena2],
            ['razred' => 3, 'uspeh' => $request->treciRazred, 'ocena' => $request->SrednjaOcena3],
            ['razred' => 4, 'uspeh' => $request->cetvrtiRazred, 'ocena' => $request->SrednjaOcena4],
        ]);

        $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
        $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

        if ($request->sport1 != 0) {
            $this->sportsManagementService->createSportForKandidat($request->insertedId, [
                'sport' => $request->sport1,
                'klub' => $request->klub1,
                'uzrast' => $request->uzrast1,
                'godine' => $request->godine1,
            ]);
        }

        if ($request->sport2 != 0) {
            $this->sportsManagementService->createSportForKandidat($request->insertedId, [
                'sport' => $request->sport2,
                'klub' => $request->klub2,
                'uzrast' => $request->uzrast2,
                'godine' => $request->godine2,
            ]);
        }

        if ($request->sport3 != 0) {
            $this->sportsManagementService->createSportForKandidat($request->insertedId, [
                'sport' => $request->sport3,
                'klub' => $request->klub3,
                'uzrast' => $request->uzrast3,
                'godine' => $request->godine3,
            ]);
        }

        $kandidat->visina = str_replace(',', '.', $request->VisinaKandidata);
        $kandidat->telesnaTezina = str_replace(',', '.', $request->TelesnaTezinaKandidata);

        $this->documentManagementService->attachDocumentsForKandidat(
            $request->insertedId,
            $request->get('dokumentiPrva', []),
            $request->get('dokumentiDruga', [])
        );

        $kandidat->brojBodovaTest = $request->BrojBodovaTest;
        $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
        $kandidat->ukupniBrojBodova = $request->ukupniBrojBodova;
        $kandidat->upisniRok = $request->UpisniRok;

        $kandidat->save();

        return $kandidat;
    }

    /**
     * Update existing kandidat information (combined page 1 & 2).
     *
     * Updates candidate personal data, study program, high school success,
     * and handles image/PDF file updates.
     *
     * NOTE: This method combines both basic and detailed info updates.
     * Legacy technical debt - see docs/ADR/002-request-coupling.md
     *
     * @param  int  $id  Candidate ID to update
     * @param  KandidatData  $data  Validated DTO containing main fields
     * @param  Request  $request  Raw HTTP request for supplemental fields
     *
     * @throws ModelNotFoundException If candidate or related success records don't exist
     * @return Kandidat Updated kandidat instance
     */
    public function updateKandidat(int $id, KandidatData $data, Request $request): Kandidat
    {
        $kandidat = Kandidat::find($id);

        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        }

        if ($request->hasFile('imageUpload')) {
            $this->fileStorageService->replaceImageForKandidat($kandidat, $request->file('imageUpload'));
        }

        if ($request->hasFile('pdfUpload')) {
            $this->fileStorageService->replacePdfForKandidat($kandidat, $request->file('pdfUpload'));
        }

        $kandidat->datumRodjenja = date_create_from_format('d.m.Y.', $request->DatumRodjenja);

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->krsnaSlava_id = $request->KrsnaSlava;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;
        $kandidat->imePrezimeJednogRoditelja = $request->ImePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $request->KontaktTelefonRoditelja;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $data->tipStudijaId;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;
        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $kandidat->statusUpisa_id = $request->statusUpisa_id;
        $kandidat->datumStatusa = empty($request->datumStatusa) ?
            Carbon::now() :
            date_create_from_format('d.m.Y.', $request->datumStatusa);

        // Update high school grades using GradeManagementService
        $this->gradeManagementService->updateGradesForKandidat($id, [
            ['razred' => 1, 'uspeh' => $request->prviRazred, 'ocena' => $request->SrednjaOcena1],
            ['razred' => 2, 'uspeh' => $request->drugiRazred, 'ocena' => $request->SrednjaOcena2],
            ['razred' => 3, 'uspeh' => $request->treciRazred, 'ocena' => $request->SrednjaOcena3],
            ['razred' => 4, 'uspeh' => $request->cetvrtiRazred, 'ocena' => $request->SrednjaOcena4],
        ]);

        $kandidat->opstiUspehSrednjaSkola_id = $request->OpstiUspehSrednjaSkola;
        $kandidat->srednjaOcenaSrednjaSkola = $request->SrednjaOcenaSrednjaSkola;

        $kandidat->visina = str_replace(',', '.', $request->VisinaKandidata);
        $kandidat->telesnaTezina = str_replace(',', '.', $request->TelesnaTezinaKandidata);

        $this->documentManagementService->deleteDocumentsForKandidat($id);
        $this->documentManagementService->attachDocumentsForKandidat(
            $id,
            $request->get('dokumentiPrva', []),
            $request->get('dokumentiDruga', [])
        );

        $kandidat->brojBodovaTest = $request->BrojBodovaTest;
        $kandidat->brojBodovaSkola = $request->BrojBodovaSkola;
        $kandidat->ukupniBrojBodova = $request->ukupniBrojBodova;
        $kandidat->upisniRok = $request->UpisniRok;
        $kandidat->indikatorAktivan = $request->IndikatorAktivan;
        $kandidat->brojIndeksa = $data->brojIndeksa;

        $kandidat->save();

        return $kandidat;
    }

    /**
     * Store new master student candidate.
     *
     * Creates a candidate record for master studies, handles automatic registration,
     * submitted documents, and optional image upload.
     *
     * NOTE: This method accepts raw Request object and lacks DTO validation.
     * Legacy technical debt - see docs/ADR/002-request-coupling.md
     *
     * @param  Request  $request  Raw HTTP request with master student data
     *
     * @throws \Exception If image upload or registration fails
     * @return Kandidat Created master candidate instance
     */
    public function storeMasterKandidat(Request $request): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $request->ImeKandidata;
        $kandidat->prezimeKandidata = $request->PrezimeKandidata;
        $kandidat->jmbg = $request->JMBG;

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        } else {
            $kandidat->uplata = 0;
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 2;
        $kandidat->studijskiProgram_id = $request->StudijskiProgram;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->prosecnaOcena = str_replace(',', '.', $request->ProsecnaOcena);
        $kandidat->upisniRok = $request->UpisniRok;
        $kandidat->godinaStudija_id = 1;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $saved = $kandidat->save();

        if ($request->hasFile('imageUpload')) {
            $this->fileStorageService->uploadImageForKandidat($kandidat, $request->file('imageUpload'));
        }

        $insertedId = $kandidat->id;

        if ($saved) {
            $this->upisService->registrujKandidata($insertedId);

            $this->documentManagementService->deleteDocumentsForKandidat($insertedId);
            $this->documentManagementService->attachDocumentsForKandidat(
                $insertedId,
                $request->get('dokumentaMaster', []),
                []
            );
        }

        return $kandidat;
    }

    /**
     * Update existing master student candidate.
     *
     * Updates master student personal data, study program, documents,
     * and handles image file updates.
     *
     * NOTE: This method accepts raw Request object and lacks DTO validation.
     * Legacy technical debt - see docs/ADR/002-request-coupling.md
     *
     * @param  int  $id  Master candidate ID to update
     * @param  Request  $request  Raw HTTP request with updated data
     *
     * @throws ModelNotFoundException If master candidate is not found
     * @return Kandidat Updated master candidate instance
     */
    public function updateMasterKandidat(int $id, Request $request): Kandidat
    {
        $kandidat = Kandidat::find($id);

        $kandidat->imeKandidata = $request->ImeKandidata;
        $kandidat->prezimeKandidata = $request->PrezimeKandidata;
        $kandidat->jmbg = $request->JMBG;

        if (isset($request->uplata)) {
            $kandidat->uplata = 1;
        }

        $kandidat->statusUpisa_id = $request->statusUpisa_id;
        $kandidat->datumStatusa = empty($request->datumStatusa) ?
            Carbon::now() :
            date_create_from_format('d.m.Y.', $request->datumStatusa);

        if ($request->hasFile('imageUpload')) {
            $this->fileStorageService->replaceImageForKandidat($kandidat, $request->file('imageUpload'));
        }

        $kandidat->mestoRodjenja = $request->mestoRodjenja;
        $kandidat->kontaktTelefon = $request->KontaktTelefon;
        $kandidat->adresaStanovanja = $request->AdresaStanovanja;
        $kandidat->email = $request->Email;

        $kandidat->srednjeSkoleFakulteti = $request->NazivSkoleFakulteta;
        $kandidat->mestoZavrseneSkoleFakulteta = $request->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $request->SmerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $request->TipStudija;
        $kandidat->studijskiProgram_id = $request->StudijskiProgram;
        $kandidat->skolskaGodinaUpisa_id = $request->SkolskeGodineUpisa;

        $kandidat->prosecnaOcena = str_replace(',', '.', $request->ProsecnaOcena);
        $kandidat->upisniRok = $request->UpisniRok;

        $kandidat->brojIndeksa = $request->brojIndeksa;

        $kandidat->drzavaZavrseneSkole = $request->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $request->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $request->drzavaRodjenja;

        $saved = $kandidat->save();

        $this->documentManagementService->deleteDocumentsForKandidat($id);
        $this->documentManagementService->attachDocumentsForKandidat(
            $id,
            $request->get('dokumentaMaster', []),
            []
        );

        return $kandidat;
    }

    /**
     * Delete candidate and all related records (transnational).
     *
     * Removes candidate, documents, enrollment history, sports records,
     * exam registrations, and the candidate image from storage.
     *
     * @param  int  $id  Candidate ID to delete
     *
     * @throws \Exception If transaction fails
     * @return bool True if deletion succeeded
     */
    public function deleteKandidat(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $kandidat = Kandidat::find($id);
            $this->documentManagementService->deleteDocumentsForKandidat($id);
            UpisGodine::where(['kandidat_id' => $id])->delete();
            $this->sportsManagementService->deleteSportsForKandidat($id);
            PrijavaIspita::where(['kandidat_id' => $id])->delete();

            $this->fileStorageService->deleteImageForKandidat($kandidat);
            $this->gradeManagementService->deleteGradesForKandidat($id);

            return (bool) Kandidat::destroy($id);
        });
    }

    /**
     * Delete master kandidat (simple delete).
     */
    public function deleteMasterKandidat(int $id): bool
    {
        return (bool) Kandidat::destroy($id);
    }

    /**
     * Store sport for kandidat.
     */
    public function storeSport(int $kandidatId, array $data): SportskoAngazovanje
    {
        return $this->sportsManagementService->createSportForKandidat($kandidatId, $data);
    }

    /**
     * Masovna uplata za osnovne studije.
     */
    public function masovnaUplata(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();

            UpisGodine::uplatiGodinu($kandidatId, 1);
        }
    }

    /**
     * Masovni upis za osnovne studije.
     */
    public function masovniUpis(array $kandidatIds): bool
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $this->upisService->registrujKandidata($kandidatId);

            $returnValue = $this->upisService->upisiGodinu($kandidatId, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);

            if ($returnValue) {
                $kandidat->statusUpisa_id = 1;
                $kandidat->datumStatusa = Carbon::now();
                $kandidat->save();
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Masovna uplata za master studije.
     */
    public function masovnaUplataMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->uplata = 1;
            $kandidat->save();
        }
    }

    /**
     * Masovni upis za master studije.
     */
    public function masovniUpisMaster(array $kandidatIds): void
    {
        $kandidatiMap = Kandidat::whereIn('id', $kandidatIds)->get()->keyBy('id');

        foreach ($kandidatIds as $kandidatId) {
            $kandidat = $kandidatiMap->get($kandidatId);
            $kandidat->statusUpisa_id = 1;
            $kandidat->datumStatusa = Carbon::now();
            $kandidat->save();

            $this->upisService->generisiBrojIndeksa($kandidatId);
        }
    }

    /**
     * Dispatch mass enrollment for students (async queue).
     *
     * Handles processing large sets of candidates using background jobs.
     *
     * @param  array  $kandidatIds  List of candidate IDs to enroll
     * @return array Status message indicating background processing started
     */
    public function masovniUpisAsync(array $kandidatIds): array
    {
        MassEnrollmentJob::dispatch($kandidatIds);

        return ['status' => 'queued', 'count' => count($kandidatIds)];
    }

    /**
     * Upis kandidata (enrollment logic).
     */
    public function upisKandidata(int $id): array
    {
        $kandidat = Kandidat::find($id);
        $this->upisService->registrujKandidata($id);

        if ($kandidat->tipStudija_id == 1) {
            $check = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $check) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
        } elseif ($kandidat->tipStudija_id == 2) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        } elseif ($kandidat->tipStudija_id == 3) {
            $checkTwo = $this->upisService->upisiGodinu($id, $kandidat->godinaStudija_id, $kandidat->skolskaGodinaUpisa_id);
            if (! $checkTwo) {
                return ['success' => false, 'tipStudija_id' => $kandidat->tipStudija_id];
            }
            $this->upisService->generisiBrojIndeksa($kandidat->id);
        }

        $kandidat->statusUpisa_id = 1;
        $kandidat->datumStatusa = Carbon::now();
        $saved = $kandidat->save();

        return [
            'success' => $saved,
            'tipStudija_id' => $kandidat->tipStudija_id,
        ];
    }

    /**
     * Registracija kandidata.
     */
    public function registracijaKandidata(int $id): void
    {
        $this->upisService->registrujKandidata($id);
    }

    /**
     * Get dropdown data for edit view (osnovne).
     */
    public function getEditDropdownData(int $id): array
    {
        return $this->dropdownDataService->getEditDropdownData($id);
    }

    /**
     * Get dropdown data for edit master view.
     */
    public function getEditDropdownDataMaster(int $id): array
    {
        return $this->dropdownDataService->getEditDropdownDataMaster($id);
    }

    public function storeKandidat(KandidatData $data): Kandidat
    {
        return DB::transaction(function () use ($data) {
            return Kandidat::create($data->toArray());
        });
    }

    /**
     * Get kandidat by ID (alias)
     */
    public function create(array $data): Kandidat
    {
        return DB::transaction(function () use ($data) {
            $kandidat = Kandidat::create($data);

            return $kandidat;
        });
    }

    /**
     * Update kandidat by array data (basic, legacy method)
     */
    public function update(int $id, array $data): ?Kandidat
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return null;
        }

        $kandidat->update($data);

        return $kandidat;
    }

    /**
     * Delete kandidat by ID (basic, legacy method)
     */
    public function delete(int $id): bool
    {
        $kandidat = $this->findById($id);

        if (! $kandidat) {
            return false;
        }

        return $kandidat->delete();
    }

    /**
     * Get kandidati by status
     */
    public function getByStatus(int $statusId): mixed
    {
        return Kandidat::where('statusUpisa_id', $statusId)->get();
    }

    /**
     * Get kandidati by studijski program
     */
    public function getByStudijskiProgram(int $programId): mixed
    {
        return Kandidat::where('studijskiProgram_id', $programId)->get();
    }

    /**
     * Search kandidati
     */
    public function search(string $query): mixed
    {
        return Kandidat::where('imeKandidata', 'like', "%{$query}%")
            ->orWhere('prezimeKandidata', 'like', "%{$query}%")
            ->orWhere('brojIndeksa', 'like', "%{$query}%")
            ->get();
    }
}
