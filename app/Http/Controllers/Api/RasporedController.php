<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raspored;
use Illuminate\Http\Request;

class RasporedController extends Controller
{
    public function index(Request $request)
    {
        $query = Raspored::with(['profesor', 'ucionica']);
        
        if ($request->dan) {
            $query->where('dan', $request->dan);
        }
        
        if ($request->predmet_id) {
            $query->where('predmet_id', $request->predmet_id);
        }
        
        if ($request->profesor_id) {
            $query->where('profesor_id', $request->profesor_id);
        }
        
        $raspored = $query->orderBy('vreme_pocetka')->get();
        
        return response()->json([
            'data' => $raspored,
            'message' => 'Распоред успешно учитат',
        ]);
    }

    public function today()
    {
        $dan = strtolower(now()->format('l'));
        $danMap = [
            'monday' => 'Понедељак',
            'tuesday' => 'Уторак',
            'wednesday' => 'Среда',
            'thursday' => 'Четвртак',
            'friday' => 'Петак',
            'saturday' => 'Субота',
            'sunday' => 'Недеља',
        ];
        
        $raspored = Raspored::with(['profesor', 'ucionica'])
            ->where('dan', $danMap[$dan] ?? $dan)
            ->where('aktivan', true)
            ->orderBy('vreme_pocetka')
            ->get();
        
        return response()->json([
            'data' => $raspored,
            'message' => 'Данашњи распоред',
        ]);
    }

    public function show(Raspored $raspored)
    {
        return response()->json([
            'data' => $raspored->load(['profesor', 'ucionica']),
            'message' => 'Распоред успешно учитат',
        ]);
    }
}
