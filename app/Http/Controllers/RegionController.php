<?php

namespace App\Http\Controllers;

use App\Models\Region;
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
        $region = Region::all();

        return view('sifarnici.region', compact('region'));
    }

    public function unos(Request $request)
    {
        $region = new Region;
        $region->naziv = $request->naziv;

        $region->save();

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

        $region->update();

        return Redirect::to('/region');
    }

    public function delete(Region $region)
    {
        $region->delete();

        return back();
    }
}
