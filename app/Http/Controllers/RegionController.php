<?php

namespace App\Http\Controllers;

use App\Region;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $region = Region::all();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return view('sifarnici.region', compact('region'));
    }

    public function unos(Request $request)
    {
        $region = new Region;
        $region->naziv = $request->naziv;

        try {
            $region->save();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/region');
    }

    public function edit(Region $region)
    {
        return view('sifarnici.editRegion', compact('region'));
    }

    public function add()
    {
        return view('sifarnici.addRegion');
    }

    public function update(Request $request, Region $region)
    {
        $region->naziv = $request->naziv;

        try {
            $region->update();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return Redirect::to('/region');
    }

    public function delete(Region $region)
    {
        try {
            $region->delete();
        } catch (QueryException $e) {
            dd('Дошло је до непредвиђене грешке.'.$e->getMessage());
        }

        return back();
    }
}
