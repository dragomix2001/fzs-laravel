<?php

namespace App\Http\Controllers;

use App\DTOs\DiplomaAddData;
use App\DTOs\DiplomskiAddData;
use App\DTOs\NastavniPlanData;
use App\DTOs\ZapisnikStampaData;
use App\Exports\SpisakKandidataExport;
use App\Http\Requests\ReportGodinaRequest;
use App\Http\Requests\ReportPredmetRequest;
use App\Http\Requests\ReportProgramGodinaRequest;
use App\Http\Requests\ReportProgramRequest;
use App\Models\Kandidat;
use App\Services\DiplomaService;
use App\Services\DiplomskiRadService;
use App\Services\IspitPdfService;
use App\Services\StudentListService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IzvestajiController extends Controller
{
    public function __construct(
        protected StudentListService $studentListService,
        protected DiplomaService $diplomaService,
        protected DiplomskiRadService $diplomskiRadService,
        protected IspitPdfService $ispitPdfService,
    ) {}

    public function spisakPoSmerovima()
    {
        return $this->studentListService->spisakPoSmerovima();
    }

    public function integralno(ReportGodinaRequest $request)
    {
        return $this->studentListService->integralno($request->integer('godina'));
    }

    public function spisakPoSmerovimaOstali(ReportGodinaRequest $request)
    {
        return $this->studentListService->spisakPoSmerovimaOstali($request->integer('godina'));
    }

    public function spisakPoSmerovimaAktivni(ReportGodinaRequest $request)
    {
        return $this->studentListService->spisakPoSmerovimaAktivni($request->integer('godina'));
    }

    public function spisakZaSmer(ReportProgramGodinaRequest $request)
    {
        return $this->studentListService->spisakZaSmer($request->integer('program'), $request->integer('godina'));
    }

    public function spisakPoProgramu(ReportProgramRequest $request)
    {
        return $this->studentListService->spisakPoProgramu($request->integer('program'));
    }

    public function spisakPoGodini(ReportGodinaRequest $request)
    {
        return $this->studentListService->spisakPoGodini($request->integer('godina'));
    }

    public function spisakPoSlavama()
    {
        return $this->studentListService->spisakPoSlavama();
    }

    public function spisakPoProfesorima()
    {
        return $this->studentListService->spisakPoProfesorima();
    }

    public function spiskoviStudenti()
    {
        return $this->studentListService->spiskoviStudenti();
    }

    public function potvrdeStudent(Kandidat $student)
    {
        return $this->diplomaService->potvrdeStudent($student);
    }

    public function diplomaUnos(Kandidat $student)
    {
        return $this->diplomaService->diplomaUnos($student);
    }

    public function diplomaAdd(Request $request)
    {
        return $this->diplomaService->diplomaAdd(DiplomaAddData::fromRequest($request));
    }

    public function spisakPoPredmetima(ReportPredmetRequest $request)
    {
        return $this->studentListService->spisakPoPredmetima($request->integer('predmet'));
    }

    public function diplomaStampa(Kandidat $student)
    {
        return $this->diplomaService->diplomaStampa($student);
    }

    public function diplomskiUnos(Kandidat $student)
    {
        return $this->diplomskiRadService->diplomskiUnos($student);
    }

    public function diplomskiAdd(Request $request)
    {
        return $this->diplomskiRadService->diplomskiAdd(DiplomskiAddData::fromRequest($request));
    }

    public function komisijaStampa(Kandidat $student)
    {
        return $this->diplomskiRadService->komisijaStampa($student);
    }

    public function polozeniStampa($id)
    {
        return $this->ispitPdfService->polozeniStampa($id);
    }

    public function nastavniPlan(Request $request)
    {
        return $this->ispitPdfService->nastavniPlan(NastavniPlanData::fromRequest($request));
    }

    public function spisakDiplomiranih(ReportGodinaRequest $request)
    {
        return $this->studentListService->spisakDiplomiranih($request->integer('godina'));
    }

    public function zapisnikStampa(Request $request)
    {
        return $this->ispitPdfService->zapisnikStampa(ZapisnikStampaData::fromRequest($request));
    }

    public function zapisnikDiplomski(Kandidat $student)
    {
        return $this->diplomskiRadService->zapisnikDiplomski($student);
    }

    public function excelStampa(ReportGodinaRequest $request)
    {
        return Excel::download(
            new SpisakKandidataExport($request->integer('godina')),
            'Spisak.xlsx'
        );
    }
}
