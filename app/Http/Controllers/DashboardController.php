<?php

namespace App\Http\Controllers;

use App\Models\AktivniIspitniRokovi;
use App\Models\Kandidat;
use App\Models\Obavestenje;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\SkolskaGodUpisa;
use App\Models\StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;
use App\Services\DocumentReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(private readonly DocumentReviewService $documentReviewService) {}

    public function index(Request $request): View
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

        $yearStart = now()->startOfYear();
        $nextYearStart = $yearStart->copy()->addYear();
        $polozeniIspiti = PolozeniIspiti::query()
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->count();
        $prijavljeniIspiti = PrijavaIspita::query()
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->count();

        $aktivnaObavestenja = Obavestenje::aktivna()->count();

        $prolaznost = 0;
        if ($prijavljeniIspiti > 0) {
            $prolaznost = round(($polozeniIspiti / $prijavljeniIspiti) * 100, 1);
        }

        $ispitiPoRoku = ZapisnikOPolaganjuIspita::selectRaw('rok_id, COUNT(*) as broj')
            ->where('datum', '>=', $yearStart->toDateString())
            ->where('datum', '<', $nextYearStart->toDateString())
            ->groupBy('rok_id')
            ->with('ispitniRok')
            ->get();

        $najcesciNeuspesni = PolozeniIspiti::selectRaw('predmet_id, COUNT(*) as broj')
            ->where('konacnaOcena', '<', 6)
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->groupBy('predmet_id')
            ->orderByDesc('broj')
            ->limit(5)
            ->with('predmet')
            ->get();

        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->limit(5)->get();

        $today = now()->toDateString();
        $nextThirtyDays = now()->addDays(30)->toDateString();
        $predstojeciRokovi = AktivniIspitniRokovi::query()
            ->where('indikatorAktivan', true)
            ->where('kraj', '>=', $today)
            ->where('pocetak', '<=', $nextThirtyDays)
            ->orderBy('pocetak')
            ->limit(5)
            ->get();
        $prijavePredstojecihRokova = PrijavaIspita::query()
            ->where('datum', '>=', $today)
            ->where('datum', '<=', $nextThirtyDays)
            ->count();
        $nepotpunaDokumentacija = count($this->documentReviewService->getIncompleteCandidatesReviewList());

        $defaultWidgets = [
            'studenti_ukupno' => true,
            'polozeni_ispiti' => true,
            'prijavljeni_ispiti' => true,
            'aktivna_obavestenja' => true,
            'studenti_po_programu' => true,
            'studenti_po_godini' => true,
            'prolaznost' => true,
            'neuspesni_predmeti' => true,
            'operativni_pregled' => true,
        ];
        $widgets = array_merge($defaultWidgets, session('dashboard_widgets', []));

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
            'predstojeciRokovi',
            'prijavePredstojecihRokova',
            'nepotpunaDokumentacija',
            'skolskeGodine',
            'skolskaGodinaId',
            'widgets'
        ));
    }

    public function saveWidgets(Request $request): RedirectResponse
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
            'operativni_pregled' => $request->has('operativni_pregled'),
        ];

        session(['dashboard_widgets' => $widgets]);

        return redirect()->route('dashboard.index');
    }

    public function studenti(Request $request): View
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

        $studenti = $query->with(['studijskiProgram', 'godinaUpisa'])->get();
        $programi = StudijskiProgram::all();
        $godine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();

        return view('dashboard.studenti', compact('studenti', 'programi', 'godine', 'programId', 'godinaId'));
    }

    public function ispiti(Request $request): View
    {
        $validated = $request->validate([
            'godina' => ['sometimes', 'integer', 'between:1000,9998'],
        ]);
        $godina = (string) ($validated['godina'] ?? now()->year);
        $yearStart = now()->setDate((int) $godina, 1, 1)->startOfDay();
        $nextYearStart = $yearStart->copy()->addYear();

        $polozeniPoMesecima = PolozeniIspiti::selectRaw('MONTH(created_at) as mesec, COUNT(*) as broj')
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();

        $prijavePoMesecima = PrijavaIspita::selectRaw('MONTH(created_at) as mesec, COUNT(*) as broj')
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->groupBy('mesec')
            ->orderBy('mesec')
            ->get();

        $uspehPoPredmetu = PolozeniIspiti::selectRaw('predmet_id, 
            COUNT(*) as ukupno,
            SUM(CASE WHEN konacnaOcena >= 6 THEN 1 ELSE 0 END) as polozeni,
            AVG(konacnaOcena) as prosek')
            ->where('created_at', '>=', $yearStart)
            ->where('created_at', '<', $nextYearStart)
            ->groupBy('predmet_id')
            ->with('predmet')
            ->orderByDesc('ukupno')
            ->limit(10)
            ->get();

        return view('dashboard.ispiti', compact('polozeniPoMesecima', 'prijavePoMesecima', 'uspehPoPredmetu', 'godina'));
    }
}
