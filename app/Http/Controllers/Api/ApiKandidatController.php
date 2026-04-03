<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKandidatRequest;
use App\Http\Requests\Api\UpdateKandidatRequest;
use App\Models\Kandidat;

class ApiKandidatController extends Controller
{
    public function index()
    {
        $kandidati = Kandidat::with(['tipStudija', 'program', 'upisaneGodine'])->get();

        return response()->json($kandidati);
    }

    public function store(StoreKandidatRequest $request)
    {
        $kandidat = Kandidat::create($request->validated());

        return response()->json($kandidat, 201);
    }

    public function show(Kandidat $kandidat)
    {
        $kandidat->load(['tipStudija', 'program', 'upisaneGodine', 'angazovanja']);

        return response()->json($kandidat);
    }

    public function update(UpdateKandidatRequest $request, Kandidat $kandidat)
    {
        $kandidat->update($request->validated());

        return response()->json($kandidat);
    }

    public function destroy(Kandidat $kandidat)
    {
        $kandidat->delete();

        return response()->json(null, 204);
    }
}
