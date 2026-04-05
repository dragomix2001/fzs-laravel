<?php

declare(strict_types=1);

namespace App\Services;

use App\GodinaStudija;
use App\KandidatPrilozenaDokumenta;
use App\KrsnaSlava;
use App\Opstina;
use App\OpstiUspeh;
use App\PrilozenaDokumenta;
use App\SkolskaGodUpisa;
use App\Sport;
use App\SportskoAngazovanje;
use App\StatusGodine;
use App\StatusStudiranja;
use App\StudijskiProgram;
use App\TipStudija;
use App\UspehSrednjaSkola;
use Illuminate\Database\Eloquent\Collection;

/**
 * Dropdown Data Service - Centralized dropdown/form data provider.
 *
 * This is a helper service extracted from KandidatService to centralize all dropdown
 * data retrieval for forms. Improves testability and reduces KandidatService complexity.
 *
 * Responsibilities:
 * - Provide dropdown data for candidate creation forms (osnovne/master)
 * - Provide dropdown data for candidate edit forms (with kandidat-specific data)
 * - Retrieve study programs filtered by type
 *
 * @see KandidatService (original implementation)
 * @see KandidatController (form rendering)
 */
class DropdownDataService
{
    public function __construct(
        protected GradeManagementService $gradeManagementService
    ) {}

    /**
     * Get study programs filtered by study type.
     *
     * @param  int  $tipStudijaId  Study type ID (1 = osnovne, 2 = master)
     * @return Collection Study programs for the given type
     */
    public function getStudijskiProgrami(int $tipStudijaId): mixed
    {
        return StudijskiProgram::where('tipStudija_id', $tipStudijaId)->get();
    }

    /**
     * Get all dropdown and metadata needed for candidate creation forms (osnovne studije).
     *
     * Retrieves locations, religions, schools, grades, sports, and available study programs
     * for basic (undergraduate) student registration forms.
     *
     * @return array Associative array of collections for form select inputs
     */
    public function getDropdownData(): array
    {
        return [
            'mestoRodjenja' => Opstina::all(),
            'krsnaSlava' => KrsnaSlava::all(),
            'mestoZavrseneSkoleFakulteta' => Opstina::all(),
            'opstiUspehSrednjaSkola' => OpstiUspeh::all(),
            'uspehSrednjaSkola' => UspehSrednjaSkola::all(),
            'sportskoAngazovanje' => SportskoAngazovanje::all(),
            'prilozeniDokumentPrvaGodina' => PrilozenaDokumenta::all(),
            'statusaUpisaKandidata' => StatusStudiranja::all(),
            'studijskiProgram' => StudijskiProgram::where('tipStudija_id', '1')->get(),
            'tipStudija' => TipStudija::all(),
            'godinaStudija' => GodinaStudija::all(),
            'skolskeGodineUpisa' => SkolskaGodUpisa::all(),
        ];
    }

    /**
     * Get dropdown data specifically for the master student creation form.
     *
     * Focuses on active master programs and specific document requirements.
     * Filters study programs to only active master programs (tipStudija_id = 2, indikatorAktivan = 1).
     *
     * @return array Associative array of collections for master student select inputs
     */
    public function getDropdownDataMaster(): array
    {
        return [
            'mestoRodjenja' => Opstina::all(),
            'krsnaSlava' => KrsnaSlava::all(),
            'opstiUspehSrednjaSkola' => OpstiUspeh::all(),
            'uspehSrednjaSkola' => UspehSrednjaSkola::all(),
            'sportskoAngazovanje' => SportskoAngazovanje::all(),
            'prilozeniDokumentPrvaGodina' => PrilozenaDokumenta::all(),
            'statusaUpisaKandidata' => StatusStudiranja::all(),
            'studijskiProgram' => StudijskiProgram::where(['tipStudija_id' => 2, 'indikatorAktivan' => 1])->get(),
            'tipStudija' => TipStudija::all(),
            'godinaStudija' => GodinaStudija::all(),
            'skolskeGodineUpisa' => SkolskaGodUpisa::all(),
            'dokumentaMaster' => PrilozenaDokumenta::where('skolskaGodina_id', '3')->get(),
        ];
    }

    /**
     * Get dropdown data for candidate edit form (osnovne studije).
     *
     * Merges base dropdown data with kandidat-specific data:
     * - High school grades (via GradeManagementService)
     * - Attached sports engagements
     * - Attached documents
     * - Edit-specific study program filters (only active programs)
     *
     * @param  int  $id  Kandidat ID
     * @return array Complete dropdown data array for edit form
     */
    public function getEditDropdownData(int $id): array
    {
        $sport = Sport::all();
        $dokumentiPrvaGodina = PrilozenaDokumenta::where('skolskaGodina_id', '1')->get();
        $dokumentiOstaleGodine = PrilozenaDokumenta::where('skolskaGodina_id', '2')->get();
        $statusKandidata = StatusGodine::whereNotIn('id', [4, 5])->get();
        $studijskiProgram = StudijskiProgram::where(['tipStudija_id' => 1, 'indikatorAktivan' => 1])->get();

        $prilozenaDokumenta = KandidatPrilozenaDokumenta::where('kandidat_id', $id)->pluck('prilozenaDokumenta_id')->toArray();

        // Get grades using GradeManagementService
        $grades = $this->gradeManagementService->getGradesForEdit($id);
        $prviRazred = $grades['prviRazred'];
        $drugiRazred = $grades['drugiRazred'];
        $treciRazred = $grades['treciRazred'];
        $cetvrtiRazred = $grades['cetvrtiRazred'];

        $sportskoAngazovanjeKandidata = SportskoAngazovanje::where('kandidat_id', $id)->get();

        return array_merge($this->getDropdownData(), [
            'sport' => $sport,
            'dokumentiPrvaGodina' => $dokumentiPrvaGodina,
            'dokumentiOstaleGodine' => $dokumentiOstaleGodine,
            'statusKandidata' => $statusKandidata,
            'studijskiProgram' => $studijskiProgram,
            'prilozenaDokumenta' => $prilozenaDokumenta,
            'prviRazred' => $prviRazred,
            'drugiRazred' => $drugiRazred,
            'treciRazred' => $treciRazred,
            'cetvrtiRazred' => $cetvrtiRazred,
            'sportskoAngazovanjeKandidata' => $sportskoAngazovanjeKandidata,
        ]);
    }

    /**
     * Get dropdown data for master candidate edit form.
     *
     * Merges master base dropdown data with kandidat-specific data:
     * - Attached documents
     * - Edit-specific status filters (exclude inactive statuses)
     *
     * @param  int  $id  Kandidat ID
     * @return array Complete dropdown data array for master edit form
     */
    public function getEditDropdownDataMaster(int $id): array
    {
        $statusKandidata = StatusGodine::whereNotIn('id', [4, 5])->get();
        $prilozenaDokumenta = KandidatPrilozenaDokumenta::where('kandidat_id', $id)->pluck('prilozenaDokumenta_id')->toArray();

        return array_merge($this->getDropdownDataMaster(), [
            'statusKandidata' => $statusKandidata,
            'prilozenaDokumenta' => $prilozenaDokumenta,
        ]);
    }
}
