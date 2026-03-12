<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Predmet;
use Illuminate\Http\Request;

class ApiIspitController extends Controller
{
    public function index()
    {
        $predmeti = Predmet::all();
        return response()->json($predmeti);
    }

    public function store(Request $request)
    {
        $predmet = Predmet::create($request->all());
        return response()->json($predmet, 201);
    }

    public function show(Predmet $predmet)
    {
        return response()->json($predmet);
    }

    public function update(Request $request, Predmet $predmet)
    {
        $predmet->update($request->all());
        return response()->json($predmet);
    }

    public function destroy(Predmet $predmet)
    {
        $predmet->delete();
        return response()->json(null, 204);
    }
}
