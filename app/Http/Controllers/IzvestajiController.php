<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Kandidat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StudentListService;
use App\Services\DiplomaService;
use App\Services\DiplomskiRadService;
use App\Services\IspitService;
use Maatwebsite\Excel;

class IzvestajiController extends Controller
{
    protected $studentListService;
    protected $diplomaService;
    protected $diplomskiRadService;
    protected $ispitService;

    public function __construct()
    {
        $this->studentListService = new StudentListService();
        $this->diplomaService = new DiplomaService();
        $this->diplomskiRadService = new DiplomskiRadService();
        $this->ispitService = new IspitService();
    }

    public function spisakPoSmerovima()
    {
        return $this->studentListService->spisakPoSmerovima();
    }

    public function integralno(Request $request)
    {
        return $this->studentListService->integralno($request->godina);
    }

    public function spisakPoSmerovimaOstali(Request $request)
    {
        return $this->studentListService->spisakPoSmerovimaOstali($request->godina);
    }

    public function spisakPoSmerovimaAktivni(Request $request)
    {
        return $this->studentListService->spisakPoSmerovimaAktivni($request->godina);
    }

    public function spisakZaSmer(Request $request)
    {
        return $this->studentListService->spisakZaSmer($request->program, $request->godina);
    }

    public function spisakPoProgramu(Request $request)
    {
        return $this->studentListService->spisakPoProgramu($request->program);
    }

    public function spisakPoGodini(Request $request)
    {
        return $this->studentListService->spisakPoGodini($request->godina);
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
        return $this->diplomaService->diplomaAdd($request);
    }

    public function spisakPoPredmetima(Request $request)
    {
        return $this->studentListService->spisakPoPredmetima($request->predmet);
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
        return $this->diplomskiRadService->diplomskiAdd($request);
    }

    public function komisijaStampa(Kandidat $student)
    {
        return $this->diplomskiRadService->komisijaStampa($student);
    }

    public function polozeniStampa($id)
    {
        return $this->ispitService->polozeniStampa($id);
    }

    public function nastavniPlan(Request $request)
    {
        return $this->ispitService->nastavniPlan($request);
    }

    public function spisakDiplomiranih(Request $request)
    {
        return $this->studentListService->spisakDiplomiranih($request->godina);
    }

    public function zapisnikStampa(Request $request)
    {
        return $this->ispitService->zapisnikStampa($request);
    }

    public function zapisnikDiplomski(Kandidat $student)
    {
        return $this->diplomskiRadService->zapisnikDiplomski($student);
    }

    public function excelStampa(Request $request)
    {
        $godina = $request->godina;
        $statusi = array("1", "2", "4", "5", "7");

        $kandidat = \DB::table('kandidat')
            ->join('studijski_program', 'kandidat.studijskiProgram_id', '=', 'studijski_program.id')
            ->whereIn('kandidat.statusUpisa_id', $statusi)->where(['kandidat.skolskaGodinaUpisa_id' => $godina])->
            select('kandidat.ime', 'kandidat.prezimeKandidata', 'kandidat.brojIndeksa', 'studijski_program.naziv as program')
            ->orderByRaw('SUBSTR(kandidat.brojIndeksa, 5)')->orderBy('kandidat.brojIndeksa')->get();

        Excel::create('Spisak', function ($excel) use ($kandidat) {
            $excel->sheet('sheet1', function ($sheet) use ($kandidat) {
                $sheet->fromArray($kandidat);
            });
        })->export('xls');
    }
}
