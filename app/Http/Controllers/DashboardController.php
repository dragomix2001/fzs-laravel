<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\StudijskiProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Models\Obavestenje;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $skolskaGodinaId = $request->skolska_godina_id ?? 
            SkolskaGodUpisa::where('aktivan', true)->value('id');

        $ukupnoStudenata = Kandidat::where('statusUpisa_id', 3)->count();
        
        $studentiPoGodini = Kandidat::selectRaw('godinaUpisa_id, COUNT(*) as broj')
            ->where('statusUpisa_id', 3)
            ->groupBy('godinaUpisa_id')
            ->with('godinaUpisa')
            ->get();

        $studentiPoProgramu = Kandidat::selectRaw('studijskiProgram_id, COUNT(*) as broj')
            ->where('statusUpisa_id', 3)
            ->groupBy('studijskiProgram_id')
            ->with('studijskiProgram')
            ->get();

        $polozeniIspiti = PolozeniIspiti::whereYear('datum', date('Y'))->count();
        $prijavljeniIspiti = PrijavaIspita::whereYear('datumPrijave', date('Y'))->count();
        
        $aktivnaObavestenja = Obavestenje::aktivna()->count();
        
        $prolaznost = 0;
        if ($prijavljeniIspiti > 0) {
            $prolaznost = round(($polozeniIspiti / $prijavljeniIspiti) * 100, 1);
        }

        $ispitiPoRoku = ZapisnikOPolaganjuIspita::selectRaw('ispitni_rok_id, COUNT(*) as broj')
            ->whereYear('datum', date('Y'))
            ->groupBy('ispitni_rok_id')
            ->with('ispitniRok')
            ->get();

        $najcesciNeuspesni = PolozeniIspiti::selectRaw('predmet_id, COUNT(*) as broj')
            ->where('konacna_ocena', '<', 6)
            ->whereYear('datum', date('Y'))
            ->groupBy('predmet_id')
            ->orderByDesc('broj')
            ->limit(5)
            ->with('predmet')
            ->get();

        $skolskeGodine = SkolskaGodUpisa::orderBy('godina', 'desc')->limit(5)->get();

        return view('dashboard.index', compact(
            'ukupnoStudenata',
            'studentiPoGodini',
            'studentiPoProgramu',
            'polozeniIspiti',
            'prijavljeniIspiti',
            'aktivnaObavestenja',
            'prolaznost',
            'ispitiPoRoku',
            'najcesciNeuspesni',
            'skolskeGodine',
            'skolskaGodinaId'
        ));
    }

    public function studenti(Request $request)
    {
        $programId = $request->program_id;
        $godinaId = $request->godina_id;
        
        $query = Kandidat::where('statusUpisa_id', 3);
        
        if ($programId) {
            $query->where('studijskiProgram_id', $programId);
        }
        
        if ($godinaId) {
            $query->where('godinaUpisa_id', $godinaId);
        }
        
        $studenti = $query->with(['studijskiProgram', 'godinaUpisa'])->get();
        $programi = StudijskiProgram::all();
        $godine = SkolskaGodUpisa::orderBy('godina', 'desc')->get();
        
        return view('dashboard.studenti', compact('studenti', 'programi', 'godine', 'programId', 'godinaId'));
    }

    public function ispiti(Request $request)
    {
        $godina = $request->godina ?? date('Y');
        
        $polozeniPoMesecima = PolozeniIspiti::selectRaw('MONTH(datum) as mesec, COUNT(*) as broj')
            ->whereYear('datum', $godina)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();
            
        $prijavePoMesecima = PrijavaIspita::selectRaw('MONTH(datumPrijave) as mesec, COUNT(*) as broj')
            ->whereYear('datumPrijave', $godina)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();
            
        $uspehPoPredmetu = PolozeniIspiti::selectRaw('predmet_id, 
            COUNT(*) as ukupno,
            SUM(CASE WHEN konacna_ocena >= 6 THEN 1 ELSE 0 END) as polozeni,
            AVG(konacna_ocena) as prosek')
            ->whereYear('datum', $godina)
            ->groupBy('predmet_id')
            ->with('predmet')
            ->orderByDesc('ukupno')
            ->limit(10)
            ->get();
            
        return view('dashboard.ispiti', compact('polozeniPoMesecima', 'prijavePoMesecima', 'uspehPoPredmetu', 'godina'));
    }
}
