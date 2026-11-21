<?php

namespace App\Http\Controllers;

use App\Models\GodinaStudija;
use App\Models\Kandidat;
use App\Models\PredmetProgram;
use App\Models\SkolskaGodUpisa;
use App\Models\StatusGodine;
use App\Models\StudijskiProgram;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search()
    {
        return $this->searchResult(request());
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

        $studentSearchRequested = collect([
            'pretraga',
            'studijski_program_id',
            'godina_studija_id',
            'status_upisa_id',
            'skolska_godina_id',
        ])->contains(fn (string $key): bool => isset($validated[$key]) && $validated[$key] !== '');
        $subjectSearchRequested = ! empty($validated['pretraga_predmet']);

        $studenti = collect();

        if ($studentSearchRequested) {
            $query = Kandidat::query()->with(['program', 'godinaStudija', 'statusUpisa']);

            if (! empty($validated['pretraga'])) {
                $term = $validated['pretraga'];
                $terms = preg_split('/\s+/', trim($term), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $query->where(function ($q) use ($term, $terms) {
                    $q->where(function ($nameQuery) use ($terms) {
                        foreach ($terms as $nameTerm) {
                            $nameQuery->where(function ($query) use ($nameTerm) {
                                $query->where('imeKandidata', 'LIKE', $nameTerm.'%')
                                    ->orWhere('prezimeKandidata', 'LIKE', $nameTerm.'%');
                            });
                        }
                    })
                        ->orWhere('brojIndeksa', 'LIKE', $term.'%')
                        ->orWhere('jmbg', $term);
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

            $studenti = $query
                ->orderBy('prezimeKandidata')
                ->orderBy('imeKandidata')
                ->paginate(25, ['*'], 'student_page')
                ->withQueryString();
        }

        $predmeti = collect();
        if ($subjectSearchRequested) {
            $term = $validated['pretraga_predmet'];
            $predmeti = PredmetProgram::query()
                ->with(['predmet', 'studijskiProgram'])
                ->whereHas('predmet', function ($query) use ($term) {
                    $query->where('naziv', 'LIKE', $term.'%');
                })
                ->orderBy('predmet_id')
                ->paginate(25, ['*'], 'subject_page')
                ->withQueryString();
        }

        $hasSearch = $studentSearchRequested || $subjectSearchRequested;

        return view('search.index', compact(
            'studenti',
            'predmeti',
            'studijskiProgrami',
            'godineStudija',
            'skolskeGodine',
            'statusi',
            'request',
            'studentSearchRequested',
            'subjectSearchRequested',
            'hasSearch'
        ));
    }
}
