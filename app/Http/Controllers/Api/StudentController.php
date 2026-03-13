<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kandidat;
use App\Models\PolozeniIspiti;
use App\Models\PrijavaIspita;
use App\Models\SkolskaGodUpisa;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get current student profile
     * GET /api/v1/student/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $kandidat = Kandidat::where('user_id', $user->id)->first();

        if (!$kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        return response()->json([
            'data' => $kandidat,
            'message' => 'Профил успешно учитат',
        ]);
    }

    /**
     * Get student's passed exams
     * GET /api/v1/student/ispiti
     */
    public function polozeniIspiti(Request $request)
    {
        $user = $request->user();
        $kandidat = Kandidat::where('user_id', $user->id)->first();

        if (!$kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        $ispiti = PolozeniIspiti::with('predmet')
            ->where('kandidat_id', $kandidat->id)
            ->where('indikatorAktivan', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $ispiti,
            'message' => 'Положени испити успешно учитати',
        ]);
    }

    /**
     * Get student's exam registrations
     * GET /api/v1/student/prijave
     */
    public function prijave(Request $request)
    {
        $user = $request->user();
        $kandidat = Kandidat::where('user_id', $user->id)->first();

        if (!$kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        $prijave = PrijavaIspita::with(['predmet', 'rok'])
            ->where('kandidat_id', $kandidat->id)
            ->orderBy('datum', 'desc')
            ->get();

        return response()->json([
            'data' => $prijave,
            'message' => 'Пријаве испита успешно учитате',
        ]);
    }

    /**
     * Get enrollment history
     * GET /api/v1/student/upis
     */
    public function upis(Request $request)
    {
        $user = $request->user();
        $kandidat = Kandidat::where('user_id', $user->id)->first();

        if (!$kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        $upisi = SkolskaGodUpisa::where('kandidat_id', $kandidat->id)
            ->with('skolskaGodina')
            ->orderBy('godina_upisa', 'desc')
            ->get();

        return response()->json([
            'data' => $upisi,
            'message' => 'Историја уписа успешно учитата',
        ]);
    }

    /**
     * Get student statistics
     * GET /api/v1/student/stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $kandidat = Kandidat::where('user_id', $user->id)->first();

        if (!$kandidat) {
            return response()->json([
                'message' => 'Студент није пронађен',
            ], 404);
        }

        $polozeni = PolozeniIspiti::where('kandidat_id', $kandidat->id)
            ->where('indikatorAktivan', 1)
            ->count();

        $prosek = PolozeniIspiti::where('kandidat_id', $kandidat->id)
            ->where('indikatorAktivan', 1)
            ->avg('konacnaOcena');

        $espb = PolozeniIspiti::where('kandidat_id', $kandidat->id)
            ->where('indikatorAktivan', 1)
            ->with('predmet')
            ->get()
            ->sum(function ($ispit) {
                return $ispit->predmet->espb ?? 0;
            });

        return response()->json([
            'data' => [
                'polozeni_ispiti' => $polozeni,
                'prosek' => round($prosek, 2),
                'espb' => $espb,
            ],
            'message' => 'Статистика успешно учитата',
        ]);
    }
}
