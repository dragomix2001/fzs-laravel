<?php

namespace App\Http\Controllers;

use App\Opstina;
use App\Region;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OpstinaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $opstina = Opstina::all();
            $region = Region::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.opstina', compact('opstina', 'region'));
    }

    public function unos(Request $request)
    {
        $opstina = new Opstina;

        $opstina->naziv = $request->naziv;
        $opstina->region_id = $request->region_id;

        try {
            $opstina->save();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/opstina');
    }

    public function edit(Opstina $opstina)
    {
        try {
            $region = Region::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.editOpstina', compact('opstina', 'region'));
    }

    public function add()
    {
        try {
            $region = Region::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.addOpstina', compact('region'));
    }

    public function update(Request $request, Opstina $opstina)
    {
        $opstina->naziv = $request->naziv;
        $opstina->region_id = $request->region_id;

        try {
            $opstina->update();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/opstina');
    }

    public function delete(Opstina $opstina)
    {
        try {
            $opstina->delete();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return back();
    }
}
