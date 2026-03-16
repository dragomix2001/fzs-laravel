<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\NastavnaNedelja;
use App\Models\Predmet;
use App\Models\Prisanstvo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrisustvoController extends Controller
{
    public function index(Request $request)
    {
        $predmeti = Predmet::all();
        $nedelje = NastavnaNedelja::orderBy('redni_broj', 'desc')->get();

        $prisanstva = null;
        if ($request->predmet && $request->nedelja) {
            $prisanstva = Prisanstvo::where('predmet_id', $request->predmet)
                ->where('nastavna_nedelja_id', $request->nedelja)
                ->with('student')
                ->get();
        }

        return view('prisustvo.index', compact('predmeti', 'nedelje', 'prisanstva'));
    }

    public function create(Request $request)
    {
        $predmeti = Predmet::all();
        $nedelje = NastavnaNedelja::orderBy('redni_broj', 'desc')->get();

        $studenti = [];
        if ($request->predmet) {
            $studenti = Kandidat::where('statusUpisa_id', 3)->get();
        }

        return view('prisustvo.create', compact('predmeti', 'nedelje', 'studenti'));
    }

    public function store(Request $request)
    {
        foreach ($request->student_ids as $studentId) {
            Prisanstvo::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'predmet_id' => $request->predmet_id,
                    'nastavna_nedelja_id' => $request->nastavna_nedelja_id,
                ],
                [
                    'status' => $request->status[$studentId] ?? 'odsutan',
                    'napomena' => $request->napomena[$studentId] ?? null,
                    'profesor_id' => Auth::user()?->profesor_id,
                ]
            );
        }

        return redirect()->route('prisustvo.index')->with('success', 'Prisustvo uspešno sačuvano');
    }

    public function report(Request $request)
    {
        $studenti = Kandidat::where('statusUpisa_id', 3)->get();

        $prisanstva = Prisanstvo::where('predmet_id', $request->predmet_id)
            ->with('student', 'nastavnaNedelja')
            ->get()
            ->groupBy('student_id');

        return view('prisustvo.report', compact('studenti', 'prisanstva'));
    }
}
