<?php

namespace App\Http\Controllers;

use App\Models\Aktivnost;
use App\Models\Kandidat;
use App\Models\Ocenjivanje;
use App\Models\Predmet;
use Illuminate\Http\Request;

class AktivnostController extends Controller
{
    public function index()
    {
        $aktivnosti = Aktivnost::with('predmet')->orderBy('datum', 'desc')->get();

        return view('aktivnost.index', compact('aktivnosti'));
    }

    public function create()
    {
        $predmeti = Predmet::all();

        return view('aktivnost.create', compact('predmeti'));
    }

    public function store(Request $request)
    {
        $aktivnost = Aktivnost::create($request->all());

        return redirect()->route('aktivnost.index')->with('success', 'Аktivnost креирана');
    }

    public function show(Aktivnost $aktivnost)
    {
        $ocene = Ocenjivanje::where('aktivnost_id', $aktivnost->id)
            ->with('student')
            ->get();

        return view('aktivnost.show', compact('aktivnost', 'ocene'));
    }

    public function ocenjivanje(Aktivnost $aktivnost)
    {
        $studenti = Kandidat::where('statusUpisa_id', 3)->get();
        $ocene = Ocenjivanje::where('aktivnost_id', $aktivnost->id)
            ->pluck('bodovi', 'student_id')
            ->toArray();

        return view('aktivnost.ocenjivanje', compact('aktivnost', 'studenti', 'ocene'));
    }

    public function saveOcenjivanje(Request $request, Aktivnost $aktivnost)
    {
        foreach ($request->bodovi as $studentId => $bodovi) {
            if ($bodovi !== null && $bodovi !== '') {
                Ocenjivanje::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'aktivnost_id' => $aktivnost->id,
                    ],
                    [
                        'bodovi' => $bodovi,
                        'ocena' => $this->izracunajOcenu($bodovi, $aktivnost->max_bodova),
                        'profesor_id' => auth()->user()->profesor_id ?? null,
                    ]
                );
            }
        }

        return redirect()->route('aktivnost.show', $aktivnost->id)->with('success', 'Оцењивање сачувано');
    }

    private function izracunajOcenu($bodovi, $maxBodova)
    {
        $procenat = ($bodovi / $maxBodova) * 100;

        if ($procenat >= 90) {
            return 10;
        }
        if ($procenat >= 80) {
            return 9;
        }
        if ($procenat >= 70) {
            return 8;
        }
        if ($procenat >= 60) {
            return 7;
        }
        if ($procenat >= 50) {
            return 6;
        }
        if ($procenat >= 40) {
            return 5;
        }

        return 5;
    }

    public function rezime(Request $request)
    {
        $predmet = Predmet::find($request->predmet_id);
        $aktivnosti = Aktivnost::where('predmet_id', $request->predmet_id)->get();

        $studenti = Kandidat::where('statusUpisa_id', 3)->get();

        $rezultati = [];
        foreach ($studenti as $student) {
            $ukupnoBodova = 0;
            $ukupnoMax = 0;

            foreach ($aktivnosti as $aktivnost) {
                $ocena = Ocenjivanje::where('student_id', $student->id)
                    ->where('aktivnost_id', $aktivnost->id)
                    ->first();

                if ($ocena && $ocena->bodovi) {
                    $ukupnoBodova += $ocena->bodovi;
                }
                $ukupnoMax += $aktivnost->max_bodova;
            }

            $rezultati[] = [
                'student' => $student,
                'bodovi' => $ukupnoBodova,
                'max' => $ukupnoMax,
                'procenat' => $ukupnoMax > 0 ? round(($ukupnoBodova / $ukupnoMax) * 100, 2) : 0,
            ];
        }

        return view('aktivnost.rezime', compact('predmet', 'aktivnosti', 'rezultati'));
    }
}
