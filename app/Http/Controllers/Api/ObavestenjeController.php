<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obavestenje;
use Illuminate\Http\Request;

class ObavestenjeController extends Controller
{
    /**
     * Get all active obavestenja for mobile app
     * GET /api/v1/obavestenja
     */
    public function index(Request $request)
    {
        $obavestenja = Obavestenje::with('profesor')
            ->when($request->tip, function ($query) use ($request) {
                $query->where('tip', $request->tip);
            })
            ->where('aktivan', true)
            ->orderBy('datum_objave', 'desc')
            ->get();

        return response()->json([
            'data' => $obavestenja,
            'message' => 'Обавештења успешно учитана',
        ]);
    }

    /**
     * Get single obavestenje
     * GET /api/v1/obavestenja/{id}
     */
    public function show(Obavestenje $obavestenje)
    {
        return response()->json([
            'data' => $obavestenje->load('profesor'),
            'message' => 'Обавештење успешно учитано',
        ]);
    }

    /**
     * Get public obavestenja (for unauthenticated users)
     * GET /api/v1/obavestenja/javna
     */
    public function javna()
    {
        $obavestenja = Obavestenje::where('aktivan', true)
            ->where('tip', 'opste')
            ->orderBy('datum_objave', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $obavestenja,
            'message' => 'Javna обавештења успешно учитана',
        ]);
    }
}
