<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\Obavestenje;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $skolskaGodinaId = $request->skolska_godina_id ??
            Cache::remember('active_skolska_godina', 3600, function () {
                return SkolskaGodUpisa::where('aktivan', true)->value('id');
            });

        $ukupnoStudenata = Kandidat::where('statusUpisa_id', 3)->count();

        $studentiPoGodini = Kandidat::selectRaw('skolskaGodinaUpisa_id, COUNT(*) as broj')
            ->where('statusUpisa_id', 3)
            ->groupBy('skolskaGodinaUpisa_id')
            ->with('godinaUpisa')
            ->get();

        $studentiPoProgramu = Kandidat::selectRaw('studijskiProgram_id, COUNT(*) as broj')
            ->where('statusUpisa_id', 3)
            ->groupBy('studijskiProgram_id')
            ->with('program')
            ->get();

        $polozeniIspiti = PolozeniIspiti::whereYear('created_at', date('Y'))->count();
        $prijavljeniIspiti = PrijavaIspita::whereYear('created_at', date('Y'))->count();

        $aktivnaObavestenja = Obavestenje::aktivna()->count();

        $prolaznost = 0;
        if ($prijavljeniIspiti > 0) {
            $prolaznost = round(($polozeniIspiti / $prijavljeniIspiti) * 100, 1);
        }

        $ispitiPoRoku = ZapisnikOPolaganjuIspita::selectRaw('rok_id, COUNT(*) as broj')
            ->whereYear('datum', date('Y'))
            ->groupBy('rok_id')
            ->with('ispitniRok')
            ->get();

        $najcesciNeuspesni = PolozeniIspiti::selectRaw('predmet_id, COUNT(*) as broj')
            ->where('konacnaOcena', '<', 6)
            ->whereYear('created_at', date('Y'))
            ->groupBy('predmet_id')
            ->orderByDesc('broj')
            ->limit(5)
            ->with('predmet')
            ->get();

        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->limit(5)->get();

        $widgets = session('dashboard_widgets', [
            'studenti_ukupno' => true,
            'polozeni_ispiti' => true,
            'prijavljeni_ispiti' => true,
            'aktivna_obavestenja' => true,
            'studenti_po_programu' => true,
            'studenti_po_godini' => true,
            'prolaznost' => true,
            'neuspesni_predmeti' => true,
        ]);

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
            'skolskaGodinaId',
            'widgets'
        ));
    }

    public function saveWidgets(Request $request)
    {
        $widgets = [
            'studenti_ukupno' => $request->has('studenti_ukupno'),
            'polozeni_ispiti' => $request->has('polozeni_ispiti'),
            'prijavljeni_ispiti' => $request->has('prijavljeni_ispiti'),
            'aktivna_obavestenja' => $request->has('aktivna_obavestenja'),
            'studenti_po_programu' => $request->has('studenti_po_programu'),
            'studenti_po_godini' => $request->has('studenti_po_godini'),
            'prolaznost' => $request->has('prolaznost'),
            'neuspesni_predmeti' => $request->has('neuspesni_predmeti'),
        ];

        session(['dashboard_widgets' => $widgets]);

        return redirect()->route('dashboard.index');
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
            $query->where('skolskaGodinaUpisa_id', $godinaId);
        }

        $studenti = $query->with(['program', 'godinaUpisa'])->get();
        $programi = StudijskiProgram::all();
        $godine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();

        return view('dashboard.studenti', compact('studenti', 'programi', 'godine', 'programId', 'godinaId'));
    }

    public function ispiti(Request $request)
    {
        $godina = $request->godina ?? date('Y');

        $polozeniPoMesecima = PolozeniIspiti::selectRaw('MONTH(created_at) as mesec, COUNT(*) as broj')
            ->whereYear('created_at', $godina)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();

        $prijavePoMesecima = PrijavaIspita::selectRaw('MONTH(created_at) as mesec, COUNT(*) as broj')
            ->whereYear('created_at', $godina)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();

        $uspehPoPredmetu = PolozeniIspiti::selectRaw('predmet_id, 
            COUNT(*) as ukupno,
            SUM(CASE WHEN konacnaOcena >= 6 THEN 1 ELSE 0 END) as polozeni,
            AVG(konacnaOcena) as prosek')
            ->whereYear('created_at', $godina)
            ->groupBy('predmet_id')
            ->with('predmet')
            ->orderByDesc('ukupno')
            ->limit(10)
            ->get();

        return view('dashboard.ispiti', compact('polozeniPoMesecima', 'prijavePoMesecima', 'uspehPoPredmetu', 'godina'));
    }
}
