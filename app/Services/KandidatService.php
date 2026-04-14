<?php

namespace App\Services;

use App\DTOs\KandidatData;
use App\DTOs\KandidatPage1Data;
use App\DTOs\KandidatPage2Data;
use App\DTOs\KandidatUpdateData;
use App\DTOs\MasterKandidatData;
use App\Models\Kandidat;
use App\Models\PrijavaIspita;
use App\Models\PrilozenaDokumenta;
use App\Models\Sport;
use App\Models\SportskoAngazovanje;
use App\Models\StudijskiProgram;
use App\Models\UpisGodine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Kandidat Service - Main orchestrator for student candidate operations.
 *
 * Responsibilities:
 * - Kandidat CRUD operations (create, update, delete)
 * - Image and PDF file handling (upload, update, delete)
 * - High school grades management (UspehSrednjaSkola)
 * - Sports engagement management (SportskoAngazovanje)
 * - Documents management (KandidatPrilozenaDokumenta)
 * - Dropdown data retrieval for forms
 * - Cache management for active studijski programs
 *
 * @see KandidatController
 * @see KandidatEnrollmentService  For enrollment/mass operations (extracted)
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

    private function asCarbon(?\DateTimeInterface $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::instance(\DateTime::createFromInterface($value));
    }

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
     * Get candidate by ID or fail.
     *
     * @param  int  $id  Primary key of the candidate
     * @return Kandidat The candidate instance
     */
    public function findByIdOrFail(int $id): Kandidat
    {
        return Kandidat::findOrFail($id);
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
        return $this->getActiveStudijskiProgramId(1);
    }

    /**
     * Get active study program ID for the given study type.
     *
     * @param  int  $tipStudijaId  Study type ID
     * @return int|null The active program ID or null if none active
     */
    public function getActiveStudijskiProgramId(int $tipStudijaId): ?int
    {
        return Cache::remember("active_studijski_program_{$tipStudijaId}", 3600, function () use ($tipStudijaId) {
            return StudijskiProgram::where(['tipStudija_id' => $tipStudijaId, 'indikatorAktivan' => 1])->value('id');
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
     * Get active study programs for a specific study type.
     *
     * @param  int  $tipStudijaId  Study type ID
     * @return Collection Collection of active study programs
     */
    public function getAktivniStudijskiProgrami(int $tipStudijaId): mixed
    {
        return StudijskiProgram::where([
            'tipStudija_id' => $tipStudijaId,
            'indikatorAktivan' => 1,
        ])->get();
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
     * Get all reference data needed for kandidat page 2 after page 1 save.
     *
     * @param  int  $insertedId  Newly created kandidat ID
     * @return array Associative array of view data for page 2
     */
    public function getPageTwoFormData(int $insertedId): array
    {
        return array_merge($this->getDropdownData(), [
            'insertedId' => $insertedId,
            'sport' => Sport::all(),
            'dokumentiPrvaGodina' => PrilozenaDokumenta::where('skolskaGodina_id', '1')->get(),
            'dokumentiOstaleGodine' => PrilozenaDokumenta::where('skolskaGodina_id', '2')->get(),
        ]);
    }

    /**
     * Get all data needed for the sports engagement page.
     *
     * @param  int  $kandidatId  Kandidat ID
     * @return array Associative array of kandidat, sport options, and existing engagements
     */
    public function getSportPageData(int $kandidatId): array
    {
        return [
            'sport' => Sport::all(),
            'kandidat' => $this->findByIdOrFail($kandidatId),
            'sportskoAngazovanje' => $this->sportsManagementService->getSportsForKandidat($kandidatId),
            'id' => $kandidatId,
        ];
    }

    /**
     * Store kandidat page 1 (basic information).
     *
     * Creates a new Kandidat record with basic personal information, study program selection,
     * and optional image upload. This is the first step of the 2-step application process.
     *
     * @param  KandidatPage1Data  $data  Typed page 1 candidate input
     *
     * @throws \Exception If image upload fails
     * @return Kandidat Created kandidat instance
     */
    public function storeKandidatPage1(KandidatPage1Data $data): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        $kandidat->uplata = (bool) $data->uplata;

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        if ($data->datumRodjenja !== null) {
            $kandidat->datumRodjenja = $this->asCarbon($data->datumRodjenja);
        }

        $kandidat->mestoRodjenja = $data->mestoRodjenja;
        $kandidat->krsnaSlava_id = $data->krsnaSlavaId;
        $kandidat->kontaktTelefon = $data->kontaktTelefon;
        $kandidat->adresaStanovanja = $data->adresaStanovanja;
        $kandidat->email = $data->email;
        $kandidat->imePrezimeJednogRoditelja = $data->imePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $data->kontaktTelefonRoditelja;
        $kandidat->srednjeSkoleFakulteti = $data->srednjeSkoleFakulteti;
        $kandidat->mestoZavrseneSkoleFakulteta = $data->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $data->smerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 1;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $data->skolskaGodinaUpisaId;

        $kandidat->drzavaZavrseneSkole = $data->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $data->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $data->drzavaRodjenja;

        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->save();

        if ($data->imageUpload !== null) {
            $this->fileStorageService->uploadImageForKandidat($kandidat, $data->imageUpload);
        }

        return $kandidat;
    }

    /**
     * Store kandidat page 2 (grades, sports, documents, and scores).
     *
     * Completes the candidate profile by saving high school grades, sports engagement,
     * submitted documents, and calculating the total score based on academic success.
     *
     * @param  KandidatPage2Data  $data  Typed page 2 candidate input
     *
     * @throws ModelNotFoundException If the candidate from page 1 is not found
     * @return Kandidat Updated kandidat instance
     */
    public function storeKandidatPage2(KandidatPage2Data $data): Kandidat
    {
        $kandidat = Kandidat::findOrFail($data->kandidatId);

        $this->gradeManagementService->createGradesForKandidat($data->kandidatId, $data->grades);

        $kandidat->opstiUspehSrednjaSkola_id = $data->opstiUspehSrednjaSkolaId;
        $kandidat->srednjaOcenaSrednjaSkola = $data->srednjaOcenaSrednjaSkola;

        foreach ($data->sports as $sportData) {
            $this->sportsManagementService->createSportForKandidat($data->kandidatId, $sportData);
        }

        $kandidat->visina = $data->visina;
        $kandidat->telesnaTezina = $data->telesnaTezina;

        $this->documentManagementService->attachDocumentsForKandidat(
            $data->kandidatId,
            $data->dokumentiPrva,
            $data->dokumentiDruga
        );

        $kandidat->brojBodovaTest = $data->brojBodovaTest;
        $kandidat->brojBodovaSkola = $data->brojBodovaSkola;
        $kandidat->ukupniBrojBodova = $data->ukupniBrojBodova;
        $kandidat->upisniRok = $data->upisniRok;

        $kandidat->save();

        return $kandidat;
    }

    /**
     * Update existing kandidat information (combined page 1 & 2).
     *
     * Updates candidate personal data, study program, high school success,
     * and handles image/PDF file updates.
     *
     * @param  int  $id  Candidate ID to update
     * @param  KandidatUpdateData  $data  Typed update input
     *
     * @throws ModelNotFoundException If candidate or related success records don't exist
     * @return Kandidat Updated kandidat instance
     */
    public function updateKandidat(int $id, KandidatUpdateData $data): Kandidat
    {
        $kandidat = Kandidat::findOrFail($id);

        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if ($data->uplata) {
            $kandidat->uplata = true;
        }

        if ($data->imageUpload !== null) {
            $this->fileStorageService->replaceImageForKandidat($kandidat, $data->imageUpload);
        }

        if ($data->pdfUpload !== null) {
            $this->fileStorageService->replacePdfForKandidat($kandidat, $data->pdfUpload);
        }

        $kandidat->datumRodjenja = $this->asCarbon($data->datumRodjenja);

        $kandidat->mestoRodjenja = $data->mestoRodjenja;
        $kandidat->krsnaSlava_id = $data->krsnaSlavaId;
        $kandidat->kontaktTelefon = $data->kontaktTelefon;
        $kandidat->adresaStanovanja = $data->adresaStanovanja;
        $kandidat->email = $data->email;
        $kandidat->imePrezimeJednogRoditelja = $data->imePrezimeJednogRoditelja;
        $kandidat->kontaktTelefonRoditelja = $data->kontaktTelefonRoditelja;

        $kandidat->srednjeSkoleFakulteti = $data->srednjeSkoleFakulteti;
        $kandidat->mestoZavrseneSkoleFakulteta = $data->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $data->smerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $data->tipStudijaId;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $data->skolskaGodinaUpisaId;
        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->drzavaZavrseneSkole = $data->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $data->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $data->drzavaRodjenja;

        $kandidat->statusUpisa_id = $data->statusUpisaId;
        $kandidat->datumStatusa = $this->asCarbon($data->datumStatusa) ?? Carbon::now();

        $this->gradeManagementService->updateGradesForKandidat($id, $data->grades);

        $kandidat->opstiUspehSrednjaSkola_id = $data->opstiUspehSrednjaSkolaId;
        $kandidat->srednjaOcenaSrednjaSkola = $data->srednjaOcenaSrednjaSkola;

        $kandidat->visina = $data->visina;
        $kandidat->telesnaTezina = $data->telesnaTezina;

        $this->documentManagementService->deleteDocumentsForKandidat($id);
        $this->documentManagementService->attachDocumentsForKandidat(
            $id,
            $data->dokumentiPrva,
            $data->dokumentiDruga
        );

        $kandidat->brojBodovaTest = $data->brojBodovaTest;
        $kandidat->brojBodovaSkola = $data->brojBodovaSkola;
        $kandidat->ukupniBrojBodova = $data->ukupniBrojBodova;
        $kandidat->upisniRok = $data->upisniRok;
        $kandidat->indikatorAktivan = $data->indikatorAktivan;
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
     * @param  MasterKandidatData  $data  Typed master candidate input
     *
     * @throws \Exception If image upload or registration fails
     * @return Kandidat Created master candidate instance
     */
    public function storeMasterKandidat(MasterKandidatData $data): Kandidat
    {
        $kandidat = new Kandidat;
        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        $kandidat->statusUpisa_id = 3;
        $kandidat->datumStatusa = Carbon::now();

        $kandidat->uplata = (bool) $data->uplata;

        $kandidat->mestoRodjenja = $data->mestoRodjenja;
        $kandidat->kontaktTelefon = $data->kontaktTelefon;
        $kandidat->adresaStanovanja = $data->adresaStanovanja;
        $kandidat->email = $data->email;

        $kandidat->srednjeSkoleFakulteti = $data->srednjeSkoleFakulteti;
        $kandidat->mestoZavrseneSkoleFakulteta = $data->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $data->smerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = 2;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $data->skolskaGodinaUpisaId;

        $kandidat->prosecnaOcena = $data->prosecnaOcena;
        $kandidat->upisniRok = $data->upisniRok;
        $kandidat->godinaStudija_id = $data->godinaStudijaId;

        $kandidat->drzavaZavrseneSkole = $data->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $data->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $data->drzavaRodjenja;

        $saved = $kandidat->save();

        if ($data->imageUpload !== null) {
            $this->fileStorageService->uploadImageForKandidat($kandidat, $data->imageUpload);
        }

        $insertedId = $kandidat->id;

        if ($saved) {
            $this->upisService->registrujKandidata($insertedId);

            $this->documentManagementService->deleteDocumentsForKandidat($insertedId);
            $this->documentManagementService->attachDocumentsForKandidat(
                $insertedId,
                $data->dokumentaMaster,
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
     * @param  int  $id  Master candidate ID to update
     * @param  MasterKandidatData  $data  Typed updated master candidate input
     *
     * @throws ModelNotFoundException If master candidate is not found
     * @return Kandidat Updated master candidate instance
     */
    public function updateMasterKandidat(int $id, MasterKandidatData $data): Kandidat
    {
        $kandidat = Kandidat::findOrFail($id);

        $kandidat->imeKandidata = $data->ime;
        $kandidat->prezimeKandidata = $data->prezime;
        $kandidat->jmbg = $data->JMBG;

        if ($data->uplata) {
            $kandidat->uplata = true;
        }

        $kandidat->statusUpisa_id = $data->statusUpisaId;
        $kandidat->datumStatusa = $this->asCarbon($data->datumStatusa) ?? Carbon::now();

        if ($data->imageUpload !== null) {
            $this->fileStorageService->replaceImageForKandidat($kandidat, $data->imageUpload);
        }

        $kandidat->mestoRodjenja = $data->mestoRodjenja;
        $kandidat->kontaktTelefon = $data->kontaktTelefon;
        $kandidat->adresaStanovanja = $data->adresaStanovanja;
        $kandidat->email = $data->email;

        $kandidat->srednjeSkoleFakulteti = $data->srednjeSkoleFakulteti;
        $kandidat->mestoZavrseneSkoleFakulteta = $data->mestoZavrseneSkoleFakulteta;
        $kandidat->smerZavrseneSkoleFakulteta = $data->smerZavrseneSkoleFakulteta;

        $kandidat->tipStudija_id = $data->tipStudijaId;
        $kandidat->studijskiProgram_id = $data->studijskiProgramId;
        $kandidat->skolskaGodinaUpisa_id = $data->skolskaGodinaUpisaId;

        $kandidat->prosecnaOcena = $data->prosecnaOcena;
        $kandidat->upisniRok = $data->upisniRok;

        $kandidat->brojIndeksa = $data->brojIndeksa;

        $kandidat->drzavaZavrseneSkole = $data->drzavaZavrseneSkole;
        $kandidat->godinaZavrsetkaSkole = $data->godinaZavrsetkaSkole;
        $kandidat->drzavaRodjenja = $data->drzavaRodjenja;

        $kandidat->save();

        $this->documentManagementService->deleteDocumentsForKandidat($id);
        $this->documentManagementService->attachDocumentsForKandidat(
            $id,
            $data->dokumentaMaster,
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
