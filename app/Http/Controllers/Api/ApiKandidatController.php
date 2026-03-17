<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kandidat;
use Illuminate\Http\Request;

class ApiKandidatController extends Controller
{
    public function index()
    {
        $kandidati = Kandidat::with(['tipStudija', 'program', 'upisaneGodine'])->get();

        return response()->json($kandidati);
    }

    public function store(Request $request)
    {
        $kandidat = Kandidat::create($request->all());

        return response()->json($kandidat, 201);
    }

    public function show(Kandidat $kandidat)
    {
        $kandidat->load(['tipStudija', 'program', 'upisaneGodine', 'angazovanja']);

        return response()->json($kandidat);
    }

    public function update(Request $request, Kandidat $kandidat)
    {
        $kandidat->update($request->all());

        return response()->json($kandidat);
    }

    public function destroy(Kandidat $kandidat)
    {
        $kandidat->delete();

        return response()->json(null, 204);
    }
}
