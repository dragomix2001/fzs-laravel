<?php

namespace App\Http\Controllers;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\Predmet;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search()
    {
        $studijskiProgrami = StudijskiProgram::all();
        $godineStudija = GodinaStudija::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();
        $statusi = StatusGodine::all();

        return view('search.index', compact('studijskiProgrami', 'godineStudija', 'skolskeGodine', 'statusi'));
    }

    public function searchResult(Request $request)
    {
        $studijskiProgrami = StudijskiProgram::all();
        $godineStudija = GodinaStudija::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();
        $statusi = StatusGodine::all();

        // Student search
        $query = Kandidat::query();

        // Text search
        if ($request->pretraga) {
            $query->where(function ($q) use ($request) {
                $q->where('imeKandidata', 'LIKE', '%'.$request->pretraga.'%')
                    ->orWhere('prezimeKandidata', 'LIKE', '%'.$request->pretraga.'%')
                    ->orWhere('brojIndeksa', 'LIKE', '%'.$request->pretraga.'%')
                    ->orWhere('jmbg', 'LIKE', '%'.$request->pretraga.'%');
            });
        }

        // Filters
        if ($request->studijski_program_id) {
            $query->where('studijskiProgram_id', $request->studijski_program_id);
        }

        if ($request->godina_studija_id) {
            $query->where('godinaStudija_id', $request->godina_studija_id);
        }

        if ($request->status_upisa_id) {
            $query->where('statusUpisa_id', $request->status_upisa_id);
        }

        if ($request->skolska_godina_id) {
            $query->where('skolskaGodinaUpisa_id', $request->skolska_godina_id);
        }

        $studenti = $query->get();

        // Predmet search
        $predmeti = [];
        if ($request->pretraga_predmet) {
            $predmeti = Predmet::where('naziv', 'LIKE', '%'.$request->pretraga_predmet.'%')
                ->orWhere('sifraPredmeta', 'LIKE', '%'.$request->pretraga_predmet.'%')
                ->get();
        }

        return view('search.index', compact('studenti', 'predmeti', 'studijskiProgrami', 'godineStudija', 'skolskeGodine', 'statusi', 'request'));
    }
}
