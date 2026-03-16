<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aktivnost;
use Illuminate\Http\Request;

class AktivnostController extends Controller
{
    public function index(Request $request)
    {
        $query = Aktivnost::with(['profesor', 'ucionica']);

        if ($request->tip) {
            $query->where('tip', $request->tip);
        }

        if ($request->datum) {
            $query->whereDate('datum_vreme_pocetka', $request->datum);
        }

        $aktivnosti = $query->orderBy('datum_vreme_pocetka', 'desc')->get();

        return response()->json([
            'data' => $aktivnosti,
            'message' => 'Активности успешно учитате',
        ]);
    }

    public function today()
    {
        $aktivnosti = Aktivnost::with(['profesor', 'ucionica'])
            ->whereDate('datum_vreme_pocetka', now()->toDateString())
            ->where('aktivan', true)
            ->orderBy('datum_vreme_pocetka')
            ->get();

        return response()->json([
            'data' => $aktivnosti,
            'message' => 'Данашње активности',
        ]);
    }

    public function show(Aktivnost $aktivnost)
    {
        return response()->json([
            'data' => $aktivnost->load(['profesor', 'ucionica', 'studenti']),
            'message' => 'Активност успешно учитата',
        ]);
    }

    public function myActivities(Request $request)
    {
        $user = $request->user();
        $kandidat = \App\Models\Kandidat::where('email', $user->email)->first();

        if (! $kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        $aktivnosti = Aktivnost::with(['profesor', 'ucionica'])
            ->whereHas('studenti', function ($query) use ($kandidat) {
                $query->where('kandidat_id', $kandidat->id);
            })
            ->orderBy('datum_vreme_pocetka', 'desc')
            ->get();

        return response()->json([
            'data' => $aktivnosti,
            'message' => 'Моје активности',
        ]);
    }
}
