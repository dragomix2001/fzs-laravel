<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raspored;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RasporedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Raspored::with(['predmet', 'profesor', 'studijskiProgram', 'oblikNastave']);

        if ($request->dan) {
            $query->where('dan', $request->dan);
        }

        if ($request->predmet_id) {
            $query->where('predmet_id', $request->predmet_id);
        }

        if ($request->profesor_id) {
            $query->where('profesor_id', $request->profesor_id);
        }

        if ($request->studijski_program_id) {
            $query->where('studijski_program_id', $request->studijski_program_id);
        }

        $raspored = $query->orderBy('dan')->orderBy('vreme_od')->get();

        return response()->json([
            'data' => $raspored,
            'message' => 'Распоред успешно учитат',
        ]);
    }

    public function today(): JsonResponse
    {
        $dayMap = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];
        $currentDay = $dayMap[strtolower(now()->englishDayOfWeek)] ?? null;

        $raspored = Raspored::with(['predmet', 'profesor', 'studijskiProgram', 'oblikNastave'])
            ->when($currentDay !== null, fn ($query) => $query->where('dan', $currentDay))
            ->where('aktivan', true)
            ->orderBy('vreme_od')
            ->get();

        return response()->json([
            'data' => $raspored,
            'message' => 'Данашњи распоред',
        ]);
    }

    public function show(Raspored $raspored): JsonResponse
    {
        return response()->json([
            'data' => $raspored->load(['predmet', 'profesor', 'studijskiProgram', 'oblikNastave']),
            'message' => 'Распоред успешно учитат',
        ]);
    }
}
