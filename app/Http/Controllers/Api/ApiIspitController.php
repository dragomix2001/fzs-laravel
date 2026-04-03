<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePredmetRequest;
use App\Http\Requests\Api\UpdatePredmetRequest;
use App\Models\Predmet;

class ApiIspitController extends Controller
{
    public function index()
    {
        $predmeti = Predmet::all();

        return response()->json($predmeti);
    }

    public function store(StorePredmetRequest $request)
    {
        $predmet = Predmet::create($request->validated());

        return response()->json($predmet, 201);
    }

    public function show(Predmet $predmet)
    {
        return response()->json($predmet);
    }

    public function update(UpdatePredmetRequest $request, Predmet $predmet)
    {
        $predmet->update($request->validated());

        return response()->json($predmet);
    }

    public function destroy(Predmet $predmet)
    {
        $predmet->delete();

        return response()->json(null, 204);
    }
}
