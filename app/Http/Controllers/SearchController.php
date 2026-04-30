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
        $validated = $request->validate([
            'pretraga' => ['nullable', 'string', 'max:100'],
            'pretraga_predmet' => ['nullable', 'string', 'max:100'],
            'studijski_program_id' => ['nullable', 'integer', 'exists:studijski_program,id'],
            'godina_studija_id' => ['nullable', 'integer', 'exists:godina_studija,id'],
            'status_upisa_id' => ['nullable', 'integer', 'exists:status_studiranja,id'],
            'skolska_godina_id' => ['nullable', 'integer', 'exists:skolska_god_upisa,id'],
        ]);

        $studijskiProgrami = StudijskiProgram::all();
        $godineStudija = GodinaStudija::all();
        $skolskeGodine = SkolskaGodUpisa::orderBy('naziv', 'desc')->get();
        $statusi = StatusGodine::all();

        // Student search — only run query if at least one filter is provided
        $studenti = collect();
        $hasFilter = array_filter($validated);

        if ($hasFilter) {
            $query = Kandidat::query();

            if (! empty($validated['pretraga'])) {
                $term = $validated['pretraga'];
                $query->where(function ($q) use ($term) {
                    $q->where('imeKandidata', 'LIKE', '%'.$term.'%')
                        ->orWhere('prezimeKandidata', 'LIKE', '%'.$term.'%')
                        ->orWhere('brojIndeksa', 'LIKE', '%'.$term.'%')
                        ->orWhere('jmbg', 'LIKE', '%'.$term.'%');
                });
            }

            if (! empty($validated['studijski_program_id'])) {
                $query->where('studijskiProgram_id', $validated['studijski_program_id']);
            }

            if (! empty($validated['godina_studija_id'])) {
                $query->where('godinaStudija_id', $validated['godina_studija_id']);
            }

            if (! empty($validated['status_upisa_id'])) {
                $query->where('statusUpisa_id', $validated['status_upisa_id']);
            }

            if (! empty($validated['skolska_godina_id'])) {
                $query->where('skolskaGodinaUpisa_id', $validated['skolska_godina_id']);
            }

            $studenti = $query->orderBy('prezimeKandidata')->orderBy('imeKandidata')->limit(500)->get();
        }

        // Predmet search
        $predmeti = [];
        if (! empty($validated['pretraga_predmet'])) {
            $term = $validated['pretraga_predmet'];
            $predmeti = Predmet::where('naziv', 'LIKE', '%'.$term.'%')
                ->orWhere('sifraPredmeta', 'LIKE', '%'.$term.'%')
                ->limit(200)
                ->get();
        }

        return view('search.index', compact('studenti', 'predmeti', 'studijskiProgrami', 'godineStudija', 'skolskeGodine', 'statusi', 'request'));
    }
}
